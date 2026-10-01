<?php

namespace App\Repositories\Admin;

use App\Models\BillingPolicy;
use App\Models\Customer;
use App\Models\Proposal;
use App\Models\ProposalPhase;
use App\Models\ProposalPrerequisite;
use App\Models\ProposalScheduleStep;
use App\Models\ProposalVersion;
use App\Services\InvoiceGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Prettus\Repository\Eloquent\BaseRepository;

class ProposalRepository extends BaseRepository
{
    function model()
    {
        return Proposal::class;
    }

    public function index()
    {
        return view('admin.proposal.index');
    }

    public function create($attribute = [])
    {
        return view('admin.proposal.create', array_merge(
            ['proposal' => new Proposal()],
            $this->formData()
        ));
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $proposal = $this->model->create(array_merge(
                $request->only($this->scalarFields()),
                ['created_by_id' => Auth::id()]
            ));

            $this->syncChildren($proposal, $request);

            if ($proposal->status === Proposal::STATUS_ACCEPTED) {
                $this->generateProjectInvoices($proposal);
            }

            $message = __('Proposta cadastrada com sucesso.');
            if ($request->input('action') === 'save_pdf') {
                $this->createVersion($proposal);
                $message = __('Proposta cadastrada e PDF gerado com sucesso.');
            }

            DB::commit();
            return redirect()->route('admin.proposal.edit', $proposal->id)->with('success', $message);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $proposal = $this->model
            ->with(['customer', 'phases.items', 'prerequisites', 'scheduleSteps', 'versions.createdBy', 'billingPolicies.invoices'])
            ->findOrFail($id);

        return view('admin.proposal.edit', array_merge(
            ['proposal' => $proposal],
            $this->formData()
        ));
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $proposal = $this->model->findOrFail($id);
            $wasAccepted = $proposal->status === Proposal::STATUS_ACCEPTED;

            $proposal->update($request->only($this->scalarFields()));

            $this->syncChildren($proposal, $request);

            if (! $wasAccepted && $proposal->status === Proposal::STATUS_ACCEPTED) {
                $this->generateProjectInvoices($proposal);
            }

            $message = __('Proposta atualizada com sucesso.');
            if ($request->input('action') === 'save_pdf') {
                $this->createVersion($proposal);
                $message = __('Proposta atualizada e novo PDF gerado com sucesso.');
            }

            DB::commit();
            return redirect()->route('admin.proposal.edit', $proposal->id)->with('success', $message);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Ao aceitar uma proposta (transição pra status=accepted), gera de uma
     * vez as 2 cobranças do contrato - Sinal de Entrada (vence na hora, "no
     * ato da assinatura") e Parcela Final (estimativa de 30 dias após o
     * prazo de execução, já que a data real de conclusão do projeto ainda
     * não existe nesse momento - ajustável depois direto na fatura, quando o
     * projeto realmente terminar).
     *
     * Cada parcela vira uma BillingPolicy (frequency=once) + a Invoice
     * correspondente, reaproveitando o mesmo caminho de uma cobrança avulsa
     * manual (link do Mercado Pago e e-mail de fatura criada saem
     * automáticos - ver InvoiceGenerator). Idempotente: se a proposta já tem
     * alguma política de faturamento (ela só pode ser aceita uma vez do
     * ponto de vista prático), não gera de novo.
     */
    public function generateProjectInvoices(Proposal $proposal): void
    {
        if ($proposal->status !== Proposal::STATUS_ACCEPTED || $proposal->billingPolicies()->exists()) {
            return;
        }

        $today = Carbon::today();
        $finalDueDate = $today->copy()->addDays(30);
        $generator = app(InvoiceGenerator::class);

        if ($proposal->downPaymentValue() > 0) {
            $downPayment = $proposal->billingPolicies()->create([
                'customer_id' => $proposal->customer_id,
                'description' => 'Sinal de Entrada — ' . $proposal->title,
                'frequency' => BillingPolicy::FREQUENCY_ONCE,
                'amount' => $proposal->downPaymentValue(),
                'due_day' => min($today->day, 28),
                'starts_at' => $today,
                'status' => BillingPolicy::STATUS_ACTIVE,
                'notes' => 'Sinal de Entrada — ' . $proposal->title . ' — vencimento {{vencimento}}. ' . Proposal::DOWN_PAYMENT_NOTE,
                'created_by_id' => Auth::id(),
            ]);

            $generator->createForDate($downPayment, $today);
        }

        if ($proposal->finalPaymentValue() > 0) {
            $finalPayment = $proposal->billingPolicies()->create([
                'customer_id' => $proposal->customer_id,
                'description' => 'Parcela Final — ' . $proposal->title,
                'frequency' => BillingPolicy::FREQUENCY_ONCE,
                'amount' => $proposal->finalPaymentValue(),
                'due_day' => min($finalDueDate->day, 28),
                'starts_at' => $today,
                'status' => BillingPolicy::STATUS_ACTIVE,
                'notes' => 'Parcela Final — ' . $proposal->title . ' — vencimento {{vencimento}}. ' . Proposal::FINAL_PAYMENT_NOTE . ' Data estimada — confirme e ajuste o vencimento na fatura quando o projeto for concluído.',
                'created_by_id' => Auth::id(),
            ]);

            $generator->createForDate($finalPayment, $finalDueDate);
        }
    }

    /**
     * "Gerar Faturas" button on the edit screen - covers a proposal that's
     * already accepted but has no billing yet (accepted before this feature
     * existed, or some earlier attempt failed before the policies were
     * created). The normal path is automatic, triggered by the status
     * transition inside update() - this is the manual fallback for
     * everything that transition can't catch.
     */
    public function generateInvoices($id)
    {
        DB::beginTransaction();
        try {
            $proposal = $this->model->findOrFail($id);

            if ($proposal->status !== Proposal::STATUS_ACCEPTED) {
                DB::rollback();
                return redirect()->back()->with('error', __('A proposta precisa estar com status "Aceita" para gerar as faturas.'));
            }

            if ($proposal->billingPolicies()->exists()) {
                DB::rollback();
                return redirect()->back()->with('error', __('Esta proposta já tem faturamento gerado.'));
            }

            $this->generateProjectInvoices($proposal);

            DB::commit();
            return redirect()->route('admin.proposal.edit', $proposal->id)->with('success', __('Faturas de Entrada e Parcela Final geradas com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $proposal = $this->model->findOrFail($id);

            foreach ($proposal->versions as $version) {
                Storage::disk('azure')->delete($version->pdf_path);
            }

            $proposal->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Proposta removida com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Gera uma versão nova (PDF daquele exato momento, guardado no blob) -
     * chamado só quando o usuário clica em "Salvar e Gerar PDF", não em
     * todo "Salvar" comum, pra não lotar o blob de versões a cada ajuste
     * pequeno feito durante a edição.
     */
    private function createVersion(Proposal $proposal): void
    {
        $proposal->load(['customer', 'phases.items', 'prerequisites', 'scheduleSteps']);

        $nextVersionNumber = (int) ($proposal->versions()->max('version_number') ?? 0) + 1;

        $pdf = Pdf::loadView('admin.proposal.document', ['proposal' => $proposal, 'forPdf' => true])
            ->setPaper('a4');

        $pdfPath = "proposals/{$proposal->id}/v{$nextVersionNumber}.pdf";
        Storage::disk('azure')->put($pdfPath, $pdf->output());

        ProposalVersion::create([
            'proposal_id' => $proposal->id,
            'version_number' => $nextVersionNumber,
            'pdf_path' => $pdfPath,
            'created_by_id' => Auth::id(),
        ]);
    }

    /**
     * Apaga uma versão específica - do blob primeiro, depois o registro.
     */
    public function destroyVersion($proposalId, $versionId)
    {
        $version = ProposalVersion::where('proposal_id', $proposalId)->findOrFail($versionId);

        Storage::disk('azure')->delete($version->pdf_path);
        $version->delete();

        return redirect()->back()->with('success', __('Versão removida.'));
    }

    /**
     * Repeatable rows (fases/itens, pré-requisitos, cronograma) have no
     * stable ID from the form - simplest correct way to sync them on every
     * save is drop and recreate, instead of diffing existing rows.
     */
    private function syncChildren(Proposal $proposal, $request): void
    {
        $proposal->phases()->delete();
        foreach ($request->input('phases', []) as $phaseOrder => $phaseData) {
            if (blank($phaseData['title'] ?? null)) {
                continue;
            }

            $phase = ProposalPhase::create([
                'proposal_id' => $proposal->id,
                'title' => $phaseData['title'],
                'subtitle' => $phaseData['subtitle'] ?? null,
                'order' => $phaseOrder,
            ]);

            foreach ($phaseData['items'] ?? [] as $itemOrder => $description) {
                if (blank($description)) {
                    continue;
                }

                $phase->items()->create([
                    'description' => $description,
                    'order' => $itemOrder,
                ]);
            }
        }

        $proposal->prerequisites()->delete();
        foreach ($request->input('prerequisites', []) as $order => $description) {
            if (blank($description)) {
                continue;
            }

            ProposalPrerequisite::create([
                'proposal_id' => $proposal->id,
                'description' => $description,
                'order' => $order,
            ]);
        }

        $proposal->scheduleSteps()->delete();
        foreach ($request->input('schedule_steps', []) as $order => $stepData) {
            if (blank($stepData['description'] ?? null)) {
                continue;
            }

            ProposalScheduleStep::create([
                'proposal_id' => $proposal->id,
                'duration_label' => $stepData['duration_label'] ?? '',
                'description' => $stepData['description'],
                'order' => $order,
            ]);
        }
    }

    private function scalarFields(): array
    {
        return [
            'customer_id', 'title', 'subtitle', 'document_label', 'proposal_year', 'about_text',
            'total_value', 'down_payment_percent', 'final_payment_percent', 'estimated_days',
            'contratante_responsavel',
            'object_intro',
            'status',
        ];
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'placeholders' => $this->placeholders(),
        ];
    }

    /**
     * Texto sugerido pra cada campo de conteúdo - nunca vira valor real do
     * campo (isso preencheria a proposta com texto "fantasma" que passa
     * despercebido), só o atributo placeholder do input: aparece em cinza e
     * some ao digitar, sem contar como dado da proposta se o campo ficar
     * em branco.
     */
    private function placeholders(): array
    {
        return [
            'proposal_year' => (string) now()->year,
            'down_payment_percent' => '40',
            'final_payment_percent' => '60',
            'about_text' => 'Esta proposta comercial contempla integralmente as condições técnicas, prazos, premissas e demais cláusulas contratuais que regerão a prestação dos serviços de Tecnologia da Informação. O instrumento contratual está consolidado nas seções seguintes.',
            'object_intro' => 'O presente contrato tem por objeto a prestação dos serviços de Tecnologia da Informação, distribuídos rigorosamente nas fases técnicas descritas a seguir:',
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProposalDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateProposalRequest;
use App\Http\Requests\Admin\UpdateProposalRequest;
use App\Models\Proposal;
use App\Models\ProposalVersion;
use App\Repositories\Admin\ProposalRepository;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    public $repository;

    public function __construct(ProposalRepository $repository)
    {
        $this->authorizeResource(Proposal::class, 'proposal');
        $this->repository = $repository;
    }

    public function index(ProposalDataTable $dataTable)
    {
        return $dataTable->render('admin.proposal.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateProposalRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(Proposal $proposal)
    {
        //
    }

    public function edit(Proposal $proposal)
    {
        return $this->repository->edit($proposal->id);
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal)
    {
        return $this->repository->update($request, $proposal->id);
    }

    public function destroy(Proposal $proposal)
    {
        return $this->repository->destroy($proposal->id);
    }

    /**
     * "Gerar Faturas" - fallback manual pra uma proposta que já está aceita
     * mas ainda não tem Entrada/Parcela Final geradas (aceita antes desta
     * função existir, por exemplo). O caminho normal é automático, ao mudar
     * o status pra "Aceita" e salvar - ver ProposalRepository::update().
     */
    public function generateInvoices(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        return $this->repository->generateInvoices($proposal->id);
    }

    /**
     * Visualização rápida em HTML, sem gerar/gravar uma versão nova no
     * blob - só pra conferir o layout antes de salvar.
     */
    public function preview(Proposal $proposal)
    {
        $this->authorize('view', $proposal);

        $proposal->load(['customer', 'phases.items', 'prerequisites', 'scheduleSteps']);

        return view('admin.proposal.document', ['proposal' => $proposal]);
    }

    public function downloadVersion(Proposal $proposal, ProposalVersion $version)
    {
        $this->authorize('view', $proposal);

        abort_if($version->proposal_id !== $proposal->id, 404);

        if (! Storage::disk('azure')->exists($version->pdf_path)) {
            return redirect()->back()->with('error', __('PDF desta versão não encontrado.'));
        }

        $safeLabel = preg_replace('/[^A-Za-z0-9]+/', '_', $proposal->document_label) ?: 'proposta';

        return Storage::disk('azure')->download($version->pdf_path, "Proposta_{$safeLabel}_v{$version->version_number}.pdf");
    }

    public function destroyVersion(Proposal $proposal, ProposalVersion $version)
    {
        $this->authorize('update', $proposal);

        abort_if($version->proposal_id !== $proposal->id, 404);

        return $this->repository->destroyVersion($proposal->id, $version->id);
    }
}

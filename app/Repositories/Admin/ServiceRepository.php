<?php

namespace App\Repositories\Admin;

use Exception;
use App\Models\BillingPolicy;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Eloquent\BaseRepository;

class ServiceRepository extends BaseRepository
{
    function model()
    {
        return Service::class;
    }

    public function index()
    {
        return view('admin.service.index');
    }

    public function create($attribute = [])
    {
        return view('admin.service.create');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $service = $this->model->create([
                'name' => $request->name,
                'slug' => $request->slug ?: Service::generateSlug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'billing_type' => $request->billing_type,
                'status' => $request->status ?? 1,
                'created_by_id' => Auth::id(),
            ]);

            $this->syncPricingPlans($service, $request);

            DB::commit();
            return redirect()->route('admin.service.index')->with('success', 'Serviço cadastrado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $service = $this->model->with('pricingPlans')->find($id);
        return view('admin.service.edit', [
            'service' => $service,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $service = $this->model->findOrFail($id);
            $service->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'price' => $request->price,
                'billing_type' => $request->billing_type,
                'status' => $request->status ?? 1,
            ]);

            $this->syncPricingPlans($service, $request);

            DB::commit();
            return redirect()->route('admin.service.index')->with('success', 'Serviço atualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Apaga e recria os planos por frequência a cada save - mais simples e
     * seguro que tentar casar linhas existentes por ID (mesmo padrão usado
     * em Proposal::phases, ver ProposalRepository::syncChildren()). O
     * formulário manda um preço por frequência conhecida
     * (pricing_plans[monthly], pricing_plans[annual]...), vazio = não
     * oferece essa frequência.
     */
    private function syncPricingPlans(Service $service, $request): void
    {
        $service->pricingPlans()->delete();

        $allowedFrequencies = array_keys(BillingPolicy::frequencies());

        foreach ($request->input('pricing_plans', []) as $frequency => $price) {
            if ($price === null || $price === '' || ! in_array($frequency, $allowedFrequencies, true)) {
                continue;
            }

            $service->pricingPlans()->create([
                'frequency' => $frequency,
                'price' => $price,
            ]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $service = $this->model->findOrFail($id);
            $service->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Serviço removido com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function status($id, $status)
    {
        $service = $this->model->findOrFail($id);
        $service->update(['status' => $status]);

        return json_encode(["resp" => $service]);
    }
}

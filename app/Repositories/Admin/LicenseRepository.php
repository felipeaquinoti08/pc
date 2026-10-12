<?php

namespace App\Repositories\Admin;

use Exception;
use App\Models\License;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Eloquent\BaseRepository;

class LicenseRepository extends BaseRepository
{
    function model()
    {
        return License::class;
    }

    public function index()
    {
        return view('admin.license.index');
    }

    public function create($attribute = [])
    {
        return view('admin.license.create', [
            'customers' => \App\Models\Customer::orderBy('name')->get(),
            'services' => \App\Models\Service::orderBy('name')->get(),
        ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $license = $this->model->create([
                'customer_id' => $request->customer_id,
                'license_key' => License::generateKey(),
                'quantity' => $request->quantity,
                'starts_at' => $request->starts_at,
                'expires_at' => $request->expires_at,
                'status' => $request->status,
                'notes' => $request->notes,
                'created_by_id' => Auth::id(),
            ]);

            $license->services()->sync($request->service_ids);

            DB::commit();
            return redirect()->route('admin.license.index')->with('success', 'Licenciamento cadastrado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $license = $this->model->with(['activations.service', 'services'])->find($id);
        return view('admin.license.edit', [
            'license' => $license,
            'customers' => \App\Models\Customer::orderBy('name')->get(),
            'services' => \App\Models\Service::orderBy('name')->get(),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $license = $this->model->findOrFail($id);
            $license->update([
                'customer_id' => $request->customer_id,
                'quantity' => $request->quantity,
                'starts_at' => $request->starts_at,
                'expires_at' => $request->expires_at,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            $license->services()->sync($request->service_ids);

            DB::commit();
            return redirect()->route('admin.license.index')->with('success', 'Licenciamento atualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $license = $this->model->findOrFail($id);
            $license->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Licenciamento removido com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Manually free up an activation slot (see App\Models\License -
     * activations are only released by an admin, never automatically).
     */
    public function revokeActivation($licenseId, $activationId)
    {
        $license = $this->model->findOrFail($licenseId);
        $activation = $license->activations()->findOrFail($activationId);
        $activation->update(['status' => \App\Models\LicenseActivation::STATUS_REVOKED]);

        return redirect()->back()->with('success', __('Instalação revogada. A vaga já pode ser usada por outra instalação.'));
    }
}

<?php

namespace App\Repositories\Admin;

use Exception;
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
            $this->model->create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'billing_type' => $request->billing_type,
                'status' => $request->status ?? 1,
                'created_by_id' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('admin.service.index')->with('success', 'Serviço cadastrado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $service = $this->model->find($id);
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
                'description' => $request->description,
                'price' => $request->price,
                'billing_type' => $request->billing_type,
                'status' => $request->status ?? 1,
            ]);

            DB::commit();
            return redirect()->route('admin.service.index')->with('success', 'Serviço atualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
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

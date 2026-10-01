<?php

namespace App\Repositories\Admin;

use Exception;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Eloquent\BaseRepository;

class CustomerRepository extends BaseRepository
{
    function model()
    {
        return Customer::class;
    }

    public function index()
    {
        return view('admin.customer.index');
    }

    public function create($attribute = [])
    {
        return view('admin.customer.create');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $this->model->create([
                'name' => $request->name,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'number' => $request->no_number ? null : $request->number,
                'no_number' => (bool) $request->no_number,
                'complement' => $request->complement,
                'neighborhood' => $request->neighborhood,
                'city' => $request->city,
                'nfse_municipio_codigo' => $request->nfse_municipio_codigo,
                'postal_code' => $request->postal_code,
                'country_id' => $request->country_id,
                'state' => $request->state,
                'status' => $request->status ?? 1,
                'notes' => $request->notes,
                'created_by_id' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('admin.customer.index')->with('success', 'Cliente cadastrado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $customer = $this->model->find($id);
        return view('admin.customer.edit', [
            'customer' => $customer,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $customer = $this->model->findOrFail($id);
            $customer->update([
                'name' => $request->name,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'number' => $request->no_number ? null : $request->number,
                'no_number' => (bool) $request->no_number,
                'complement' => $request->complement,
                'neighborhood' => $request->neighborhood,
                'city' => $request->city,
                'nfse_municipio_codigo' => $request->nfse_municipio_codigo,
                'postal_code' => $request->postal_code,
                'country_id' => $request->country_id,
                'state' => $request->state,
                'status' => $request->status ?? 1,
                'notes' => $request->notes,
            ]);

            DB::commit();
            return redirect()->route('admin.customer.index')->with('success', 'Cliente atualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $customer = $this->model->findOrFail($id);
            $customer->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Cliente removido com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function status($id, $status)
    {
        $customer = $this->model->findOrFail($id);
        $customer->update(['status' => $status]);

        return json_encode(["resp" => $customer]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\DataTables\CustomerDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\CustomerRepository;
use App\Http\Requests\Admin\CreateCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Services\CnpjLookupService;

class CustomerController extends Controller
{
    public $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->authorizeResource(Customer::class, 'customer');
        $this->repository = $repository;
    }

    public function index(CustomerDataTable $dataTable)
    {
        return $dataTable->render('admin.customer.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateCustomerRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(Customer $customer)
    {
        //
    }

    public function edit(Customer $customer)
    {
        return $this->repository->edit($customer->id);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return $this->repository->update($request, $customer->id);
    }

    public function destroy(Customer $customer)
    {
        return $this->repository->destroy($customer->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    /**
     * AJAX chamado do formulário de Cliente ao digitar um CNPJ - ver
     * App\Services\CnpjLookupService e admin/customer/scripts.blade.php.
     */
    public function cnpjLookup(string $cnpj, CnpjLookupService $service)
    {
        $data = $service->lookup($cnpj);

        if (! $data) {
            return response()->json(['success' => false, 'message' => 'CNPJ não encontrado ou inválido.'], 404);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}

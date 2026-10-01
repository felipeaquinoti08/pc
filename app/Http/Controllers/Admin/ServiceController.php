<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use Illuminate\Http\Request;
use App\DataTables\ServiceDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\ServiceRepository;
use App\Http\Requests\Admin\CreateServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;

class ServiceController extends Controller
{
    public $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->authorizeResource(Service::class, 'service');
        $this->repository = $repository;
    }

    public function index(ServiceDataTable $dataTable)
    {
        return $dataTable->render('admin.service.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateServiceRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(Service $service)
    {
        //
    }

    public function edit(Service $service)
    {
        return $this->repository->edit($service->id);
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        return $this->repository->update($request, $service->id);
    }

    public function destroy(Service $service)
    {
        return $this->repository->destroy($service->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }
}

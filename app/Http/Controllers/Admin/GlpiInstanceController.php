<?php

namespace App\Http\Controllers\Admin;

use App\Models\GlpiInstance;
use Illuminate\Http\Request;
use App\DataTables\GlpiInstanceDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\GlpiInstanceRepository;
use App\Http\Requests\Admin\CreateGlpiInstanceRequest;
use App\Http\Requests\Admin\UpdateGlpiInstanceRequest;

class GlpiInstanceController extends Controller
{
    public $repository;

    public function __construct(GlpiInstanceRepository $repository)
    {
        $this->authorizeResource(GlpiInstance::class, 'glpi_instance');
        $this->repository = $repository;
    }

    public function index(GlpiInstanceDataTable $dataTable)
    {
        return $dataTable->render('admin.glpi_instance.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateGlpiInstanceRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(GlpiInstance $glpi_instance)
    {
        //
    }

    public function edit(GlpiInstance $glpi_instance)
    {
        return $this->repository->edit($glpi_instance->id);
    }

    public function update(UpdateGlpiInstanceRequest $request, GlpiInstance $glpi_instance)
    {
        return $this->repository->update($request, $glpi_instance->id);
    }

    public function destroy(GlpiInstance $glpi_instance)
    {
        return $this->repository->destroy($glpi_instance->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function testConnection(GlpiInstance $glpi_instance)
    {
        $this->authorize('update', $glpi_instance);

        return $this->repository->testConnection($glpi_instance->id);
    }
}

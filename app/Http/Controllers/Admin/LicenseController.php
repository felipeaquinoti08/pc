<?php

namespace App\Http\Controllers\Admin;

use App\Models\License;
use Illuminate\Http\Request;
use App\DataTables\LicenseDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\LicenseRepository;
use App\Http\Requests\Admin\CreateLicenseRequest;
use App\Http\Requests\Admin\UpdateLicenseRequest;

class LicenseController extends Controller
{
    public $repository;

    public function __construct(LicenseRepository $repository)
    {
        $this->authorizeResource(License::class, 'license');
        $this->repository = $repository;
    }

    public function index(LicenseDataTable $dataTable)
    {
        return $dataTable->render('admin.license.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateLicenseRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(License $license)
    {
        //
    }

    public function edit(License $license)
    {
        return $this->repository->edit($license->id);
    }

    public function update(UpdateLicenseRequest $request, License $license)
    {
        return $this->repository->update($request, $license->id);
    }

    public function destroy(License $license)
    {
        return $this->repository->destroy($license->id);
    }

    public function revokeActivation(Request $request, License $license, $activationId)
    {
        $this->authorize('update', $license);

        return $this->repository->revokeActivation($license->id, $activationId);
    }
}

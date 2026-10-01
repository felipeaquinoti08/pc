<?php

namespace App\Http\Controllers\Admin;

use App\Models\BillingPolicy;
use App\DataTables\BillingPolicyDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\BillingPolicyRepository;
use App\Http\Requests\Admin\CreateBillingPolicyRequest;
use App\Http\Requests\Admin\UpdateBillingPolicyRequest;

class BillingPolicyController extends Controller
{
    public $repository;

    public function __construct(BillingPolicyRepository $repository)
    {
        $this->authorizeResource(BillingPolicy::class, 'billing_policy');
        $this->repository = $repository;
    }

    public function index(BillingPolicyDataTable $dataTable)
    {
        return $dataTable->render('admin.billing_policy.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateBillingPolicyRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(BillingPolicy $billing_policy)
    {
        //
    }

    public function edit(BillingPolicy $billing_policy)
    {
        return $this->repository->edit($billing_policy->id);
    }

    public function update(UpdateBillingPolicyRequest $request, BillingPolicy $billing_policy)
    {
        return $this->repository->update($request, $billing_policy->id);
    }

    public function destroy(BillingPolicy $billing_policy)
    {
        return $this->repository->destroy($billing_policy->id);
    }

    public function reconcile(BillingPolicy $billing_policy)
    {
        $this->authorize('update', $billing_policy);

        return $this->repository->reconcileInvoices($billing_policy->id);
    }
}

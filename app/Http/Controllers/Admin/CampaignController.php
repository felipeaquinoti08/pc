<?php

namespace App\Http\Controllers\Admin;

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\DataTables\CampaignDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\CampaignRepository;
use App\Http\Requests\Admin\CreateCampaignRequest;
use App\Http\Requests\Admin\UpdateCampaignRequest;

class CampaignController extends Controller
{
    public $repository;

    public function __construct(CampaignRepository $repository)
    {
        $this->authorizeResource(Campaign::class, 'campaign');
        $this->repository = $repository;
    }

    public function index(CampaignDataTable $dataTable)
    {
        return $dataTable->render('admin.campaign.index');
    }

    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateCampaignRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(Campaign $campaign)
    {
        //
    }

    public function edit(Campaign $campaign)
    {
        return $this->repository->edit($campaign->id);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        return $this->repository->update($request, $campaign->id);
    }

    public function destroy(Campaign $campaign)
    {
        return $this->repository->destroy($campaign->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }
}

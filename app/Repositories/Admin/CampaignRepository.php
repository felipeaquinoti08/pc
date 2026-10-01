<?php

namespace App\Repositories\Admin;

use Exception;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Eloquent\BaseRepository;

class CampaignRepository extends BaseRepository
{
    function model()
    {
        return Campaign::class;
    }

    public function index()
    {
        return view('admin.campaign.index');
    }

    public function create($attribute = [])
    {
        return view('admin.campaign.create', [
            'services' => \App\Models\Service::orderBy('name')->get(),
        ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $campaign = $this->model->create([
                'name' => $request->name,
                'slug' => $request->slug ?: Campaign::generateSlug($request->name),
                'service_id' => $request->service_id,
                'description' => $request->description,
                'trial_days' => $request->trial_days,
                'quantity' => $request->quantity,
                'repository_url' => $request->repository_url,
                'instructions' => $request->instructions,
                'whatsapp_number' => $request->whatsapp_number,
                'status' => $request->status ?? 1,
                'created_by_id' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('admin.campaign.edit', $campaign->id)->with('success', 'Campanha cadastrada com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $campaign = $this->model->withCount('licenses')->find($id);
        return view('admin.campaign.edit', [
            'campaign' => $campaign,
            'services' => \App\Models\Service::orderBy('name')->get(),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $campaign = $this->model->findOrFail($id);
            $campaign->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'service_id' => $request->service_id,
                'description' => $request->description,
                'trial_days' => $request->trial_days,
                'quantity' => $request->quantity,
                'repository_url' => $request->repository_url,
                'instructions' => $request->instructions,
                'whatsapp_number' => $request->whatsapp_number,
                'status' => $request->status ?? 1,
            ]);

            DB::commit();
            return redirect()->route('admin.campaign.index')->with('success', 'Campanha atualizada com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $campaign = $this->model->findOrFail($id);
            $campaign->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Campanha removida com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function status($id, $status)
    {
        $campaign = $this->model->findOrFail($id);
        $campaign->update(['status' => $status]);

        return json_encode(["resp" => $campaign]);
    }
}

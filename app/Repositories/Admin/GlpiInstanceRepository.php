<?php

namespace App\Repositories\Admin;

use Exception;
use App\Models\GlpiInstance;
use App\Services\Glpi\GlpiApiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Eloquent\BaseRepository;

class GlpiInstanceRepository extends BaseRepository
{
    function model()
    {
        return GlpiInstance::class;
    }

    public function index()
    {
        return view('admin.glpi_instance.index');
    }

    public function create($attribute = [])
    {
        return view('admin.glpi_instance.create');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $this->model->create([
                'name' => $request->name,
                'base_url' => rtrim($request->base_url, '/'),
                'oauth_client_id' => $request->oauth_client_id,
                'oauth_client_secret' => $request->oauth_client_secret,
                'username' => $request->username,
                'password' => $request->password,
                'entities_id' => $request->entities_id ?? 0,
                'status' => $request->status ?? 1,
                'notes' => $request->notes,
                'created_by_id' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('admin.glpi_instance.index')->with('success', 'Instância GLPI cadastrada com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        return view('admin.glpi_instance.edit', [
            'glpiInstance' => $this->model->findOrFail($id),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $instance = $this->model->findOrFail($id);
            $instance->update([
                'name' => $request->name,
                'base_url' => rtrim($request->base_url, '/'),
                'oauth_client_id' => $request->oauth_client_id,
                // Em branco no form = mantém o segredo já salvo.
                'oauth_client_secret' => filled($request->oauth_client_secret) ? $request->oauth_client_secret : $instance->oauth_client_secret,
                'username' => $request->username,
                'password' => filled($request->password) ? $request->password : $instance->password,
                'entities_id' => $request->entities_id ?? 0,
                'status' => $request->status ?? 1,
                'notes' => $request->notes,
            ]);

            DB::commit();
            return redirect()->route('admin.glpi_instance.index')->with('success', 'Instância GLPI atualizada com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $instance = $this->model->findOrFail($id);
            $instance->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Instância GLPI removida com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function status($id, $status)
    {
        $instance = $this->model->findOrFail($id);
        $instance->update(['status' => $status]);

        return json_encode(["resp" => $instance]);
    }

    public function testConnection($id)
    {
        $instance = $this->model->findOrFail($id);
        $result = app(GlpiApiClient::class, ['instance' => $instance])->testConnection();

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}

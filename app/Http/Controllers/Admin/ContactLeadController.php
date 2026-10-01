<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactLead;
use Illuminate\Http\Request;
use App\DataTables\ContactLeadDataTable;
use App\Http\Controllers\Controller;

class ContactLeadController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ContactLead::class, 'contact_lead');
    }

    public function index(ContactLeadDataTable $dataTable)
    {
        return $dataTable->render('admin.contact_lead.index');
    }

    public function show(ContactLead $contact_lead)
    {
        if (! $contact_lead->read_at) {
            $contact_lead->update(['read_at' => now()]);
        }

        $contact_lead->load('customer');

        return view('admin.contact_lead.show', ['contactLead' => $contact_lead]);
    }

    public function destroy(ContactLead $contact_lead)
    {
        $contact_lead->delete();

        return redirect()->route('admin.contact_lead.index')->with('success', __('Contato removido com sucesso.'));
    }

    public function updateStatus(Request $request, ContactLead $contact_lead)
    {
        $this->authorize('update', $contact_lead);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(ContactLead::statuses())),
        ]);

        $contact_lead->update(['status' => $request->input('status')]);

        return response()->json(['status' => 'ok']);
    }
}

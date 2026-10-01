<?php

namespace App\Http\Controllers\Api;

use App\Mail\ContactLeadReceived;
use App\Models\ContactLead;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class ContactLeadController extends Controller
{
    /**
     * Public endpoint hit by the CCITI site's contact form. Matches the
     * visitor's e-mail against an existing Customer (created here on first
     * contact) so every lead ends up attached to a customer record instead
     * of floating on its own.
     */
    public function store(Request $request)
    {
        // Honeypot: a hidden field real visitors never fill in. Bots that
        // blindly fill every input trip it; we pretend to succeed so they
        // don't learn to avoid the field.
        if ($request->filled('website')) {
            return response()->json(['status' => 'ok']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        if (! $customer) {
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => 1,
                'notes' => 'Cliente criado automaticamente a partir de um contato pelo site.',
            ]);
        }

        $contactLead = ContactLead::create([
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'message' => $validated['message'] ?? null,
            'source' => $validated['source'] ?? $request->header('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if (Setting::current()->mailConfigured()) {
            try {
                Mail::to('felipe.aquino@cciti.com.br')->send(new ContactLeadReceived($contactLead));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\License;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Public trial-license signup page. One per Campaign, reachable at
     * /campanha/{slug} regardless of the campaign's status - a closed
     * campaign still resolves so its link doesn't just 404, it shows a
     * "closed" message with the WhatsApp contact instead.
     */
    public function show(Campaign $campaign)
    {
        return view('campaign.show', ['campaign' => $campaign]);
    }

    /**
     * Validates the signup, finds-or-creates the Customer (matched by
     * e-mail, same approach as Api\ContactLeadController) and issues a
     * trial License scoped to the campaign's service/duration/quantity.
     */
    public function store(Request $request, Campaign $campaign)
    {
        if (! $campaign->isAcceptingSignups()) {
            return redirect()->route('campaign.show', $campaign->slug)
                ->with('error', 'Esta campanha não está mais aceitando cadastros.');
        }

        // Honeypot: hidden field real visitors never fill in (see
        // Api\ContactLeadController for the same pattern). Pretend to
        // succeed so bots don't learn to avoid it.
        if ($request->filled('website')) {
            return redirect()->route('campaign.show', $campaign->slug);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'document_type' => ['nullable', 'in:cpf,cnpj'],
            'document_number' => ['nullable', 'string', 'max:32'],
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        if (! $customer) {
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'document_type' => $validated['document_type'] ?? null,
                'document_number' => $validated['document_number'] ?? null,
                'status' => 1,
                'notes' => 'Cliente criado automaticamente pela campanha "' . $campaign->name . '".',
            ]);
        }

        // One trial per customer per campaign - stop a re-submission from
        // silently minting a second license (the key itself isn't shown
        // again here, only a generic notice, so this can't be used to leak
        // someone else's key by resubmitting their e-mail).
        if ($campaign->licenses()->where('customer_id', $customer->id)->exists()) {
            return redirect()->route('campaign.show', $campaign->slug)
                ->with('error', 'Já existe uma licença de teste emitida para este e-mail nesta campanha. Verifique sua caixa de entrada ou fale conosco pelo WhatsApp.');
        }

        $license = License::create([
            'customer_id' => $customer->id,
            'service_id' => $campaign->service_id,
            'campaign_id' => $campaign->id,
            'license_key' => License::generateKey(),
            'quantity' => $campaign->quantity,
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays($campaign->trial_days)->toDateString(),
            'status' => License::STATUS_ACTIVE,
            'notes' => 'Licença de teste — Campanha: ' . $campaign->name,
        ]);

        return redirect()->route('campaign.success', ['campaign' => $campaign->slug, 'license' => $license->license_key]);
    }

    /**
     * Shown once right after signup (and revisitable via the bookmarkable
     * URL, since license_key is itself an unguessable secret - see
     * App\Models\License::generateKey()). There's no customer panel yet,
     * so this is the only place the key is ever surfaced - the page makes
     * a point of telling the visitor to save it.
     */
    public function success(Campaign $campaign, License $license)
    {
        abort_unless($license->campaign_id === $campaign->id, 404);

        return view('campaign.success', ['campaign' => $campaign, 'license' => $license]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LicenseValidationController extends Controller
{
    /**
     * Called by a licensed plugin every ~10 minutes.
     *
     * Request:  {license_key, instance_identifier, instance_url?}
     * Response: {valid: bool, reason: string, quantity?, active_installations?}
     *
     * reason is one of: ok, invalid_key, suspended, cancelled, expired,
     * limit_exceeded.
     */
    public function validateLicense(Request $request)
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'instance_identifier' => ['required', 'string', 'max:191'],
            'instance_url' => ['nullable', 'string', 'max:255'],
        ]);

        $license = License::where('license_key', $validated['license_key'])->first();

        if (! $license) {
            return response()->json(['valid' => false, 'reason' => 'invalid_key'], 404);
        }

        if (in_array($license->status, [License::STATUS_SUSPENDED, License::STATUS_CANCELLED], true)) {
            return response()->json(['valid' => false, 'reason' => $license->status], 403);
        }

        if ($license->expires_at !== null && $license->expires_at->isPast() && ! $license->expires_at->isToday()) {
            return response()->json(['valid' => false, 'reason' => 'expired'], 403);
        }

        $activation = LicenseActivation::where('license_id', $license->id)
            ->where('instance_identifier', $validated['instance_identifier'])
            ->first();

        if ($activation && $activation->status === LicenseActivation::STATUS_ACTIVE) {
            $activation->update([
                'instance_url' => $validated['instance_url'] ?? $activation->instance_url,
                'ip_address' => $request->ip(),
                'last_seen_at' => now(),
            ]);

            return response()->json([
                'valid' => true,
                'reason' => 'ok',
                'quantity' => $license->quantity,
                'active_installations' => $license->activeActivations()->count(),
            ]);
        }

        // New instance (or a previously revoked one checking in again) -
        // only allowed if there's room under the contracted quantity.
        $activeCount = $license->activeActivations()->count();
        if ($activeCount >= $license->quantity) {
            return response()->json([
                'valid' => false,
                'reason' => 'limit_exceeded',
                'quantity' => $license->quantity,
                'active_installations' => $activeCount,
            ], 403);
        }

        LicenseActivation::updateOrCreate(
            [
                'license_id' => $license->id,
                'instance_identifier' => $validated['instance_identifier'],
            ],
            [
                'instance_url' => $validated['instance_url'] ?? null,
                'ip_address' => $request->ip(),
                'status' => LicenseActivation::STATUS_ACTIVE,
                'activated_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'valid' => true,
            'reason' => 'ok',
            'quantity' => $license->quantity,
            'active_installations' => $activeCount + 1,
        ]);
    }
}

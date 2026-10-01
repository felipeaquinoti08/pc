<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftLoginController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $setting = Setting::current();

        if (! $setting->ssoConfigured()) {
            return redirect()->route('login.local')->with('error', 'Login com Microsoft ainda não foi configurado.');
        }

        $this->configureSocialite($setting);

        return Socialite::driver('microsoft')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $setting = Setting::current();

        if (! $setting->ssoConfigured()) {
            return redirect()->route('login.local')->with('error', 'Login com Microsoft ainda não foi configurado.');
        }

        $this->configureSocialite($setting);

        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Não foi possível concluir o login com a Microsoft. Tente novamente.');
        }

        $user = $this->resolveUser($microsoftUser);

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    private function configureSocialite(Setting $setting): void
    {
        Config::set('services.microsoft', [
            'client_id' => $setting->sso_client_id,
            'client_secret' => $setting->sso_client_secret,
            'redirect' => route('auth.microsoft.callback'),
            'tenant' => $setting->sso_tenant_id,
            // Fetched as base64 alongside the /me profile call - lets us
            // keep the panel avatar in sync with the Microsoft profile photo
            // (see syncAvatar()) without a second round-trip.
            'include_avatar' => true,
        ]);
    }

    private function resolveUser(\Laravel\Socialite\Two\User $microsoftUser): User
    {
        $microsoftId = $microsoftUser->getId();
        $email = $microsoftUser->getEmail() ?: $microsoftUser->user['mail'] ?? null;

        $user = User::where('microsoft_id', $microsoftId)->first();

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        $profile = [
            'first_name' => $microsoftUser->user['givenName'] ?? $microsoftUser->getName(),
            'last_name' => $microsoftUser->user['surname'] ?? '',
        ];

        if ($user) {
            // Existing account (created locally or by an earlier SSO login) -
            // keep name in sync with Entra on every login.
            $user->fill($profile);
            if (! $user->microsoft_id) {
                $user->microsoft_id = $microsoftId;
            }
            $user->save();
        } else {
            // First login from someone in the company's Entra tenant who has
            // no panel account yet - auto-provision with no role, so they
            // can sign in but see/do nothing until an admin grants a role
            // (Configurações → Acessos), same as any other permissionless
            // account today.
            $user = User::create(array_merge($profile, [
                'microsoft_id' => $microsoftId,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'status' => 1,
            ]));
        }

        $this->syncAvatar($user, $microsoftUser);

        return $user;
    }

    /**
     * Best-effort: keep the avatar in sync with the Microsoft profile photo.
     * Never blocks login - if cloud storage isn't configured yet, there's no
     * photo on the profile, or the save fails for any reason, the user just
     * keeps whatever avatar they already had (or none, which the UI already
     * renders as an initial-letter placeholder wherever getFirstMedia('image')
     * is null - see UserDataTable::dataTable(), admin/user/fields.blade.php).
     */
    private function syncAvatar(User $user, \Laravel\Socialite\Two\User $microsoftUser): void
    {
        if (! Setting::current()->blobConfigured()) {
            return;
        }

        $avatarBase64 = $microsoftUser->user['avatar'] ?? null;

        if (! $avatarBase64) {
            \Illuminate\Support\Facades\Log::info('Microsoft SSO: nenhuma foto de perfil retornada para '.$user->email.' - mantendo avatar atual.');

            return;
        }

        $tempPath = null;

        try {
            $binary = base64_decode($avatarBase64, true);

            if ($binary === false) {
                return;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'ms-avatar').'.jpg';
            file_put_contents($tempPath, $binary);

            $user->clearMediaCollection('image');
            $user->addMedia($tempPath)->usingName('avatar')->toMediaCollection('image');
        } catch (\Throwable $e) {
            // Photo sync failed - login still proceeds with the default avatar.
            \Illuminate\Support\Facades\Log::warning('Microsoft SSO: falha ao sincronizar foto de perfil para '.$user->email.': '.$e->getMessage());
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}

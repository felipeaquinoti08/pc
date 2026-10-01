<?php

namespace App\Providers;

use App\Enums\RoleEnum;
use App\Models\BankDetail;
use App\Models\Banner;
use App\Models\BillingPolicy;
use App\Models\Bid;
use App\Models\Campaign;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\ContactLead;
use App\Models\Document;
use App\Models\GlpiInstance;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Proposal;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\SystemLang;
use App\Models\Tax;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Models\Zone;
use App\Policies\BankDetailPolicy;
use App\Policies\BannerPolicy;
use App\Policies\BillingPolicyPolicy;
use App\Policies\BidPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\CurrencyPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ContactLeadPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\GlpiInstancePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LicensePolicy;
use App\Policies\ProposalPolicy;
use App\Policies\ServicePackagePolicy;
use App\Policies\ServicePolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\SettingPolicy;
use App\Policies\SystemLangPolicy;
use App\Policies\TaxPolicy;
use App\Policies\TimeSlotPolicy;
use App\Policies\UserPolicy;
use App\Policies\WithdrawRequestPolicy;
use App\Policies\ZonePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Subscription\Entities\Plan;
use Modules\Subscription\Entities\UserSubscription;
use Modules\Subscription\Policies\PlanPolicy;
use Modules\Subscription\Policies\SubscriptionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Customer::class => CustomerPolicy::class,
        ContactLead::class => ContactLeadPolicy::class,
        Currency::class => CurrencyPolicy::class,
        Document::class => DocumentPolicy::class,
        Service::class => ServicePolicy::class,
        License::class => LicensePolicy::class,
        Campaign::class => CampaignPolicy::class,
        GlpiInstance::class => GlpiInstancePolicy::class,
        Invoice::class => InvoicePolicy::class,
        BillingPolicy::class => BillingPolicyPolicy::class,
        Proposal::class => ProposalPolicy::class,
        SystemLang::class => SystemLangPolicy::class,
        ServicePackage::class => ServicePackagePolicy::class,
        TimeSlot::class => TimeSlotPolicy::class,
        BankDetail::class => BankDetailPolicy::class,
        Plan::class => PlanPolicy::class,
        Banner::class => BannerPolicy::class,
        Setting::class => SettingPolicy::class,
        UserSubscription::class => SubscriptionPolicy::class,
        WithdrawRequest::class => WithdrawRequestPolicy::class,
        Tax::class => TaxPolicy::class,
        Zone::class => ZonePolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
        Bid::class => BidPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // Implicitly grant "Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RoleEnum::ADMIN) ? true : null;
        });
    }
}

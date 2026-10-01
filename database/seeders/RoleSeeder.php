<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'users' => [
                'index'   => 'user.index',
                'create'  => 'user.create',
                'edit'    => 'user.edit',
                'trash'   => 'user.destroy',
                'restore' => 'user.restore',
                'delete'  => 'user.forceDelete',
            ],
            'roles' => [
                'index'  => 'role.index',
                'create' => 'role.create',
                'edit'   => 'role.edit',
                'delete' => 'role.destroy',
            ],
            'attachments' => [
                'index'  => 'attachment.index',
                'create' => 'attachment.create',
                'delete' => 'attachment.destroy',
            ],
            'customers' => [
                'index'   => 'customer.index',
                'create'  => 'customer.create',
                'edit'    => 'customer.edit',
                'trash'   => 'customer.destroy',
                'restore' => 'customer.restore',
                'delete'  => 'customer.forceDelete',
            ],
            'contact_leads' => [
                'index'   => 'contact_lead.index',
                'edit'    => 'contact_lead.edit',
                'delete'  => 'contact_lead.destroy',
            ],
            'services' => [
                'index'   => 'service.index',
                'create'  => 'service.create',
                'edit'    => 'service.edit',
                'trash'   => 'service.destroy',
                'restore' => 'service.restore',
                'delete'  => 'service.forceDelete',
            ],
            'licenses' => [
                'index'   => 'license.index',
                'create'  => 'license.create',
                'edit'    => 'license.edit',
                'trash'   => 'license.destroy',
                'restore' => 'license.restore',
                'delete'  => 'license.forceDelete',
            ],
            'campaigns' => [
                'index'   => 'campaign.index',
                'create'  => 'campaign.create',
                'edit'    => 'campaign.edit',
                'trash'   => 'campaign.destroy',
                'restore' => 'campaign.restore',
                'delete'  => 'campaign.forceDelete',
            ],
            'glpi_instances' => [
                'index'   => 'glpi_instance.index',
                'create'  => 'glpi_instance.create',
                'edit'    => 'glpi_instance.edit',
                'trash'   => 'glpi_instance.destroy',
                'restore' => 'glpi_instance.restore',
                'delete'  => 'glpi_instance.forceDelete',
            ],
            'glpi_asset_import' => [
                'create' => 'glpi_asset_import.create',
            ],
            'proposals' => [
                'index'   => 'proposal.index',
                'create'  => 'proposal.create',
                'edit'    => 'proposal.edit',
                'trash'   => 'proposal.destroy',
                'restore' => 'proposal.restore',
                'delete'  => 'proposal.forceDelete',
            ],
            'billing_policies' => [
                'index'   => 'billing_policy.index',
                'create'  => 'billing_policy.create',
                'edit'    => 'billing_policy.edit',
                'trash'   => 'billing_policy.destroy',
                'restore' => 'billing_policy.restore',
                'delete'  => 'billing_policy.forceDelete',
            ],
            'invoices' => [
                'index'   => 'invoice.index',
                'create'  => 'invoice.create', // used by the NFS-e XML import flow only
                'edit'    => 'invoice.edit',
                'trash'   => 'invoice.destroy',
                'restore' => 'invoice.restore',
                'delete'  => 'invoice.forceDelete',
            ],
            'settings' => [
                'index' => 'setting.index',
                'edit'  => 'setting.edit',
            ],
        ];

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($modules as $name => $actions) {
            Module::updateOrCreate(['name' => $name], ['name' => $name, 'actions' => $actions]);

            foreach ($actions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }
        }

        // Content (pages/blog/tags/categories) was removed from the app -
        // drop whatever modules/permissions were previously seeded for it
        // so they don't linger in the Roles screen forever.
        $removedModules = ['categories', 'tags', 'blogs', 'pages'];
        $removedPermissionPrefixes = ['category.', 'tag.', 'blog.', 'page.'];
        Module::whereIn('name', $removedModules)->delete();
        Permission::where(function ($query) use ($removedPermissionPrefixes) {
            foreach ($removedPermissionPrefixes as $prefix) {
                $query->orWhere('name', 'like', $prefix . '%');
            }
        })->delete();

        // Single "admin" role for now (see App\Providers\AuthServiceProvider::boot(),
        // which grants it every ability via Gate::before regardless of explicit
        // permissions). Business-specific roles (manager, attendant...) can be
        // added later through the admin panel's own Role screen once the team
        // structure is defined.
        $admin = Role::updateOrCreate(
            ['name' => RoleEnum::ADMIN, 'guard_name' => 'web'],
            ['system_reserve' => true]
        );
        $admin->syncPermissions(Permission::all());

        User::updateOrCreate(
            ['email' => 'felipe.aquino@cciti.com.br'],
            [
                'first_name' => 'Felipe',
                'last_name' => 'Aquino',
                'password' => Hash::make('trocar123'),
                'status' => 1,
                'system_reserve' => true,
            ]
        )->assignRole($admin);
    }
}

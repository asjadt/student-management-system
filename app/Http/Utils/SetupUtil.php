<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

trait SetupUtil
{

    // PERMISSION AND ROLE REFRESH
    public function roleRefreshFunc()
    {
        // GET PERMISSIONS
        $permissions =  config("setup-config.permissions");

        // IF NOT EXIST THEN CREATE NEW ONE
        foreach ($permissions as $permission) {
            if (!Permission::where([
                'name' => $permission,
                'guard_name' => 'api'
            ])
                ->exists()) {
                Permission::create(['guard_name' => 'api', 'name' => $permission]);
            }
        }

        // GET ALL ROLES
        $roles = config("setup-config.roles");

        // IF NOT EXIST THEN CREATE NEW ONE
        foreach ($roles as $role) {
            if (!Role::where([
                'name' => $role,
                'guard_name' => 'api',
                "is_system_default" => 1,
                "business_id" => NULL,
                "is_default" => 1,
            ])
                ->exists()) {
                Role::create([
                    'guard_name' => 'api',
                    'name' => $role,
                    "is_system_default" => 1,
                    "business_id" => NULL,
                    "is_default" => 1,
                    "is_default_for_business" => (in_array($role, [

                        "business_owner",
                        "business_admin",
                        "business_student",
                        "business_teacher",
                        "agency"
                    ]) ? 1 : 0)


                ]);
            }
        }



        // GET ROLE PERMISSIONS
        $role_permissions = config("setup-config.roles_permission");

        foreach ($role_permissions as $role_permission) {
            $role = Role::where(["name" => $role_permission["role"]])->first();

            // If role doesn't exist, skip or log it
            if (!$role) {
                continue;
            }

            $permissions = $role_permission["permissions"];

            // Get current permissions associated with the role
            $currentPermissions = $role->permissions()->pluck('name')->toArray();

            // Determine permissions to remove
            $permissionsToRemove = array_diff($currentPermissions, $permissions);

            // De assign permissions not included in the configuration
            if (!empty($permissionsToRemove)) {
                foreach ($permissionsToRemove as $permission) {
                    $role->revokePermissionTo($permission);
                }
            }
            // Assign permissions from the configuration
            $role->syncPermissions($permissions);
        }



        // GET ALL BUSINESS IDS
        $business_ids = Business::get()->pluck("id");

        // BUSINESS ROLE AND PERMISSIONS REFRESH
        foreach ($role_permissions as $role_permission) {

            foreach ($business_ids as $business_id) {

                $role = Role::where(["name" => $role_permission["role"] . "#" . $business_id])->first();

                if (empty($role)) {
                    $role = Role::create([
                        'guard_name' => 'api',
                        'name' => ($role_permission["role"] . "#" . $business_id),
                        "is_system_default" => 1,
                        "business_id" => $business_id,
                        "is_default" => 1,
                        "is_default_for_business" => 0
                    ]);
                }

                $permissions = $role_permission["permissions"];

                // Assign permissions from the configuration
                $role->syncPermissions($permissions);
            }
        }
    }
}

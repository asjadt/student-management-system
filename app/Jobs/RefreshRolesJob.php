<?php

namespace App\Jobs;

use App\Mail\RolesRefreshStatusMail;
use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RefreshRolesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // ###############################
            // permissions
            // ###############################
            $permissions =  config("setup-config.permissions");

            // setup permissions
            foreach ($permissions as $permission) {
                if (!Permission::where([
                    'name' => $permission,
                    'guard_name' => 'api'
                ])
                    ->exists()) {
                    Permission::create(['guard_name' => 'api', 'name' => $permission]);
                }
            }
            // setup roles
            $roles = config("setup-config.roles");
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



            // setup roles and permissions
            $role_permissions = config("setup-config.roles_permission");
            foreach ($role_permissions as $role_permission) {
                $role = Role::where(["name" => $role_permission["role"]])->first();

                $permissions = $role_permission["permissions"];


                // Get current permissions associated with the role
                $currentPermissions = $role->permissions()->pluck('name')->toArray();

                // Determine permissions to remove
                $permissionsToRemove = array_diff($currentPermissions, $permissions);

                // Deassign permissions not included in the configuration
                if (!empty($permissionsToRemove)) {
                    foreach ($permissionsToRemove as $permission) {
                        $role->revokePermissionTo($permission);
                    }
                }
                // Assign permissions from the configuration
                $role->syncPermissions($permissions);
            }


            $business_ids = Business::get()->pluck("id");

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

            // Success: log + email
            Log::info('Roles refresh job completed successfully.');
            // Mail::to('rony.mia7800@gmail.com')->send(new RolesRefreshStatusMail('success', 'Roles refresh completed successfully.'));
        } catch (\Exception $e) {
            // Failure: log + email
            Log::error('Roles refresh job failed: ' . $e->getMessage());
            // Mail::to('rony.mia7800@gmail.com')->send(new RolesRefreshStatusMail('failed', 'Roles refresh job failed'));

            throw $e; // ensures Laravel marks this job as failed
        }
        // -----------------------------

    }
}

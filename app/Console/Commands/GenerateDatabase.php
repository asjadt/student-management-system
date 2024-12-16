<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Str;

class GenerateDatabase extends Command
{
    // The name and signature of the console command.
    protected $signature = 'generate:database {business_id}';

    // The console command description.
    protected $description = 'Generate a new database for a given business ID';


    public function handle()
    {
        Log::info("Starting database creation process...");

        // Retrieve the business_id from the command input
        $businessId = $this->argument('business_id');

        $business = Business::where([
            "id" => $businessId
        ])
            ->first();


        $databaseName = 'svs_business_' . $businessId;

        Log::info("Business ID: $businessId");
        Log::info("Database name: $databaseName");

        // Check if the database exists


        try {
            // Fetch MySQL credentials from .env
            $adminUser = env('DB_USERNAME', 'root'); // Admin user with privileges
            $adminPassword = env('DB_PASSWORD', '');
            $host = env('DB_HOST', '127.0.0.1');

            if ($this->databaseExists($databaseName)) {
                Log::info("Database for business ID $businessId already exists.");
                $this->info("Database for business ID $businessId already exists.");
            } else {
                // Create a new database
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                Log::info("Database created successfully: {$databaseName}");

                // Grant privileges to the admin user
                DB::statement("GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$adminUser}'@'{$host}'");
                DB::statement("FLUSH PRIVILEGES");
            }



            $databaseName = 'svs_business_' . $businessId;
            $adminUser = env('DB_USERNAME', 'root'); // Admin user with privileges
            $adminPassword = env('DB_PASSWORD', '');

            // Dynamically set the default database connection configuration

            Config::set('database.connections.mysql.database', $databaseName);
            Config::set('database.connections.mysql.username', $adminUser);
            Config::set('database.connections.mysql.password', $adminPassword);

            // Reconnect to the database using the updated configuration
            DB::purge('mysql');
            DB::reconnect('mysql');



            // Run migrations on the new database
            Log::info("Running migrations on new database '{$databaseName}'");

            Artisan::call('migrate', [
                '--path' => 'database/business_migrations',
            ]);

        // Run Passport migrations
        Artisan::call('migrate', [
            '--path' => 'vendor/laravel/passport/database/migrations',
        ]);

        // Install Passport
        Artisan::call('passport:install');


            Log::info("Database for business ID $businessId has been created successfully.");





// Fetch user data
$userData = DB::connection('default')->table('users')
    ->where([
        "users.id" => $business->owner_id
    ])
    ->first();

// Convert object to array
$userDataArray = (array) $userData;

// Set created_by field to null
$userDataArray['created_by'] = null; // Set created_by to null

// Insert using DB query into the business database
DB::table('users')->insert($userDataArray);

$user = User::first(); // ###############################
        // permissions
        // ###############################
        $permissions =  config("setup-config.permissions");
        // setup permissions
        foreach ($permissions as $permission) {
            if(!DB::table('permissions')->where([
            'name' => $permission,
            'guard_name' => 'api'
            ])
            ->exists()){
                DB::table('permissions')->insert(['guard_name' => 'api', 'name' => $permission]);
            }

        }
        // setup roles
        $roles = config("setup-config.roles");
        foreach ($roles as $role) {
            if(!Role::where([
            'name' => $role,
            'guard_name' => 'api',
            "is_system_default" => 1,
            "business_id" => NULL,
            "is_default" => 1,
            ])
            ->exists()){
             Role::create(['guard_name' => 'api', 'name' => $role,"is_system_default"=> 1, "business_id" => NULL,
             "is_default" => 1,
             "is_default_for_business" => (in_array($role ,["business_admin",
             "business_admin",
             "business_staff",
             "business_teacher"

             ])?1:0)


            ]);
            }

        }

        // setup roles and permissions
        $role_permissions = config("setup-config.roles_permission");
        foreach ($role_permissions as $role_permission) {
            $role = Role::where(["name" => $role_permission["role"]])->first();
            error_log($role_permission["role"]);
            $permissions = $role_permission["permissions"];
            $role->syncPermissions($permissions);
            // foreach ($permissions as $permission) {
            //     if(!$role->hasPermissionTo($permission)){
            //         $role->givePermissionTo($permission);
            //     }


            // }
        }
$user->assignRole('business_admin');


        } catch (Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }

        Log::info("Database for business ID $businessId has been created successfully.");
        $this->info("Database for business ID $businessId has been created.");

        return 0;
    }









    // public function handle()
    // {
    //     Log::info("Starting database creation process...");

    //     // Retrieve the business_id from the command input
    //     $businessId = $this->argument('business_id');
    //     $databaseName = 'quickreview_business_' . $businessId;

    //     Log::info("Business ID: $businessId");
    //     Log::info("Database name: $databaseName");

    //     // Check if the database exists
    //     if ($this->databaseExists($databaseName)) {
    //         Log::info("Database for business ID $businessId already exists.");
    //         $this->info("Database for business ID $businessId already exists.");
    //         return 0;
    //     }

    //     try {
    //         // cPanel API credentials
    //         $cpanelUsername = env('CPANEL_USERNAME');
    //         $cpanelPassword = env('CPANEL_PASSWORD');
    //         $cpanelDomain = env('CPANEL_DOMAIN');

    //         // API URL for creating the database
    //         $cpanelUrl = "https://{$cpanelDomain}:2083/execute/Mysql/create_database?name={$cpanelUsername}_{$databaseName}";

    //         // cURL request to cPanel API for creating the database
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $cpanelUrl);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_USERPWD, "$cpanelUsername:$cpanelPassword");
    //         curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //         $response = curl_exec($ch);

    //         if (curl_errno($ch)) {
    //             Log::error("cURL Error: " . curl_error($ch));
    //             $this->error("cURL Error: " . curl_error($ch));
    //             return 1;
    //         }

    //         $result = json_decode($response, true);
    //         if (isset($result['status']) && $result['status'] == 1) {
    //             Log::info("Database created successfully: {$databaseName}");

    //             // Fetch MySQL credentials from .env
    //             $adminUser = env('DB_USERNAME', 'root'); // Admin user with privileges
    //             $adminPassword = env('DB_PASSWORD', '');

    //             // Connect to MySQL with an admin user and grant privileges
    //             DB::statement("CREATE USER IF NOT EXISTS '{$adminUser}'@'localhost' IDENTIFIED BY '{$adminPassword}'");
    //             DB::statement("GRANT ALL PRIVILEGES ON {$databaseName}.* TO '{$adminUser}'@'localhost'");
    //             DB::statement("FLUSH PRIVILEGES");

    //             // Configure new database connection
    //             config([
    //                 'database.connections.business' => [
    //                     'driver' => 'mysql',
    //                     'host' => config('database.connections.mysql.host'),
    //                     'port' => config('database.connections.mysql.port'),
    //                     'database' => $databaseName,
    //                     'username' => $adminUser,
    //                     'password' => $adminPassword,
    //                     'unix_socket' => config('database.connections.mysql.unix_socket'),
    //                     'charset' => 'utf8mb4',
    //                     'collation' => 'utf8mb4_unicode_ci',
    //                     'prefix' => '',
    //                     'strict' => true,
    //                     'engine' => null,
    //                 ],
    //             ]);

    //             // Run migrations on the new database
    //             Log::info("Running migrations on new database '{$databaseName}'");
    //             Artisan::call('migrate', [
    //                 '--database' => 'business',
    //                 '--path' => 'database/migrations',
    //             ]);

    //         } else {
    //             Log::error("Failed to create database: " . ($result['errors'][0] ?? 'Unknown error'));
    //             $this->error("Failed to create database: " . ($result['errors'][0] ?? 'Unknown error'));
    //             return 1;
    //         }

    //         curl_close($ch);

    //     } catch (Exception $e) {
    //         Log::error("An error occurred: " . $e->getMessage());
    //         $this->error("An error occurred: " . $e->getMessage());
    //         return 1;
    //     }

    //     Log::info("Database for business ID $businessId has been created successfully.");
    //     $this->info("Database for business ID $businessId has been created.");

    //     return 0;
    // }



    /**
     * Check if a database exists.
     *
     * @param string $databaseName
     * @return bool
     */
    protected function databaseExists($databaseName)
    {
        // Get the list of databases
        $databases = DB::select('SHOW DATABASES');

        foreach ($databases as $database) {
            if ($database->Database === $databaseName) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GenerateDatabase extends Command
{
    // The name and signature of the console command.
    protected $signature = 'generate:database {business_id}';

    // The console command description.
    protected $description = 'Generate a new database for a given business ID';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
{
    Log::info("Starting database creation process...");

    // Retrieve the business_id from the command input
    $businessId = $this->argument('business_id');
    $databaseName = 'quickreview_business_' . $businessId;

    Log::info("Business ID: $businessId");
    Log::info("Database name: $databaseName");

    // Check if the database exists
    if ($this->databaseExists($databaseName)) {
        Log::info("Database for business ID $businessId already exists.");
        $this->info("Database for business ID $businessId already exists.");
        return 0;
    }

    try {
        // cPanel API credentials
        $cpanelUsername = 'quickreview'; // Replace with your cPanel username
        $cpanelPassword = 'Quick@01_Review'; // Replace with your cPanel password
        $cpanelDomain = 'https://quickreview.app'; // Replace with your cPanel domain

        // API URL for creating the database
        $cpanelUrl = "https://$cpanelDomain:2083/execute/Mysql/create_database?name={$cpanelUsername}_{$databaseName}";

        // cURL request to cPanel API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cpanelUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$cpanelUsername:$cpanelPassword");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error("cURL Error: " . curl_error($ch));
            $this->error("cURL Error: " . curl_error($ch));
            return 1;
        }

        // Parse and handle the API response
        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] == 1) {
            Log::info("Database created successfully: {$databaseName}");

            // Configure new database connection
            config([
                'database.connections.business' => [
                    'driver' => 'mysql',
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'database' => $databaseName,
                    'username' => $cpanelUsername,
                    'password' => $cpanelPassword,
                    'unix_socket' => config('database.connections.mysql.unix_socket'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ],
            ]);

            // Run migrations on the new database
            Log::info("Running migrations on new database '{$databaseName}'");
            Artisan::call('migrate', [
                '--database' => 'business',
                '--path' => 'database/migrations',
            ]);

        } else {
            Log::error("Failed to create database: " . $result['errors'][0] ?? 'Unknown error');
            $this->error("Failed to create database: " . $result['errors'][0] ?? 'Unknown error');
            return 1;
        }

        curl_close($ch);

    } catch (Exception $e) {
        Log::error("An error occurred: " . $e->getMessage());
        $this->error("An error occurred: " . $e->getMessage());
        return 1;
    }

    // Output a success message
    Log::info("Database for business ID $businessId has been created successfully.");
    $this->info("Database for business ID $businessId has been created.");

    return 0;
}




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

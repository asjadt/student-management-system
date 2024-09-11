<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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
        // Retrieve the business_id from the command input
        $businessId = $this->argument('business_id');
        $databaseName = 'business_' . $businessId;

      // Check if the database exists
if ($this->databaseExists($databaseName)) {
    $this->info("Database for business ID $businessId already exists.");
    return 0;
}

try {
    // Create the new database
    DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}`");

    // Fetch MySQL credentials from config
    $username = config('database.connections.mysql.username');
    $password = config('database.connections.mysql.password');

    // Grant all privileges on the new database to the existing user
    DB::statement("GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$username}'@'localhost' IDENTIFIED BY '{$password}'");

    // Flush privileges to apply changes
    DB::statement("FLUSH PRIVILEGES");

    // Dynamically configure the new database connection
    config([
        'database.connections.business' => [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => $databaseName,
            'username' => $username,
            'password' => $password,
            'unix_socket' => config('database.connections.mysql.unix_socket'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ]);

    // Optionally, run migrations on the new database
    Artisan::call('migrate', [
        '--database' => 'business',
        '--path' => 'database/migrations',
    ]);

} catch (Exception $e) {
    $this->error("An error occurred: " . $e->getMessage());
    return 1;
}


        // Output a success message
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

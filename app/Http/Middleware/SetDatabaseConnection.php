<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class SetDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env("SELF_DB") == true) {
            $businessId = request()->input("business_id");

            if (!empty($businessId)) {
                // Query from CENTRAL database with caching (30 min TTL)
                $business = Cache::remember("business_connection_{$businessId}", 1800, function () use ($businessId) {
                    return DB::connection('mysql') // Central/default database
                        ->table('businesses')
                        ->where('id', $businessId)
                        ->first(['id', 'name']); // Only select needed fields
                });

                if ($business) {
                    $databaseName = 'svs_business_' . $businessId;
                    $adminUser = env('DB_USERNAME', 'root');
                    $adminPassword = env('DB_PASSWORD', '');

                    // Configure tenant database connection
                    Config::set('database.connections.tenant', [
                        'driver' => 'mysql',
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'port' => env('DB_PORT', '3306'),
                        'database' => $databaseName,
                        'username' => $adminUser,
                        'password' => $adminPassword,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => true,
                        'engine' => null,
                    ]);

                    // Set tenant as default connection for this request
                    Config::set('database.default', 'tenant');

                    // Purge and reconnect
                    DB::purge('tenant');
                    DB::reconnect('tenant');

                } else {
                    throw ValidationException::withMessages([
                        'business_id' => ['The selected business ID is invalid.']
                    ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'business_id' => ['The business ID field is required.']
                ]);
            }
        }

        return $next($request);
    }
}

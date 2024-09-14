<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        if(env("SELF_DB") == true) {
            if (Auth::check()) {
                $user = Auth::user();
                $businessId = $user->business_id; // Adjust this according to how you retrieve the business ID

                if (!empty($businessId)) {
                    $databaseName = 'business_' . $businessId;

                    // Dynamically set the default database connection configuration
                    Config::set('database.connections.mysql.database', $databaseName);

                    // Reconnect to the database using the updated configuration
                    DB::purge('mysql');
                    DB::reconnect('mysql');
                } else {
                    // Optionally handle the case where there is no business_id
                    // For example, set to a default database or keep the default connection
                    Config::set('database.connections.mysql.database', null);
                }
            }
            }


        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {

     
        if (env("SELF_DB") == true) {

                $businessId = request()->input("business_id"); // Adjust this according to how you retrieve the business ID
                if (!empty($businessId)) {
                    // Fetch the database credentials (for example from a Business model or configuration)
                    $business = DB::table('businesses')->where('id', $businessId)->first();

                    if ($business) {
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

                    } else {
                        return response()->json([
                            "message" => "invalid business id"
                           ],409);
                    }
                } else {
                   return response()->json([
                    "message" => "invalid business id"
                   ],409);
                }


        }

       // Retrieve the user from the token
       $user = Auth::guard('api')->user();

       if (!$user) {
           // If the user is not authenticated, return an unauthorized response
           return response()->json(['error' => 'Unauthorized'], 401);
       }

       // Log in the user to make auth()->user() accessible
       Auth::login($user);


        // Continue to the next middleware/request handler
        return $next($request);
    }
}

<?php

namespace App\Http\Utils;

use App\Models\ErrorLog;
use Exception;
use Illuminate\Http\Request;

trait ErrorUtil
{
    private function getHttpStatusCode($code) {
        if (is_numeric($code) && $code >= 400 && $code < 600) {
            // If it's a valid HTTP status code, return it
            return (int) $code;
        } else {
            // Otherwise, default to 500 Internal Server Error
            return 500;
        }
    }
    // this function do all the task and returns transaction id or -1
    public function sendError(Exception $e, $statusCode, Request $request)
    {
        $errorData = [
            "message" => $e->getMessage(),
            "line" => $e->getLine(),
            "file" => $e->getFile(),
        ];

        return response()->json($errorData, $this->getHttpStatusCode($e->getCode()));




        $user = auth()->user();
        $authorizationHeader = request()->header('Authorization');
        $token = str_replace('Bearer ', '', $authorizationHeader);

        $errorLog = [
            "api_url" => $request->fullUrl(),
            "fields" => json_encode(request()->all()),
            "token" => $token,

            "user" => !empty($user) ? (json_encode($user)) : "",
            "user_id" => !empty($user) ? $user->id : "",
            "message" => $e->getMessage(),
            "status_code" => $e->getCode(),
            "line" => $e->getLine(),
            "file" => $e->getFile(),
            "ip_address" =>  $request->header('X-Forwarded-For'),
            "request_method" => $request->method()
        ];
        ErrorLog::create($errorLog);


        if ($e->getCode() == 422) {
            $statusCode = 422;
            return response()->json(json_decode($e->getMessage()), 422);
        }

    }
    public function storeError($e, $statusCode,$line,$file)
    {
        $user = auth()->user();
        $authorizationHeader = request()->header('Authorization');

        // Now you can parse or use the $authorizationHeader as needed
        // For example, to extract the token from a Bearer token:
        $token = str_replace('Bearer ', '', $authorizationHeader);

        $errorLog = [
            "api_url" => request()->fullUrl(),
            "fields" => json_encode(request()->all()),
            "token" => $token,
            "user" => !empty($user) ? (json_encode($user)) : "",
            "user_id" => !empty($user) ? $user->id : "",
            "message" => json_encode($e),
            "status_code" => $statusCode,
            "line" => $line,
            "file" => $file,
            "ip_address" =>  request()->header('X-Forwarded-For'),
            "request_method" => request()->method()
        ];
        ErrorLog::create($errorLog);
    }
}

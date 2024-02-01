<?php

namespace App\Http\Middleware;


use Closure;


class FormatDatesInResponse
{
 public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->headers->get('content-type') === 'application/json') {
            $content = $response->getContent();
            $convertedContent = $this->convertDatesInJson($content);
            $response->setContent($convertedContent);
        }

        return $response;
    }

    private function convertDatesInJson($json)
    {
        $data = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            array_walk_recursive($data, function (&$value, $key) {
                if (is_string($value) && $this->isDateFormat($value)) {
                    $value = date('d-m-Y', strtotime($value));
                }
            });

            return json_encode($data);
        }

        return $json;
    }

    private function isDateFormat($value)
    {
        $date = date_create_from_format('Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }





}

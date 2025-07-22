<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/iclock/{action}', function (Request $request, $action) {
    Log::info("initial TEST");
    if ($action === 'getrequest') {
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    if ($action === 'cdata') {
        $raw = $request->getContent();
        Log::info("ZKTeco log received:", ['raw' => $raw]);

        $lines = explode("\n", trim($raw));
        foreach ($lines as $line) {
            if (str_starts_with($line, 'ATTLOG')) {
                preg_match('/PIN=(\d+)\s+DateTime=([0-9\-:\s]+)\s+Status=(\d+)/', $line, $matches);
                if ($matches) {
                    $pin = $matches[1];
                    $timestamp = $matches[2];
                    $status = $matches[3];

                    Log::info("Parsed log", compact('pin', 'timestamp', 'status'));
                }
            }
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    return response('Invalid Request', 404);
});

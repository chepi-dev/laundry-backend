<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/clear-cache-hosting/{token}', function ($token) {
    if ($token !== 'rahasia123') {
        abort(403, 'Unauthorized');
    }

    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');

    return response()->json([
        'status' => true,
        'message' => 'Berhasil clear cache hosting.'
    ]);
});
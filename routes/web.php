<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function ($path) {
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
})->where('path', '.*');

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

    Artisan::call('storage:link');

    return Response::json([
        'status' => true,
        'message' => 'Berhasil clear cache hosting.'
    ]);
});

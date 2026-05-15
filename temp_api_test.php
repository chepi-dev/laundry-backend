<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

$email = 'temp'.time().'@example.com';
$request = Request::create('/api/register', 'POST', [
    'name' => 'Temp User',
    'email' => $email,
    'password' => 'secret123',
    'password_confirmation' => 'secret123',
    'no_hp' => '081234567890',
    'alamat' => 'Jl Test Address',
]);

$response = $app->handle($request);
echo $response->getStatusCode() . "\n";
echo $response->getContent();

<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\Api\UniversalAuthController();

$tests = [
    ['admin', 'admin123', 'admin'],
    ['spv1', 'spv123', 'supervisor'],
    ['teller1', 'teller123', 'teller'],
    ['Handa', 'password123', 'nasabah'],
    ['17763', 'password123', 'nasabah (via NIS)'],
    ['wronguser', 'wrongpass', 'invalid'],
    ['admin', 'wrongpass', 'invalid pw'],
];

foreach ($tests as $t) {
    $req = \Illuminate\Http\Request::create('/api/auth/login', 'POST', [
        'username' => $t[0],
        'password' => $t[1],
    ]);
    $res = $controller->login($req);
    $data = json_decode($res->getContent(), true);
    $status = $res->getStatusCode();
    echo "Testing {$t[0]} / {$t[1]} (Expect {$t[2]}): HTTP $status - " . ($data['role'] ?? $data['message']) . "\n";
}


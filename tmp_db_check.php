<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ConsignmentItem;
use App\Models\Consignor;

$consignor = Consignor::where('name', 'like', '%Toni%')->first();
if (!$consignor) {
    echo "Consignor not found\n";
    exit;
}

echo "Consignor: " . $consignor->name . " (ID: " . $consignor->id . ")\n";
echo "Active Items Label Count: " . $consignor->consignmentItems()->where('status', 'active')->count() . "\n";

$items = $consignor->consignmentItems()->with('product')->get();
foreach ($items as $i) {
    echo "--- Item ID: {$i->id} ---\n";
    echo "Product: " . ($i->product->name ?? 'N/A') . "\n";
    echo "Received: {$i->quantity_received}\n";
    echo "Sold: {$i->quantity_sold}\n";
    echo "Rem: {$i->quantity_remaining}\n";
    echo "Status: {$i->status}\n";
}

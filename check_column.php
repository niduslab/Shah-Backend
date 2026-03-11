<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasColumn('products', 'kinomap')) {
    echo "Column kinomap exists.\n";
} else {
    echo "Column kinomap DOES NOT exist.\n";
}

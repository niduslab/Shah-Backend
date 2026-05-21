<?php
/**
 * Verification Script for Bulk Import Critical Fixes
 * 
 * Run this script to verify all fixes are properly applied.
 * Usage: php verify_fixes.php
 */

echo "🔍 Verifying Bulk Import Critical Fixes...\n\n";

$allPassed = true;

// Fix #1: Check league/csv in composer.json
echo "1. Checking league/csv in composer.json... ";
$composerJson = json_decode(file_get_contents('composer.json'), true);
if (isset($composerJson['require']['league/csv'])) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - league/csv not found in composer.json\n";
    $allPassed = false;
}

// Fix #2: Check cancellation logic in ProcessProductImport.php
echo "2. Checking cancellation logic in job... ";
$jobContent = file_get_contents('app/Jobs/ProcessProductImport.php');
if (strpos($jobContent, "if (\$this->import->status === 'cancelled')") !== false) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - Cancellation check not found\n";
    $allPassed = false;
}

// Fix #3: Check transaction fix (incrementProcessed before commit)
echo "3. Checking transaction isolation fix... ";
if (preg_match('/incrementProcessed\(true\);.*?DB::commit\(\);/s', $jobContent)) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - incrementProcessed not before commit\n";
    $allPassed = false;
}

// Fix #4: Check error cap in ProductImport.php
echo "4. Checking error cap (1000 limit)... ";
$modelContent = file_get_contents('app/Models/ProductImport.php');
if (strpos($modelContent, 'count($currentErrors) >= 1000') !== false) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - Error cap not found\n";
    $allPassed = false;
}

// Fix #5: Check .env for queue connection (warning only)
echo "5. Checking .env queue configuration... ";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'QUEUE_CONNECTION=') !== false) {
        $queueConnection = '';
        if (preg_match('/QUEUE_CONNECTION=(\w+)/', $envContent, $matches)) {
            $queueConnection = $matches[1];
        }
        if ($queueConnection === 'sync') {
            echo "⚠️  WARNING - Using sync queue (will timeout on large imports)\n";
            echo "   Recommendation: Set QUEUE_CONNECTION=database or redis\n";
        } else {
            echo "✅ PASS (using $queueConnection)\n";
        }
    } else {
        echo "⚠️  WARNING - QUEUE_CONNECTION not set in .env\n";
    }
} else {
    echo "⚠️  WARNING - .env file not found\n";
}

// Fix #6: Check duplicate SKU validation
echo "6. Checking duplicate SKU pre-validation... ";
$serviceContent = file_get_contents('app/Services/ProductImportService.php');
if (strpos($serviceContent, 'SKU already exists in database') !== false) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - SKU duplicate check not found\n";
    $allPassed = false;
}

// Fix #7: Check CSV escaping with fputcsv
echo "7. Checking CSV error export escaping... ";
$controllerContent = file_get_contents('app/Http/Controllers/Api/Admin/ProductImportController.php');
if (strpos($controllerContent, 'fputcsv($output') !== false) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - fputcsv not used for error export\n";
    $allPassed = false;
}

// Fix #8: Check variation parsing with delimiter detection
echo "8. Checking variation parsing (pipe handling)... ";
if (strpos($serviceContent, '$delimiter = \'|\'') !== false && 
    strpos($serviceContent, 'if (strpos($row[$attrKey], \'~~\')') !== false) {
    echo "✅ PASS\n";
} else {
    echo "❌ FAIL - Delimiter detection not found\n";
    $allPassed = false;
}

echo "\n" . str_repeat("=", 60) . "\n";

if ($allPassed) {
    echo "✅ ALL CRITICAL FIXES VERIFIED!\n\n";
    echo "Next steps:\n";
    echo "1. Run: composer update\n";
    echo "2. Update .env: QUEUE_CONNECTION=database (or redis)\n";
    echo "3. Run: php artisan queue:table && php artisan migrate\n";
    echo "4. Start queue: php artisan queue:work\n";
    echo "5. Test with small CSV import\n";
} else {
    echo "❌ SOME FIXES ARE MISSING!\n\n";
    echo "Please review the failed checks above and apply the fixes.\n";
    echo "See CRITICAL_FIXES_APPLIED.md for details.\n";
}

echo "\n";

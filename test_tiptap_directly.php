<?php

require 'vendor/autoload.php';

// Test what TipTap accepts
$editor = new \Ueberdosis\Tiptap\Editor();

echo "Testing TipTap with different inputs:\n\n";

// Test 1: NULL
echo "Test 1 - NULL: ";
try {
    $editor->setContent(null);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

// Test 2: Empty string
echo "Test 2 - Empty string '': ";
try {
    $editor->setContent('');
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

// Test 3: <p></p>
echo "Test 3 - <p></p>: ";
try {
    $editor->setContent('<p></p>');
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

// Test 4: Boolean false
echo "Test 4 - false: ";
try {
    $editor->setContent(false);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

// Test 5: Number 0
echo "Test 5 - 0: ";
try {
    $editor->setContent(0);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

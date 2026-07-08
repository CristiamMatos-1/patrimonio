<?php
// Test script to verify the Router dynamic scan can find a controller file on disk
$basePath = __DIR__ . '/..';
$basePath = realpath($basePath);
$controllerName = 'Tenant\\InventoryController';
$relativeClassPath = 'Controllers/' . str_replace('\\', '/', $controllerName) . '.php';

$parts = explode('/', ltrim($relativeClassPath, '/'));
$currentPath = rtrim($basePath, '/') . '/';

// Find the 'app' directory (case-insensitive)
$appFound = false;
foreach (scandir($currentPath) as $dir) {
    if ($dir === '.' || $dir === '..') continue;
    if (strtolower($dir) === 'app') {
        $currentPath .= $dir . '/';
        $appFound = true;
        break;
    }
}

if (!$appFound) {
    echo "app directory not found under: $currentPath\n";
    exit(2);
}

$fileLoaded = false;
$debug = [];
$debug[] = "Scanning for controller using relative path: $relativeClassPath";

foreach ($parts as $index => $part) {
    $isLast = ($index === count($parts) - 1);
    $foundMatch = false;

    if (is_dir($currentPath)) {
        $items = scandir($currentPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (strtolower($item) === strtolower($part)) {
                $currentPath .= $item;
                if (!$isLast) $currentPath .= '/';
                $foundMatch = true;
                $debug[] = "Matched part '$part' -> actual item '$item' (currentPath now: $currentPath)";
                break;
            }
        }
    }

    if (!$foundMatch) {
        $debug[] = "Could not find part '$part' in directory. Current path: $currentPath";
        break;
    }

    if ($isLast && $foundMatch) {
        if (file_exists($currentPath)) {
            $fileLoaded = true;
            $debug[] = "File found at: $currentPath";
        } else {
            $debug[] = "Reached final path but file does not exist: $currentPath";
        }
    }
}

echo implode("\n", $debug) . "\n";
if ($fileLoaded) {
    echo "SUCCESS: Controller file located.\n";
    exit(0);
}

echo "FAIL: Controller file not located.\n";
exit(1);

<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';

// Fallback for environments where Composer autoload does not locate the class (e.g., optimized autoload missing).
if (!class_exists('Greenn\\Libs\\BubblewrapSandbox')) {
    require_once __DIR__ . '/../src/BubblewrapSandbox.php';
}
if (!class_exists('Greenn\\Libs\\Exceptions\\BubblewrapUnavailableException')) {
    require_once __DIR__ . '/../src/Exceptions/BubblewrapUnavailableException.php';
}

<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';

// Fallback para ambientes onde o autoload do Composer não localiza a classe (ex.: autoload otimizado ausente).
if (!class_exists('Greenn\\Sandbox\\BubblewrapSandbox')) {
    require_once __DIR__ . '/../src/BubblewrapSandbox.php';
}
if (!class_exists('Greenn\\Sandbox\\Exceptions\\BubblewrapUnavailableException')) {
    require_once __DIR__ . '/../src/Exceptions/BubblewrapUnavailableException.php';
}

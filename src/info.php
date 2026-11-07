<?php

declare(strict_types=1);

echo 'PHP ' . PHP_VERSION . ' en ' . PHP_OS_FAMILY . PHP_EOL;

$extensions = ['mbstring', 'intl', 'pdo_mysql', 'zip'];

foreach ($extensions as $extension) {
    $status = extension_loaded($extension) ? '✔' : '✘';
    echo str_pad($extension, 15) . $status . PHP_EOL;
}

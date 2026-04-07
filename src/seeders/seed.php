<?php

$pdo = new PDO("mysql:host=localhost;dbname=resume_builder", "phpuser", "Php##0923");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$files = glob(__DIR__ . './*.php');

foreach ($files as $file) {
    echo "Seeding: " . basename($file) . "\n";

    $seeder = require $file;
    $seeder($pdo);
}

echo "Seeding Done.\n";
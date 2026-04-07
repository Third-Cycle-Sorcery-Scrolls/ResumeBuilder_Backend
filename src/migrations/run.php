<?php
require "migrate.php";
$conn = new PDO("mysql:host=localhost;port=3306;dbname=resume_builder", "phpuser", "Php##0923");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

up($conn);

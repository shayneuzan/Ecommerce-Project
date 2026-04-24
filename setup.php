<?php

//This script creates all the database tables for Traventa
//Run it once by visiting http://localhost/traventa/setup.php
//After running it tables should now be successfully created 
//and can check in phpMyAdmin for proof 

require __DIR__ . '/vendor/autoload.php';

//Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    //Connect to the database using credentials from .env
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );

    //Make PDO throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Read the SQL schema file from the migrations folder
    $sql = file_get_contents(__DIR__ . '/migrations/schema.sql');

    //Execute all the CREATE TABLE statements
    $pdo->exec($sql);

    echo 'Database tables created successfully! Visit http://localhost/traventa to seed the data.';

} catch (PDOException $e) {
    //Show error if something went wrong
    echo 'Setup failed: ' . $e->getMessage();
}
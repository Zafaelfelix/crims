<?php
/**
 * Konfigurasi koneksi database CRIMS
 */

$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'crims_db',
    'port' => 3306,
];

$mysqli = new mysqli(
    $dbConfig['host'],
    $dbConfig['user'],
    $dbConfig['pass'],
    $dbConfig['name'],
    $dbConfig['port']
);

if ($mysqli->connect_errno) {
    die('Gagal terkoneksi ke database: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');


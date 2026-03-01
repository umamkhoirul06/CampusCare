<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "campus_care2";
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(500);
    exit;
}

<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Debug
if ($data === null) {
    echo json_encode(["status" => "error", "message" => "JSON body not received"]);
    exit;
}

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Email and Password are required"]);
    exit;
}

$email = trim($data['email']);
$password = trim($data['password']);

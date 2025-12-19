<?php
header("Content-Type: application/json");
include "db_connect.php"; // DB connection file

$input = json_decode(file_get_contents("php://input"), true);

if (
    empty($input['team_id']) ||
    empty($input['name']) ||
    empty($input['email'])
) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

$team_id = $input['team_id'];
$name = $input['name'];
$email = $input['email'];
$role = isset($input['role']) ? $input['role'] : "MEMBER";

$sql = "INSERT INTO team_members (team_id, name, email, role)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $team_id, $name, $email, $role);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Team member added successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to add team member"
    ]);
}
?>

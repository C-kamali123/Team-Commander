<?php
/**
 * Delete/Remove Member Endpoint
 * POST: Delete a team member
 * Fields: member_id
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "db.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validate JSON body
if ($data === null) {
    echo json_encode(["status" => "error", "message" => "JSON body not received"]);
    exit;
}

// Validate member_id
if (!isset($data['member_id']) || !is_numeric($data['member_id'])) {
    echo json_encode(["status" => "error", "message" => "Member ID is required"]);
    exit;
}

$member_id = intval($data['member_id']);

// Check if member exists
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Member not found"]);
    $stmt->close();
    $conn->close();
    exit;
}

$member = $result->fetch_assoc();
$stmt->close();

// Delete the member
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $member_id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Member '" . $member['full_name'] . "' deleted successfully"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete member: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

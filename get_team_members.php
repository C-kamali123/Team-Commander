<?php
/**
 * Get Team Members Endpoint
 * GET: Retrieve all team members
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "db.php";

try {
    // Query team members (users with role = 'team_member')
    $stmt = $conn->prepare("SELECT id, full_name, email, role, created_at FROM users WHERE role = 'team_member' ORDER BY full_name ASC");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            "id" => (int)$row['id'],
            "full_name" => $row['full_name'],
            "email" => $row['email'],
            "role" => $row['role'],
            "created_at" => $row['created_at']
        ];
    }

    echo json_encode([
        "status" => "success",
        "count" => count($members),
        "members" => $members
    ]);

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>

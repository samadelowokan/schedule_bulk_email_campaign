<?php
/** SAMUEL ADELOWOKAN
 * Delete a Campaign
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$id = intval($_POST['id']);

try {
    $db = new PDO('sqlite:../db/email_scheduler.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete campaign logs first (foreign key integrity)
    $stmt = $db->prepare("DELETE FROM campaign_logs WHERE campaign_id = :id");
    $stmt->execute([':id' => $id]);

    // Delete campaign
    $stmt = $db->prepare("DELETE FROM campaigns WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['message' => 'Campaign deleted successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

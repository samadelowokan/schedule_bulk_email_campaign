<?php

/**
* SAMUEL ADELOWOKAN
* */

// Set headers to allow POST from forms or JS
header('Content-Type: application/json');

// Check if data was posted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Connect to SQLite database
try {
    $db = new PDO('sqlite:../db/email_scheduler.sqlite'); // adjust path if needed
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get posted data
$account = $_POST['account'] ?? '';
$days = $_POST['days'] ?? '';
$time = $_POST['time'] ?? '';
$subject = $_POST['subject'] ?? '';
$template = $_POST['template'] ?? '';
$csv = $_POST['csv'] ?? null;
$now = date('Y-m-d H:i:s');
$id = $_POST['id'];

// echo json_encode(['success' => false, 'message' => $account.','.$days.','.$time.','.$subject.','.$template.','.$csv]);
// exit;

// Validate required fields
if (empty($account) || empty($days) || empty($time) || empty($subject) || empty($csv) || empty($template)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $db->prepare("UPDATE campaigns 
    SET account = :account,
        days = :days,
        time = :time,
        subject = :subject,
        csv = :csv,
        template = :template,
        updated_at = :updated_at
    WHERE id = :id");

    // Bind values
    $stmt->bindParam(':account', $account);
    $stmt->bindParam(':days', $days);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':csv', $csv);
    $stmt->bindParam(':template', $template);
    $stmt->bindParam(':updated_at', $now);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT); 

    // Execute insert
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Campaign saved successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
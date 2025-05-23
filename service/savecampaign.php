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

// echo json_encode(['success' => false, 'message' => $account.','.$days.','.$time.','.$subject.','.$template.','.$csv]);
// exit;

// Validate required fields
if (empty($account) || empty($days) || empty($time) || empty($subject) || empty($csv) || empty($template)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $db->prepare("INSERT INTO campaigns (account, days, time, subject, csv, template, last_sent_date, created_at, updated_at) 
                          VALUES (:account, :days, :time, :subject, :csv, :template, NULL, :created_at, :updated_at)");

    // Bind values
    $stmt->bindParam(':account', $account);
    $stmt->bindParam(':days', $days); // e.g. "Monday,Wednesday"
    $stmt->bindParam(':time', $time); // e.g. "10:00"
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':csv', $csv);
    $stmt->bindParam(':template', $template);
    $stmt->bindParam(':created_at', $now);
    $stmt->bindParam(':updated_at', $now);

    // Execute insert
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Campaign saved successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
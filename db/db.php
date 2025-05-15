<?php
// Connect to SQLite DB
try {
    $db = new PDO('sqlite:db/email_scheduler.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all campaigns
    $stmt = $db->query("SELECT * FROM campaigns ORDER BY id DESC");
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
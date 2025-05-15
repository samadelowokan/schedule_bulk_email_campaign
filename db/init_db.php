<?php
/**
 * SAMUEL ADELOWOKAN
 * Run this file on a browser or CLI once to create the Database
 */
$db = new PDO('sqlite:email_scheduler.sqlite');

// Create campaigns table
$db->exec("CREATE TABLE IF NOT EXISTS campaigns (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    account TEXT NOT NULL,
    days TEXT NOT NULL,
    time TEXT NOT NULL,
    subject TEXT NOT NULL,
    mobile TEXT NULL,
    msg TEXT NULL,
    last_sent_date TEXT NULL,
    created_at TEXT,
    updated_at TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS campaign_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    campaign_id INTEGER NOT NULL,
    email TEXT NOT NULL,
    sent_at TEXT NOT NULL
)");


echo "Database initialized.";

<?php
/**
 * config.php
 *
 * Shared database connection for the AI Donation Tracker.
 *
 * Uses SQLite instead of MySQL on purpose: SQLite ships as part of PHP
 * (pdo_sqlite) and needs no separate database server, no host/user/password,
 * and no manual "create database" step. The whole database is just a file
 * (data/donations.db) that gets created automatically the first time the
 * app runs. This is what makes the project "clone and run" on any machine.
 */

header('Access-Control-Allow-Origin: *');

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

$dbPath = $dataDir . '/donations.db';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the table automatically if it doesn't exist yet.
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS donations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            donor_name TEXT NOT NULL,
            amount REAL NOT NULL,
            purpose TEXT NOT NULL,
            cardano_tx_id TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )"
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

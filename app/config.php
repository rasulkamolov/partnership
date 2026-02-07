<?php
// app/config.php

// Database Connection
$db_dir = __DIR__ . '/../data';
if (!file_exists($db_dir)) mkdir($db_dir, 0777, true);
$db = new PDO('sqlite:' . $db_dir . '/oxford_infinity.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Database Initialization (Schema)
$db->exec("CREATE TABLE IF NOT EXISTS schools (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, username TEXT UNIQUE, password TEXT, contact TEXT, joined_date DATE DEFAULT CURRENT_DATE)");
$db->exec("CREATE TABLE IF NOT EXISTS students (id INTEGER PRIMARY KEY AUTOINCREMENT, school_id INTEGER, name TEXT, group_name TEXT, status TEXT DEFAULT 'Active', monthly_fee REAL DEFAULT 0, schedule_type TEXT DEFAULT 'odd', FOREIGN KEY(school_id) REFERENCES schools(id))");
$db->exec("CREATE TABLE IF NOT EXISTS attendance (id INTEGER PRIMARY KEY AUTOINCREMENT, student_id INTEGER, status TEXT, date DATE DEFAULT CURRENT_DATE, marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

// Helper: Upgrade Schema if columns missing (idempotent)
$cols = $db->query("PRAGMA table_info(students)")->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('monthly_fee', $cols)) $db->exec("ALTER TABLE students ADD COLUMN monthly_fee REAL DEFAULT 0");
if (!in_array('schedule_type', $cols)) $db->exec("ALTER TABLE students ADD COLUMN schedule_type TEXT DEFAULT 'odd'");

// Seed Master Admin
if (!$db->query("SELECT 1 FROM schools WHERE username = 'admin'")->fetch()) {
    $db->prepare("INSERT INTO schools (name, username, password) VALUES (?, ?, ?)")->execute(['Oxford LC Master', 'admin', password_hash('admin123', PASSWORD_DEFAULT)]);
}
?>

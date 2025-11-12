<?php
// test_db.php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "✅ Database Connected!<br>";
    
    // Test if tables exist
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables);
} else {
    echo "❌ Database Connection Failed!";
}
?>
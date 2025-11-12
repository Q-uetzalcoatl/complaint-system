<?php
// setup_tables.php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql1 = "CREATE TABLE IF NOT EXISTS complaints (
        id INT PRIMARY KEY AUTO_INCREMENT,
        tracking_code VARCHAR(20) UNIQUE NOT NULL,
        category ENUM('facilities','academics','student_services') NOT NULL,
        message TEXT NOT NULL,
        status ENUM('submitted','in_review','resolved') DEFAULT 'submitted',
        office_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $sql2 = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        office ENUM('facilities','academics','student_services') UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->exec($sql1);
    $db->exec($sql2);
    echo "✅ Tables created successfully!";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
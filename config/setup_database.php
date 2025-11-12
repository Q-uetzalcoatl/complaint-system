<?php
require_once 'config/database.php';

echo "<h3>🔧 Database Setup Debug</h3>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if connection is successful
    if (!$db) {
        die("❌ Database connection is NULL. Check database credentials.");
    }
    
    echo "✅ Database connection established<br>";
    
    // TABLE 1: Complaints
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
    
    echo "🔧 Creating complaints table...<br>";
    $db->exec($sql1);
    echo "✅ Complaints table created<br>";
    
    // TABLE 2: Admin Users  
    $sql2 = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        office ENUM('facilities','academics','student_services') UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    echo "🔧 Creating admin_users table...<br>";
    $db->exec($sql2);
    echo "✅ Admin users table created<br>";
    
    echo "<h3>🎉 All tables created successfully!</h3>";
    
} catch(PDOException $e) {
    echo "❌ PDO Error: " . $e->getMessage();
} catch(Exception $e) {
    echo "❌ General Error: " . $e->getMessage();
}
?>
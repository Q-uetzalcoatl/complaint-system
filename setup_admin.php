<?php
require_once 'config/database.php';

$admins = [
    'facilities' => 'facilities123',
    'academics' => 'academics123', 
    'student_services' => 'services123'
];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    foreach ($admins as $office => $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO admin_users (office, password_hash) VALUES (:office, :hash)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":office", $office);
        $stmt->bindParam(":hash", $hash);
        $stmt->execute();
    }
    echo "✅ Admin users created successfully!";
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
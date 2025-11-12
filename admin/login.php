<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $office = $_POST['office'] ?? '';
  $password = $_POST['password'] ?? '';
  
  try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM admin_users WHERE office = :office";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":office", $office);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_office'] = $office;
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid office or password';
  } catch(PDOException $exception) {
    $error = 'Database error. Please try again.';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Office Login</title>
  <style>
    body { 
        font-family: Arial, sans-serif; 
        background: #f4f6f9; 
        display:flex; 
        align-items:center; 
        justify-content:center; 
        min-height:100vh; 
        margin:0; 
        background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('../assets/campus.jpg'); 
        background-size: cover; 
        background-position: center; 
    }
    .card { 
        width: 100%; 
        max-width: 420px; 
        background:#fff; 
        padding:24px; 
        border-radius:10px; 
        box-shadow:0 4px 10px rgba(0,0,0,.1); 
        border-top:6px solid #0a8f3d; 
    }
    h2 { 
        margin:0 0 12px; 
        color:#0a8f3d; 
    }
    label { 
        display:block; 
        margin:10px 0 6px; 
        font-weight:bold; 
        color:#2f3b2f; 
    }
    select, input, button { 
        width:100%; 
        padding:10px; 
        border-radius:6px; 
        border:1px solid #cfd8d3; 
        font-size:14px; 
    }
    .btn { 
        background:#0a8f3d; 
        color:#fff; 
        border:none; 
        cursor:pointer; 
        font-weight:bold; 
        margin-top:10px; 
    }
    .btn:hover { 
        background:#087835; 
    }
    .error { 
        color:#b00020; 
        margin-top:8px; 
        padding:10px; 
        background:#ffeaea;
        border-radius:5px; 
    }
    .links { 
        margin-top:10px; 
        font-size:13px; 
    }
    a { 
        color:#0a8f3d; 
        text-decoration:none; 
    }
    a:hover { 
        text-decoration:underline; 
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Office Login</h2>
    <form method="post">
      <label for="office">Office</label>
      <select id="office" name="office" required>
        <option value="">-- Select Office --</option>
        <option value="facilities">Facilities</option>
        <option value="academics">Academics</option>
        <option value="student_services">Student Services</option>
      </select>
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
      <button class="btn" type="submit">Login</button>
      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
    </form>
    <div class="links">
      <a href="../index.php">Back to Form</a>
    </div>
  </div>
</body>
</html>
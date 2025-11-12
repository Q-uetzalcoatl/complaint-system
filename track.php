<?php
session_start();

// ADD THIS LINE:
require_once __DIR__ . '/config/database.php';

$code = isset($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';
$record = null;
$error = '';

// REPLACE THE FILE READING SECTION:
if ($code !== '') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM complaints WHERE tracking_code = :code";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Tracking code not found.';
        }
    } catch(PDOException $exception) {
        $error = 'Database connection error.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Complaint</title>
  <style>
    body { 
        font-family: Arial, sans-serif; 
        background: #f4f6f9; 
        margin: 0; 
        padding: 24px; 
        background-image: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), url('assets/campus.jpg'); 
        background-size: cover; 
        background-position: center; 
    }
    .card { 
        max-width: 640px; 
        margin: 0 auto; 
        background: #fff; 
        border-radius: 10px; 
        padding: 20px; 
        box-shadow: 0 4px 10px rgba(0,0,0,.1); 
        border-top: 6px solid #0a8f3d; 
    }
    h2 { 
        margin: 0 0 10px; 
        color: #0a8f3d; 
    }
    label { 
        display: block; 
        margin: 12px 0 6px; 
        font-weight: bold; 
        color: #2f3b2f; 
    }
    input, button { 
        width: 100%; 
        padding: 10px; 
        border-radius: 6px; 
        border: 1px solid #cfd8d3; 
        font-size: 14px; 
    }
    .btn { 
        background: #0a8f3d; 
        color: #fff; 
        border: none; 
        cursor: pointer; 
        font-weight: bold; 
        margin-top: 10px; 
    }
    .btn:hover { 
        background: #087835; 
    }
    .meta { 
        color: #5a6b5a; 
        font-size: 13px; 
        margin: 5px 0; 
    }
    .status { 
        display: inline-block; 
        padding: 4px 8px; 
        border-radius: 12px; 
        background: #e8f5ee; 
        color: #0a8f3d; 
        font-weight: bold; 
    }
    .error { 
        color: #b00020; 
        margin-top: 8px; 
        padding: 10px;
        background: #ffeaea;
        border-radius: 5px;
    }
    .back { 
        margin-top: 10px; 
        display: inline-block; 
        color: #0a8f3d;
        text-decoration: none;
    }
    .back:hover {
        text-decoration: underline;
    }
    pre { 
        white-space: pre-wrap; 
        background: #f8faf9; 
        padding: 10px; 
        border-radius: 6px; 
        border: 1px solid #e3ebe6; 
    }
    hr {
        margin: 20px 0;
        border: none;
        border-top: 1px solid #e3ebe6;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Track Your Complaint</h2>
    <form method="get">
      <label for="code">Enter Tracking Code</label>
      <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($code); ?>" placeholder="e.g., 9F2A3B4C5D6E" required>
      <button class="btn" type="submit">Check Status</button>
    </form>
    
    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($record): ?>
      <hr>
      <div class="meta"><strong>Tracking Code:</strong> <?php echo htmlspecialchars($record['tracking_code']); ?></div>
      <div class="meta"><strong>Category:</strong> <?php echo htmlspecialchars(ucfirst($record['category'])); ?></div>
      <div class="meta"><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($record['status']); ?></span></div>
      
      <?php if (!empty($record['office_notes'])): ?>
        <div class="meta" style="margin-top:8px"><strong>Office Notes:</strong></div>
        <pre><?php echo htmlspecialchars($record['office_notes']); ?></pre>
      <?php endif; ?>
      
      <div class="meta" style="margin-top:8px"><strong>Submitted:</strong> <?php echo htmlspecialchars($record['created_at']); ?></div>
      <div class="meta"><strong>Last Updated:</strong> <?php echo htmlspecialchars($record['updated_at']); ?></div>
    <?php endif; ?>
    
    <a class="back" href="index.php">Back to Form</a>
  </div>
</body>
</html>
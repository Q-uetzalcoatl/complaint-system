<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_office'])) {
  header('Location: login.php');
  exit;
}
$office = $_SESSION['admin_office'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code = $_POST['code'] ?? '';
  $status = $_POST['status'] ?? '';
  $notes = $_POST['notes'] ?? '';
  
  try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE complaints SET status = :status, office_notes = :notes, updated_at = NOW() 
              WHERE tracking_code = :code AND category = :office";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":notes", $notes);
    $stmt->bindParam(":code", $code);
    $stmt->bindParam(":office", $office);
    $stmt->execute();
    
    header('Location: dashboard.php');
    exit;
  } catch(PDOException $exception) {
    $error = "Update failed";
  }
}

// Load complaints for this office
$complaints = [];
try {
  $database = new Database();
  $db = $database->getConnection();
  
  $query = "SELECT * FROM complaints WHERE category = :office ORDER BY created_at DESC";
  $stmt = $db->prepare($query);
  $stmt->bindParam(":office", $office);
  $stmt->execute();
  $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
  $error = "Failed to load complaints.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ucfirst($office); ?> Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .topbar { background:#0a8f3d; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
    .container { padding: 16px; }
    h2 { color:#0a8f3d; margin: 10px 0; }
    .card { background:#fff; border-radius:10px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,.08); border-left:4px solid #0a8f3d; margin-bottom:12px; }
    .meta { font-size:13px; color:#5a6b5a; margin: 3px 0; }
    .status { display:inline-block; padding:2px 8px; border-radius:12px; background:#e8f5ee; color:#0a8f3d; font-weight:bold; }
    textarea, select, input { width:100%; padding:8px; border:1px solid #cfd8d3; border-radius:6px; font-size:14px; margin: 5px 0; }
    .row { display:grid; grid-template-columns: 1fr 140px; gap:10px; margin-top:8px; }
    .btn { background:#0a8f3d; color:#fff; border:none; border-radius:6px; padding:10px; cursor:pointer; font-weight:bold; }
    .btn:hover { background:#087835; }
    .empty { color:#6b7a70; padding: 20px; text-align: center; }
    a { color:#0a8f3d; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .message { background: #f8faf9; padding: 10px; border-radius: 6px; border: 1px solid #e3ebe6; margin: 8px 0; }
  </style>
</head>
<body>
  <div class="topbar">
    <div><strong><?php echo strtoupper($office); ?></strong> Office Dashboard</div>
    <div><a style="color:#fff;" href="logout.php">Logout</a></div>
  </div>
  <div class="container">
    <h2>Incoming Complaints</h2>
    <?php if (empty($complaints)): ?>
      <div class="empty">No complaints for this office yet.</div>
    <?php else: ?>
      <?php foreach ($complaints as $c): ?>
        <div class="card">
          <div class="meta"><strong>Tracking:</strong> <?php echo htmlspecialchars($c['tracking_code']); ?> | <strong>Submitted:</strong> <?php echo htmlspecialchars($c['created_at']); ?></div>
          <div class="meta"><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($c['status']); ?></span></div>
          <div class="message"><strong>Message:</strong><br><?php echo nl2br(htmlspecialchars($c['message'])); ?></div>
          <form method="post" class="row">
            <input type="hidden" name="code" value="<?php echo htmlspecialchars($c['tracking_code']); ?>">
            <div>
              <label for="status_<?php echo htmlspecialchars($c['tracking_code']); ?>">Status</label>
              <select id="status_<?php echo htmlspecialchars($c['tracking_code']); ?>" name="status">
                <?php foreach (['submitted'=>'Submitted','in_review'=>'In Review','resolved'=>'Resolved'] as $k=>$v): ?>
                  <option value="<?php echo $k; ?>" <?php echo $c['status']===$k?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
              <label for="notes_<?php echo htmlspecialchars($c['tracking_code']); ?>">Office Notes</label>
              <textarea id="notes_<?php echo htmlspecialchars($c['tracking_code']); ?>" name="notes" rows="3" placeholder="Add notes or updates..."><?php echo htmlspecialchars($c['office_notes']); ?></textarea>
            </div>
            <div style="display:flex; align-items:flex-end;">
              <button class="btn" type="submit">Update</button>
            </div>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
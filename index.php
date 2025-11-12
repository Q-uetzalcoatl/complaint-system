<?php
session_start();

// Generate simple math captcha and store answer in session
$numA = random_int(1, 9);
$numB = random_int(1, 9);
$_SESSION['captcha_answer'] = $numA + $numB;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CvSU Anonymous Complaint</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      background-image: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), url('assets/campus.jpg');
      background-size: cover;
      background-position: center;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .form-container {
      background: #fff;
      padding: 25px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 520px;
      border-top: 6px solid #0a8f3d;
    }
    .header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }
    .logo { width: 54px; height: 54px; border-radius: 6px; overflow: hidden; display:flex; align-items:center; justify-content:center; background:#e8f5ee; }
    .logo img { width: 100%; height: 100%; object-fit: cover; }
    h2 { margin: 0; color: #0a8f3d; }
    label { display: block; margin: 10px 0 6px; font-weight: bold; color: #2f3b2f; }
    input, select, textarea, button { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cfd8d3; font-size: 14px; }
    textarea { resize: vertical; min-height: 100px; }
    .hint { font-size: 12px; color: #6b7a70; margin-top: 4px; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .btn { background: #0a8f3d; color: #fff; border: none; cursor: pointer; transition: 0.2s; font-weight: bold; }
    .btn:hover { background: #087835; }
    .footer { text-align: center; font-size: 12px; color: #5a6b5a; margin-top: 12px; }
    .note { background: #f0fff5; border: 1px solid #bfe7cc; color: #1f6b3a; padding: 8px 10px; border-radius: 6px; font-size: 13px; }
  </style>
</head>
<body>
  <div class="form-container">
    <div class="header">
      <div class="logo"><img src="assets/cvsu-logo.png" alt="CvSU Logo"></div>
      <div>
        <h2>Anonymous Complaint Form</h2>
        <div class="hint">Cavite State University — Bacoor Campus</div>
      </div>
    </div>

    <div class="note">Your identity is protected. Only the complaint details will be sent and tracked anonymously.</div>

    <form action="submit.php" method="POST" novalidate>
      <label for="student_id">Student ID</label>
      <input type="text" id="student_id" name="student_id" placeholder="e.g., 202311123" required pattern="^20\d{7,9}$">
      <div class="hint">Required for gatekeeping only. Not stored. Format: starts with 20 + 7-9 digits.</div>

      <label for="category">Select Concern</label>
      <select name="category" id="category" required>
        <option value="">-- Choose Category --</option>
        <option value="facilities">Facilities</option>
        <option value="academics">Academics</option>
        <option value="student_services">Student Services</option>
      </select>

      <label for="complaint">Your Complaint</label>
      <textarea name="complaint" id="complaint" rows="5" required></textarea>

      <label for="captcha_input">Captcha: What is <?php echo $numA; ?> + <?php echo $numB; ?>?</label>
      <input type="number" id="captcha_input" name="captcha_input" required>

      <button class="btn" type="submit">Submit Complaint</button>
    </form>

    <div class="footer">
      <div>Track your complaint using the tracking code provided after submission.</div>
      <div style="margin-top:8px">
        <a href="track.php">Have a tracking code? Check status</a>
      </div>
    </div>
  </div>
</body>
</html>



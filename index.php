<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KNS VEGETABLES</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .card {
      background: #fff;
      width: 300px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      text-align: center;
      padding: 30px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .logo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
    }

    h2 {
      font-size: 22px;
      color: #333;
      margin: 0;
    }

    .footer {
      position: absolute;
      bottom: 15px;
      text-align: center;
      width: 100%;
      color: #777;
      font-size: 13px;
    }
  </style>
</head>
<body>

  <div class="card" onclick="window.location.href='dashboard.php'">
    <img src="logo.jpeg" alt="Company Logo" class="logo">
    <h2>KNS Vegetables</h2>
  </div>

  <div class="footer">
    © <?php echo date("Y"); ?> JJ Vegetables. All rights reserved.
  </div>

</body>
</html>

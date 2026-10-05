<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teal Navbar</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .navbar {
      background-color: #008080; /* Teal color */
      height: 150px;
    }

    .navbar-brand {
      font-size: 18px;
    
      color: white;
    }

    .navbar-text {
      color: white;
      font-size: 14px;
    }

    .user-info {
      color: white;
      font-size: 18px;
    
    }

    .user-role {
      color: white;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <!-- Logo and Title -->
      <a class="navbar-brand" href="#">
        <img src="https://getlogo.net/wp-content/uploads/2019/11/qatar-university-logo-vector.png" alt="Logo" height="30"> Research Tracking System
      </a>

      <!-- Login section -->
      <div class="ml-auto">
        <div class="navbar-text">
          <span class="user-info">Guest</span>
          <span class="user-role">(Guest)</span>
        </div>
        <div class="navbar-text">
          <button class="btn btn-outline-light btn-sm">Login</button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Bootstrap JS (optional, for dropdowns, etc.) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

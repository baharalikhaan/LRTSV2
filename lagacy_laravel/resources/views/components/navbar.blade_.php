<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .navbar-teal {
      background-color: teal;
      height: 200px;
      padding-left:80px;
    }

    .navbar-teal .navbar-brand {
      color: white;
      font-size: 24px;
      font-weight: bold;
    }

    .navbar-teal .navbar-text {
      color: white;
      font-size: 18px;
    }
  </style>
  <title>Teal Navbar</title>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-teal">
    <a class="navbar-brand" href="#">
      <img src="your-logo.png" alt="Logo" height="50"> Your Title
    </a>

    <div class="collapse navbar-collapse justify-content-end">
      <span class="navbar-text">
        Welcome, User Name
      </span>
    </div>
  </nav>

  <!-- Include Bootstrap JS and Popper.js -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

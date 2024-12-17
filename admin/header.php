<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" href="/assets/images/logo.png" type="image/x-icon">
  <link href="//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

  <!-- Fancybox CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
  <!-- Fancybox JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

  <link href='//unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="/admin/assets/css/style.css">
</head>

<body>
  <header>
    <div class="header bottom-border">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-4 d-flex align-items-center">
            <div class="logo">
              <img src="/assets/images/logo.png" alt="Logo" height="70">
            </div>
          </div>
          <div class="col-lg-8 d-flex align-items-center justify-content-end">
            <nav class="navbar navbar-expand-lg">
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                  <li class="nav-item">
                    <div class="notification-icon">
                      <a href="#"><i class='bx bxs-bell fs-2 text-dark mt-2'></i></a>
                      <span class="badge">3</span>
                    </div>
                  </li>
                  <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                      <img style="border-radius: 30px; height: 40px; width: 40px;" src="/admin/assets/images/Jahanzaib.png" alt="Profile" width="40">
                      <b class="ms-2">Admin</b>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                      <li><a class="dropdown-item" href="settings.php">Edit Profile</a></li>
                      <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                      <li><a class="dropdown-item" href="#">Logout</a></li>
                    </ul>
                  </li>
                </ul>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </header>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courses | Interactive E-Tutor</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <!-- Header -->
  <?php include 'includes/header.php'; ?>

  <div class="container my-5">
    <h1 class="text-center mb-5">Our Courses</h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <!-- HTML Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center mb-3">
              <img src="assets/images/htmllogo.png" alt="HTML Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>HTML</b></h5>
            <p class="card-text">Learn the fundamentals of HTML to create the structure of your web pages.</p>
            <div class="text-center">
              <a href="html_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>

      <!-- CSS Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center mb-3">
              <img src="assets/images/css-3.png" class="card-img-top" alt="CSS Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>CSS</b></h5>
            <p class="card-text">Master CSS to style your web pages with stunning layouts and designs.</p>
            <div class="text-center">
              <a href="css_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>

      <!-- JavaScript Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center">
              <img src="assets/images/java-script.png" class="card-img-top" alt="JavaScript Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>JavaScript</b></h5>
            <p class="card-text">Get hands-on experience with JavaScript to make your websites interactive.</p>
            <div class="text-center" style="margin-top: 30px;">
              <a href="js_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>

      <!-- PHP Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center">
              <img src="assets/images/phplogo.png" class="card-img-top" alt="PHP Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>PHP</b></h5>
            <p class="card-text">Dive into PHP to develop dynamic server-side web applications.</p>
            <div class="text-center">
              <a href="php_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Bootstrap Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center">
              <img src="assets/images/bootstrap.png" class="card-img-top" alt="Bootstrap Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>Bootstrap</b></h5>
            <p class="card-text">Learn Bootstrap to quickly build responsive and mobile-friendly websites.</p>
            <div class="text-center">
              <a href="bootstrap_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>

      <!-- XML Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center">
              <img src="assets/images/xml.png" class="card-img-top" alt="XML Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>XML</b></h5>
            <p class="card-text">Understand XML for data storage and web services.</p>
            <div class="text-center">
              <a href="xml_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Java Course Card -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-center">
              <img src="assets/images/javalogo.png" class="card-img-top" alt="Java Course" height="100" style="width: 100px;">
            </div>
            <h5 class="card-title text-center"><b>Java</b></h5>
            <p class="card-text">Master Java to build robust applications and software solutions.</p>
            <div class="text-center">
              <a href="java_course.php" class="btn btn-primary">Start Learning</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
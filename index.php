<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Home - E-learning</title>

  <!-- Bootstrap CSS link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- Bootstrap Icons link -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- MDBootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet" />

  <!-- MDBootstrap JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>


  <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
  <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <!-- header -->
  <?php include 'includes/header.php' ?>

  <!-- banner -->
  <div class="home-banner">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <div class="d-flex mt-5">
            <h1 class="text-dark">Studying <span class="text-white">Online is now <br> much easier</span></h1>
          </div>
          <div>
            <div class="ms-5 py-2">
              <p class="text-white">
                Interactive E-Tutor is an interesting platform that will teach <br>you in a more interactive way
              </p>
              <button class="text-dark custom-btn mt-3" type="submit">Get Started</button>
            </div>
          </div>
        </div>
        <div class="col-md-6 d-flex justify-content-center">
          <img src="assets/images/banner1" alt="banner1" height="550px">
        </div>
      </div>
    </div>
  </div>

  <!-- Why interactive E-Tutor -->
  <div class="tutor py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12 d-flex justify-content-center">
          <h1 class="mb-5"><b><span>Why</span> Interactive E-Tutor?</b></h1>
        </div>
        <div class="col-md-6">
          <h1>"Everything You Can Do in a Classroom, You Can Do with TOTC"</h1>
          <p>TOTC's school management software helps traditional and online schools manage scheduling,
            attendance, payments and virtual classrooms all in one secure cloud-based system.</p>
          <div>
            <button class="custom-btn my-2 text-white" type="submit">Get Started</button>
          </div>
        </div>
        <div class="col-md-6  position-relative">
          <div class="ms-5">
            <img src="assets/images/php" alt="why" class="ms-5 mb-4" height="400px">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Courses -->
  <div class="course py-5">
    <div class="container">
      <h1 class="text-center mb-5"><b>Courses</b></h1>
      <div class="custom-class">
        <a href="#" class="btn filter-btn active me-2" data-filter="all">All</a>
        <a href="#" class="btn filter-btn" data-filter="html">HTML</a>
        <a href="#" class="btn filter-btn" data-filter="css">CSS</a>
        <a href="#" class="btn filter-btn" data-filter="javascript">JAVASCRIPT</a>
        <a href="#" class="btn filter-btn" data-filter="bootstrap">BOOTSTRAP</a>
        <a href="#" class="btn filter-btn" data-filter="php">PHP</a>
        <a href="#" class="btn filter-btn" data-filter="java">JAVA</a>
      </div>

      <!-- Course Images -->
      <div class="row d-flex mt-5">
        <div class="col-lg-2" data-category="bootstrap">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/bootstrap.png" alt="bootstrap" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>Bootstrap</b></p>
              <a href="#" class="button border-0 px-4 py-2">20$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="javascript">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/java-script.png" alt="java-script" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>Javascript</b></p>
              <a href="#" class="button border-0 px-4 py-2">30$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="php">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/phplogo.png" alt="phplogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>PHP</b></p>
              <a href="#" class="button border-0 px-4 py-2">50$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="java">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/javalogo.png" alt="javalogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>Java</b></p>
              <a href="#" class="button border-0 px-4 py-2">40$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="html">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/htmllogo.png" alt="htmllogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>HTML</b></p>
              <a href="#" class="button border-0 px-4 py-2">35$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="css">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/css-3.png" alt="css-3" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>CSS</b></p>
              <a href="#" class="button border-0 px-4 py-2">20$</a>
            </div>
          </div>
        </div>
      </div>

      <div class="row d-flex mt-5">
        <div class="col-lg-2" data-category="java">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/javalogo.png" alt="javalogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>Java</b></p>
              <a href="#" class="button border-0 px-4 py-2">40$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="php">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/phplogo.png" alt="phplogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>PHP</b></p>
              <a href="#" class="button border-0 px-4 py-2">50$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="javascript">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/java-script.png" alt="java-script" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>Javascript</b></p>
              <a href="#" class="button border-0 px-4 py-2">30$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="css">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/css-3.png" alt="css-3" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>CSS</b></p>
              <a href="#" class="button border-0 px-4 py-2">20$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="html">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/htmllogo.png" alt="htmllogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>HTML</b></p>
              <a href="#" class="button border-0 px-4 py-2">35$</a>
            </div>
          </div>
        </div>
        <div class="col-lg-2" data-category="java">
          <div class="card border-0 shadow">
            <div class="card-body">
              <img src="assets/images/javalogo.png" alt="javalogo" class="img-fluid equal-height">
              <p class="fs-5 py-3 text-center"><b>Java</b></p>
              <a href="#" class="button border-0 px-4 py-2">40$</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Features -->
  <div class="feature py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12 py-2 d-flex justify-content-center align-items-center">
          <h1 class="me-2 text-center"><b><span>Our </span>Features</b></h1>
        </div>
        <div class="text-center">
          <p>This very extraordinary feature, can make learning activities more efficient</p>
        </div>
      </div>
      <div class="row mb-5">
        <div class="col-md-6">
          <a href="#"><img src="assets/images/video" alt="" height="300px" class="ms-5 mt-4"></a>
        </div>
        <div class="col-md-6 py-5">
          <h1> Interactive Video Streaming</h1>
          <p>Engage students with video lessons that pause for quizzes to reinforce learning. If
            they pass, the video
            continues; if not, it replays, ensuring active understanding.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="tool py-5 ">
    <div class="container mt-4">
      <div class="row">
        <div class="col-md-6 py-5">
          <h1><span>Assessment</span> & Interactive Learning Tools</h1>
          <p class="mt-4">Empower teachers to assign quizzes, homework, and host live Zoom classes for real-time
            interaction,
            ensuring students are consistently engaged, evaluated, and supported throughout their learning journey</p>
        </div>
        <div class="col-md-6 ">
          <a href="#"><img src="assets/images/aaq" class="ms-5 " alt="" height="350px"></a>
        </div>
      </div>
    </div>
  </div>

  <!-- review -->
  <div class="testimonial py-5">
    <div class="container">
      <h1 class="text-center"><b>Testimonials</b></h1>
      <!-- Carousel wrapper -->
      <div id="carouselMultiItemExample" data-mdb-carousel-init class="carousel slide carousel-dark text-center" data-mdb-ride="carousel">
        <!-- Controls -->
        <div class="d-flex justify-content-center mb-4">
          <div class="d-flex justify-content-center mb-4">
            <button class="carousel-control-prev" type="button" data-mdb-target="#carouselMultiItemExample" data-mdb-slide="prev">
              <i class="fas fa-chevron-left arrow" aria-hidden="true"></i> <!-- Font Awesome Icon -->
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-mdb-target="#carouselMultiItemExample" data-mdb-slide="next">
              <i class="fas fa-chevron-right arrow" aria-hidden="true"></i> <!-- Font Awesome Icon -->
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>
        <!-- Inner -->
        <div class="carousel-inner py-4">
          <!-- Single item -->
          <div class="carousel-item active">
            <div class="container">
              <div class="row">
                <div class="col-lg-4">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(1).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">Anna Deynah</h5>
                  <p>UX Designer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod eos id
                    officiis hic tenetur quae quaerat ad velit ab hic tenetur.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                  </ul>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(32).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">John Doe</h5>
                  <p>Web Developer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis
                    suscipit laboriosam, nisi ut aliquid commodi.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li>
                      <i class="fas fa-star-half-alt fa-sm"></i>
                    </li>
                  </ul>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(10).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">Maria Kate</h5>
                  <p>Photographer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="far fa-star fa-sm"></i></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Single item -->
          <div class="carousel-item">
            <div class="container">
              <div class="row">
                <div class="col-lg-4">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(3).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">John Doe</h5>
                  <p>UX Designer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod eos id
                    officiis hic tenetur quae quaerat ad velit ab hic tenetur.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                  </ul>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(4).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">Alex Rey</h5>
                  <p>Web Developer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis
                    suscipit laboriosam, nisi ut aliquid commodi.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li>
                      <i class="fas fa-star-half-alt fa-sm"></i>
                    </li>
                  </ul>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(5).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">Maria Kate</h5>
                  <p>Photographer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="far fa-star fa-sm"></i></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Single item -->
          <div class="carousel-item">
            <div class="container">
              <div class="row">
                <div class="col-lg-4">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(6).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">Anna Deynah</h5>
                  <p>UX Designer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod eos id
                    officiis hic tenetur quae quaerat ad velit ab hic tenetur.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                  </ul>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(8).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">John Doe</h5>
                  <p>Web Developer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis
                    suscipit laboriosam, nisi ut aliquid commodi.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li>
                      <i class="fas fa-star-half-alt fa-sm"></i>
                    </li>
                  </ul>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                  <img class="rounded-circle shadow-1-strong mb-4"
                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/img%20(7).webp" alt="avatar"
                    style="width: 150px;" />
                  <h5 class="mb-3">Maria Kate</h5>
                  <p>Photographer</p>
                  <p class="text-muted">
                    <i class="fas fa-quote-left pe-2"></i>
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti.
                  </p>
                  <ul class="list-unstyled d-flex justify-content-center text-warning mb-0">
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="fas fa-star fa-sm"></i></li>
                    <li><i class="far fa-star fa-sm"></i></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Famous Categories -->
  <div class="categorie py-5">
    <div class="container">
      <h1 class="text-center"><b><span>Famous</span> Categories</b></h1>

      <div id="carouselMultiItemExample" data-mdb-carousel-init class="carousel slide carousel-dark text-center" data-mdb-ride="carousel">
        <!-- Controls -->
        <div class="d-flex justify-content-center mb-4">
          <div class="d-flex justify-content-center mb-4">
            <button class="carousel-control-prev" type="button" data-mdb-target="#carouselMultiItemExample" data-mdb-slide="prev">
              <i class="fas fa-chevron-left arrow" aria-hidden="true"></i> <!-- Font Awesome Icon -->
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-mdb-target="#carouselMultiItemExample" data-mdb-slide="next">
              <i class="fas fa-chevron-right arrow" aria-hidden="true"></i> <!-- Font Awesome Icon -->
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>

        <!-- Inner -->
        <div class="carousel-inner py-4">
          <!-- Single item -->
          <div class="carousel-item active">
            <div class="container">
              <div class="row">
                <div class="col-4">
                  <div class="card bg-transparent mx-2">
                    <img class="card-img-top" src="assets/images/html" alt="HTML">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">HTML Basics</h5>
                      <p class="card-text">By John Doe</p>
                      <span class="mb-3">
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                      </span>
                      <div class="mt-auto">
                        <a href="#" class="custom-btn text-decoration-none text-white">Price: $10</a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-4">
                  <div class="card bg-transparent mx-2">
                    <img class="card-img-top" src="assets/images/html" alt="HTML">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">HTML Basics</h5>
                      <p class="card-text">By John Doe</p>
                      <span class="mb-3">
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                      </span>
                      <div class="mt-auto">
                        <a href="#" class="custom-btn text-decoration-none text-white">Price: $10</a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-4">
                  <div class="card bg-transparent mx-2">
                    <img class="card-img-top" src="assets/images/html" alt="HTML">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">HTML Basics</h5>
                      <p class="card-text">By John Doe</p>
                      <span class="mb-3">
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                      </span>
                      <div class="mt-auto">
                        <a href="#" class="custom-btn text-decoration-none text-white">Price: $10</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Single item -->
          <div class="carousel-item">
            <div class="container">
              <div class="row">
                <div class="col-4">
                  <div class="card bg-transparent mx-2">
                    <img class="card-img-top" src="assets/images/html" alt="HTML">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">HTML Basics</h5>
                      <p class="card-text">By John Doe</p>
                      <span class="mb-3">
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                      </span>
                      <div class="mt-auto">
                        <a href="#" class="custom-btn text-decoration-none text-white">Price: $10</a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-4">
                  <div class="card bg-transparent mx-2">
                    <img class="card-img-top" src="assets/images/html" alt="HTML">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">HTML Basics</h5>
                      <p class="card-text">By John Doe</p>
                      <span class="mb-3">
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                      </span>
                      <div class="mt-auto">
                        <a href="#" class="custom-btn text-decoration-none text-white">Price: $10</a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-4">
                  <div class="card bg-transparent mx-2">
                    <img class="card-img-top" src="assets/images/html" alt="HTML">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">HTML Basics</h5>
                      <p class="card-text">By John Doe</p>
                      <span class="mb-3">
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                        <i class="bi bi-star-fill" style="color: gold;"></i>
                      </span>
                      <div class="mt-auto">
                        <a href="#" class="custom-btn text-decoration-none text-white">Price: $10</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- Become a teacher -->
  <div class="teacher py-5">
    <div class="container">
      <h2 class="mb-4 text-center"><b><span>Become</span> a Teacher</b></h2>
      <h5 class="text-center mb-2">Share Your Knowledge, Inspire the Future</h5>
      <p class="text-center mb-5">
        Join our community of educators and start making an impact by sharing your expertise.
        Whether you’re a professional in coding, design, or any field, TOTC makes it easy
        for you to create and manage courses, reach eager learners, and earn from your passion.
      </p>

      <!-- Why Teach with Us? -->
      <div class="row">
        <div class="col-md-3">
          <div class="card bg-transparent border-0">
            <div class="card-body">
              <h5 class="card-title"><b>Flexible Schedule</b></h5>
              <p>Teach anytime, anywhere.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-transparent border-0">
            <div class="card-body">
              <h5 class="card-title"><b>Earn Extra Income</b></h5>
              <p>Get paid for every enrollment.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-transparent border-0">
            <div class="card-body" style="padding-bottom: 0px;">
              <h5 class="card-title"><b>Global Reach</b></h5>
              <p>Connect with students worldwide.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-transparent border-0">
            <div class="card-body" style="padding-bottom: 0px;">
              <h5 class="card-title"><b>Simple Tools</b></h5>
              <p>Course creation tools and support.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action Button -->
      <div class="d-flex justify-content-center mt-4">
        <a href="#" class="custom-btn text-decoration-none  px-4 py-2">Start Teaching Today</a>
      </div>
    </div>
  </div>

  <!-- footer -->
  <?php include 'includes/footer.php' ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <script src="assets/js/owl.carousel.min.js"></script>
  <script src="assets/js/custom.js"></script>

  <!-- JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const filterButtons = document.querySelectorAll('.filter-btn');
      const galleryItems = document.querySelectorAll('[data-category]');

      filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();

          // Remove active class from all buttons
          filterButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');

          const filterValue = this.getAttribute('data-filter');

          // Show or hide images based on the selected filter
          galleryItems.forEach(item => {
            if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
              item.style.display = 'block';
            } else {
              item.style.display = 'none';
            }
          });
        });
      });
    });
  </script>

</body>

</html>
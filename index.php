<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <!-- Bootstrap CSS link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Bootstrap Icons link -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
  <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <!-- header -->
 <?php include 'includes/header.php' ?>

  <!-- banner -->
  <div class="banner">
    <div class="container">
      <div class="row">
        <div class="col-md-6 mt-5">
          <div class="d-flex ms-5">
            <h1 class="text-dark ms-5 mt-5 me-2">Studying </h1>
            <h1 class="mt-5 text-white"> Online is now</h1>
          </div>
          <div class="ms-5">
            <h1 class="ms-5 text-white"> much easier</h1>
          </div>
          <div>
            <div class="ms-5">
              <p class="ms-5 text-white">
                Interactive E-Tutor is an interesting platform that will teach <br>you in a more interactive way
              </p>
              <button class="btn-new ms-5 text-dark" type="submit">Get Started</button>

            </div>
          </div>
        </div>
        <div class="col-md-6">
          <img class="banner1" src="assets/images/banner1" alt="banner1" height="550px">
        </div>
      </div>
    </div>
  </div>

  <!-- Why interactive E-Tutor -->
  <div class="container ">
    <div class="row py-5">
      <div class="col-md-12 pb-5 d-flex justify-content-center">
        <h1 class="me-2 color-252641">Why</h1>
        <h1 class="color-F48C06">Interactive E-Tutor?</h1>
      </div>
      <div class="col-md-6">
        <div>
          <h1>"Everything You Can Do in a Classroom, You Can Do with TOTC"</h1>
        </div>
        <div>
          <p class="color-666">TOTC’s school management software helps traditional and online schools manage scheduling,
            attendance, payments and virtual classrooms all in one secure cloud-based system.</p>
        </div>
        <div>
          <button class="btn-n btn-light my-2 text-white" type="submit">Get Started</button>
        </div>
      </div>
      <div class="col-md-6  position-relative">
        <div class="ms-5">
          <img src="assets/images/php" alt="why" class="ms-5 mb-4" height="400px">
        </div>

      </div>
    </div>
  </div>

  <!-- Courses  -->
  <div class="famous-categorie py-5">
    <div class="container">
      <div id="courseCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="col-md-12">
          <h1 class="text-center ">Courses</h1>
        </div>
        <div class="col-md-12 d-flex justify-content-center py-3">
          <button class="btn-n me-3 text-white"> All</button>
          <button class="btn-n me-3 text-white"> HTML</button>
          <button class="btn-n me-3 text-white"> CSS</button>
          <button class="btn-n me-3 text-white"> PHP</button>
          <button class="btn-n me-3 text-white"> Bootstrap</button>
          <button class="btn-n me-3 text-white"> XML</button>
          <button class="btn-n me-3 text-white"> Java</button>
          <button class="btn-n me-3 text-white"> Node JS</button>
          <button class="btn-n me-3 text-white"> JS</button>
        </div>
        <div class="carousel-inner mb-4">
          <div class="carousel-item active">
            <div class="row">
              <!-- Card 1 -->
              <div class="col-md-4 mt-5">
                <div class="card border-0 bg-transparent" style="width: 100%;">
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
                      <a href="#" class="btn-filter text-decoration-none text-white">Price: $10</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 2 -->
              <div class="col-md-4 mt-5">
                <div class="card border-0 bg-transparent" style="width: 100%;">
                  <img class="card-img-top" src="assets/images/css" alt="CSS">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">CSS Mastery</h5>
                    <p class="card-text">By Jane Smith</p>
                    <span class="mb-3">
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                    </span>
                    <div class="mt-auto">
                      <a href="#" class="btn-filter text-decoration-none text-white">Price: $15</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 3 -->
              <div class="col-md-4 mt-5">
                <div class="card border-0 bg-transparent" style="width: 100%;">
                  <img class="card-img-top" src="assets/images/php" alt="Bootstrap">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Bootstrap 5</h5>
                    <p class="card-text">By Alex Johnson</p>
                    <span class="mb-3">
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                    </span>
                    <div class="mt-auto">
                      <a href="#" class="btn-filter text-decoration-none text-white">Price: $20</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Second carousel item -->
          <div class="carousel-item">
            <div class="row">
              <!-- Card 4 -->
              <div class="col-md-4 mt-5">
                <div class="card border-0 bg-transparent" style="width: 100%;">
                  <img class="card-img-top" src="assets/images/php" alt="JavaScript">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">JavaScript Essentials</h5>
                    <p class="card-text">By Emily Davis</p>
                    <span class="mb-3">
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                    </span>
                    <div class="mt-auto">
                      <a href="#" class="btn-filter text-decoration-none text-white">Price: $25</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 5 -->
              <div class="col-md-4 mt-5">
                <div class="card border-0 bg-transparent" style="width: 100%;">
                  <img class="card-img-top" src="assets/images/java" alt="Java">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Java Programming</h5>
                    <p class="card-text">By Michael Brown</p>
                    <span class="mb-3">
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                    </span>
                    <div class="mt-auto">
                      <a href="#" class="btn-filter text-decoration-none text-white">Price: $30</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Card 6 -->
              <div class="col-md-4 mt-5">
                <div class="card border-0 bg-transparent" style="width: 100%;">
                  <img class="card-img-top" src="assets/images/php" alt="PHP">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">PHP Development</h5>
                    <p class="card-text">By Sarah Lee</p>
                    <span class="mb-3">
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                      <i class="bi bi-star-fill" style="color: gold;"></i>
                    </span>
                    <div class="mt-auto">
                      <a href="#" class="btn-filter text-decoration-none text-white">Price: $35</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#courseCarousel" data-bs-slide="prev"
          style="display:none;">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#courseCarousel" data-bs-slide="next"
          style="display:none;">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Features -->
  <div class="feature py-5">
    <div class="container">
      <div class="row">
        <!-- Center the text horizontally and vertically -->
        <div class="col-md-12 py-2 d-flex justify-content-center align-items-center">
          <h1 class="me-2 text-center color-F48C06">Our</h1>
          <h1 class="color-252641">Features</h1>
        </div>
        <div class="text-center">
          <p class="color-666">This very extraordinary feature, can make learning activities more efficient</p>
        </div>
      </div>
      <div class="row mb-5">
        <div class="col-md-6">
          <a href="#"><img src="assets/images/video" alt="" height="300px" class="ms-5 mt-4"></a>
        </div>
        <div class="col-md-6 py-5">
          <div class="">
            <h1> Interactive Video Streaming</h1>
          </div>
          <div class="">
            <p class="color-666">Engage students with video lessons that pause for quizzes to reinforce learning. If
              they pass, the video
              continues; if not, it replays, ensuring active understanding.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="more-feature py-5 ">
    <div class="container mt-4">
      <div class="row">
        <div class="col-md-6 py-5">
          <div>
            <h1 class="color-F48C06">Assessment & Interactive </h1>
            <h1>Learning Tools</h1>
          </div>
          <p class="color-666">Empower teachers to assign quizzes, homework, and host live Zoom classes for real-time
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
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center color-49BBBD mb-3">Testimonial</h1>
        </div>
        <div class="text-center mb-4">
          <p>Some quick example text to build on the card title and make up the bulk of the card's</p>
        </div>

        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
                content.</p>
              <div class="d-flex">
                <img class="card-img-top" src="..." alt="Card image cap">
                <h5 class="card-title">Card title</h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
                content.</p>
              <h5 class="card-title">Card title</h5>
              <img class="card-img-top" src="..." alt="Card image cap">
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
                content.</p>
              <h5 class="card-title">Card title</h5>
              <img class="card-img-top" src="..." alt="Card image cap">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Famous Categories -->
  <div class="famous-categorie py-5">
    <div class="container">
      <h1 class="text-center color-49BBBD mb-5">Famous Categories</h1>
      <div class="owl-carousel">
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="card bg-transparent">
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
                <a href="#" class="btn-n text-decoration-none text-white">Price: $10</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- become a teacher -->
  <div class="become-teacher-section py-5">
    <div class="container text-center">
      <h2 class="mb-4 fw-bold">Become a Teacher</h2>
      <p class="lead">Share Your Knowledge, Inspire the Future</p>
      <p class="color-666 mb-5">
        Join our community of educators and start making an impact by sharing your expertise.
        Whether you’re a professional in coding, design, or any field, TOTC makes it easy
        for you to create and manage courses, reach eager learners, and earn from your passion.
      </p>

      <!-- Why Teach with Us? -->
      <div class="row justify-content-center">
        <div class="col-md-3 ">
          <div class="card bg-transparent border-0">
            <div class="card-body ">
              <h5 class="card-title fw-bold">Flexible Schedule</h5>
              <p class="card-text color-666">Teach anytime, anywhere.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 ">
          <div class="card bg-transparent border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Earn Extra Income</h5>
              <p class="card-text color-666">Get paid for every enrollment.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-transparent border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Global Reach</h5>
              <p class="card-text color-666">Connect with students worldwide.</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 ">
          <div class="card bg-transparent border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Simple Tools</h5>
              <p class="card-text color-666">Course creation tools and support.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action Button -->
      <div class="d-flex justify-content-center mt-4">
        <a href="#" class="btn-n text-decoration-none  px-4 py-2">Start Teaching Today</a>
      </div>
    </div>
  </div>

  <!-- footer -->
   <? include 'includes/footer.php' ?> 

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <script src="assets/js/owl.carousel.min.js"></script>
  <script src="assets/js/custom.js"></script>

</body>

</html>
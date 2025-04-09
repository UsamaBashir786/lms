<?php
session_start();
include 'db/db.php'

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- css links -->
  <?php include 'includes/css-links.php' ?>
</head>

<body>
  <!-- header  -->
  <?php include 'includes/header.php' ?>
  <?php include 'includes/preloader.php' ?>
  <div style="display: none;" id="main-content">
    <!-- Swiper -->
    <section id="home" class="hero-section">
      <div class="hero-content">
        <h1>Welcome to Learning Management System</h1>
        <p>Access a wide variety of courses and enhance your skills</p>
        <a href="#courses" class="btn btn-custom">Explore Courses</a>
      </div>
    </section>
    <!-- Our Popular Courses -->
    <section id="courses" class="container my-5">
      <h2 class="text-center mb-5">Our Popular Courses</h2>
      <div class="row">
        <!-- Course Card 1 -->
        <div class="col-md-4 mb-4">
          <div class="card course-card">
            <img src="assets\images\webdevelopment.jpg" alt="Course Image 1">
            <div class="card-body course-card-body">
              <h5>Web Development</h5>
              <p>Learn the fundamentals of front-end and back-end web development with practical projects.</p>
              <a href="courses.php" class="btn-learn-more">Learn More</a>
            </div>
          </div>
        </div>
        <!-- Course Card 2 -->
        <div class="col-md-4 mb-4">
          <div class="card course-card">
            <img src="assets\images\datascience.jpg" alt="Course Image 2">
            <div class="card-body course-card-body">
              <h5>Data Science</h5>
              <p>Explore data analysis, machine learning, and big data with hands-on exercises and projects.</p>
              <a href="courses.php" class="btn-learn-more">Learn More</a>
            </div>
          </div>
        </div>
        <!-- Course Card 3 -->
        <div class="col-md-4 mb-4">
          <div class="card course-card">
            <img src="assets\images\digitalmarkiting.webp" alt="Course Image 3">
            <div class="card-body course-card-body">
              <h5>Digital Marketing</h5>
              <p>Master the art of online marketing, SEO, social media strategies, and more in this course.</p>
              <a href="courses.php" class="btn-learn-more">Learn More</a>
            </div>
          </div>
        </div>
        <!-- Course Card 4 -->
        <div class="col-md-4 mb-4">
          <div class="card course-card">
            <img src="assets\images\graphicsdesign.jpg" alt="Course Image 4">
            <div class="card-body course-card-body">
              <h5>Graphic Design</h5>
              <p>Learn the tools and techniques of graphic design, including Photoshop, Illustrator, and more.</p>
              <a href="courses.php" class="btn-learn-more">Learn More</a>
            </div>
          </div>
        </div>
        <!-- Course Card 5 -->
        <div class="col-md-4 mb-4">
          <div class="card course-card">
            <img src="assets\images\python.webp" alt="Course Image 5">
            <div class="card-body course-card-body">
              <h5>Python Programming</h5>
              <p>Master Python with this course covering everything from basics to advanced topics like web scraping.</p>
              <a href="courses.php" class="btn-learn-more">Learn More</a>
            </div>
          </div>
        </div>
        <!-- Course Card 6 -->
        <div class="col-md-4 mb-4">
          <div class="card course-card">
            <img src="assets\images\ai.jpg" alt="Course Image 6">
            <div class="card-body course-card-body">
              <h5>AI & Machine Learning</h5>
              <p>Learn the basics and advanced concepts of Artificial Intelligence and Machine Learning with hands-on projects.</p>
              <a href="courses.php" class="btn-learn-more">Learn More</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Features Section -->
    <section id="features" class="features-section">
      <div class="container">
        <h2>Our Features</h2>
        <div class="row">
          <!-- Feature 1 -->
          <div class="col-md-4 mb-4">
            <div class="card feature-card">
              <div class="card-body feature-card-body">
                <div class="icon">
                  <i class="bi bi-chat-square-text"></i> <!-- Bootstrap Icon for illustration -->
                </div>
                <h5>Interactive Learning</h5>
                <p>Engage with interactive lessons, quizzes, and live discussions to boost learning.</p>
              </div>
            </div>
          </div>
          <!-- Feature 2 -->
          <div class="col-md-4 mb-4">
            <div class="card feature-card">
              <div class="card-body feature-card-body">
                <div class="icon">
                  <i class="bi bi-book-half"></i> <!-- Bootstrap Icon for illustration -->
                </div>
                <h5>Wide Range of Courses</h5>
                <p>Access a variety of courses on different subjects and skills, anytime, anywhere.</p>
              </div>
            </div>
          </div>
          <!-- Feature 3 -->
          <div class="col-md-4 mb-4">
            <div class="card feature-card">
              <div class="card-body feature-card-body">
                <div class="icon">
                  <i class="bi bi-person-check"></i> <!-- Bootstrap Icon for illustration -->
                </div>
                <h5>Personalized Learning</h5>
                <p>Get a learning experience tailored to your skills and needs with personalized feedback.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <!-- Feature 4 -->
          <div class="col-md-4 mb-4">
            <div class="card feature-card">
              <div class="card-body feature-card-body">
                <div class="icon">
                  <i class="bi bi-clock"></i> <!-- Bootstrap Icon for illustration -->
                </div>
                <h5>Flexible Timings</h5>
                <p>Learn at your own pace with flexible timings and lifetime access to course materials.</p>
              </div>
            </div>
          </div>
          <!-- Feature 5 -->
          <div class="col-md-4 mb-4">
            <div class="card feature-card">
              <div class="card-body feature-card-body">
                <div class="icon">
                  <i class="bi bi-people"></i> <!-- Bootstrap Icon for illustration -->
                </div>
                <h5>Community Support</h5>
                <p>Join a community of learners and instructors to share ideas and get support.</p>
              </div>
            </div>
          </div>
          <!-- Feature 6 -->
          <div class="col-md-4 mb-4">
            <div class="card feature-card">
              <div class="card-body feature-card-body">
                <div class="icon">
                  <i class="bi bi-gear"></i> <!-- Bootstrap Icon for illustration -->
                </div>
                <h5>Advanced Tools</h5>
                <p>Utilize advanced tools and features like progress tracking and course completion certificates.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Portal Access Section -->
    <div class="portal-access">
      <div class="portal-content">
        <h2>Access Your Portal</h2>
        <p>Choose your portal to access relevant information and resources.</p>
        <div class="portal-buttons">
          <a href="student/student-portal-login.php" class="btn btn-student">Access Student Portal</a>
          <a href="teacher/teacher-portal-login.php" class="btn btn-teacher">Access Teacher Portal</a>
        </div>
      </div>
    </div>
    <!-- Learning Website Details Section -->
    <div class="learning-website-section">
      <div class="details-container">
        <div class="text-content">
          <h2>Why Choose Our Learning Platform?</h2>
          <p>
            Our platform offers interactive and engaging learning experiences designed to help you master new skills
            effectively. With expert instructors, real-world projects, and flexible learning options, you'll achieve
            your goals in no time.
          </p>
          <ul>
            <li><strong>Expert Instructors:</strong> Learn from industry professionals.</li>
            <li><strong>Flexible Learning:</strong> Access courses anytime, anywhere.</li>
            <li><strong>Practical Projects:</strong> Work on real-world assignments.</li>
            <li><strong>Certification:</strong> Earn certificates to showcase your skills.</li>
          </ul>
          <button class="btn custom-btn text-white" style="background-color: var(--primary-color);" onclick="location.href='courses.php'">See Demo</button>
        </div>
        <div class="image-content">
          <img src="assets/images/graphicsdesign.jpg" alt="Learning Platform Demo">
        </div>
      </div>
    </div>
    <!-- Assessment & Interactive Learning Tools -->
    <div class="tool py-5">
      <div class="container mt-4">
        <div class="row">
          <div class="col-md-6 py-5">
            <h1><span>Assessment</span> & Interactive Learning Tools</h1>
            <p class="mt-4">
              Empower teachers to assign quizzes, homework, and host live Zoom classes for real-time interaction, ensuring students are consistently engaged, evaluated, and supported throughout their learning journey
            </p>
          </div>
          <div class="col-md-6">
            <a href="#"><img src="assets/images/aaq" class="w-100" alt="" height="350px" /></a>
          </div>
        </div>
      </div>
    </div>
    <!-- zoom -->
    <div class="zoom-section">
      <!-- Left Content -->
      <div class="content">
        <h2>Join Our Zoom Meeting</h2>
        <p>Zoom is a powerful and user-friendly video conferencing platform that allows you to host and attend virtual meetings. Whether you're holding a one-on-one meeting or a large conference, Zoom offers an array of features like screen sharing, whiteboarding, breakout rooms, and more. It's the perfect tool for remote communication and collaboration.</p>
        <a href="zoom-meeting.php" class="zoom-btn">Start Zoom Meeting Demo</a>
      </div>

      <!-- Right Image -->
      <div class="image">
        <img src="assets/images/zoom.jpg" alt="Zoom Meeting Image">
      </div>
    </div>
    <!-- Testimonials Section -->
    <section id="testimonial" class="testimonial-section">
      <div class="container">
        <h2>What Our Students Say</h2>
        <div class="row">
          <!-- Testimonial 1 -->
          <div class="col-md-4 mb-4">
            <div class="card testimonial-card">
              <img src="assets\images\guy-1.jpg" alt="Student 1">
              <p>"This platform has transformed the way I learn! The courses are engaging, and I can go at my own pace."</p>
              <h5>John Doe</h5>
              <p class="role">Web Developer</p>
            </div>
          </div>
          <!-- Testimonial 2 -->
          <div class="col-md-4 mb-4">
            <div class="card testimonial-card">
              <img src=" assets\images\guy-2.jpg" alt="Student 2">
              <p>"I love the variety of courses and the community support. I have learned so much in a short time!"</p>
              <h5>Jane Smith</h5>
              <p class="role">Data Scientist</p>
            </div>
          </div>
          <!-- Testimonial 3 -->
          <div class="col-md-4 mb-4">
            <div class="card testimonial-card">
              <img src="assets\images\guy-3.jpg" alt="Student 3">
              <p>"The flexibility of learning on this platform is amazing. I can learn whenever and wherever I want."</p>
              <h5>Samuel Lee</h5>
              <p class="role">Digital Marketer</p>
            </div>
          </div>
        </div>
        <!-- Carousel for larger screens -->
        <div id="testimonialCarousel" class="carousel slide d-md-none" data-bs-ride="carousel">
          <div class="carousel-inner">
            <!-- Carousel Item 1 -->
            <div class="carousel-item active">
              <div class="testimonial-card">
                <img src="https://via.placeholder.com/80" alt="Student 1">
                <p>"This platform has transformed the way I learn! The courses are engaging, and I can go at my own pace."</p>
                <h5>John Doe</h5>
                <p class="role">Web Developer</p>
              </div>
            </div>
            <!-- Carousel Item 2 -->
            <div class="carousel-item">
              <div class="testimonial-card">
                <img src="https://via.placeholder.com/80" alt="Student 2">
                <p>"I love the variety of courses and the community support. I have learned so much in a short time!"</p>
                <h5>Jane Smith</h5>
                <p class="role">Data Scientist</p>
              </div>
            </div>
            <!-- Carousel Item 3 -->
            <div class="carousel-item">
              <div class="testimonial-card">
                <img src="https://via.placeholder.com/80" alt="Student 3">
                <p>"The flexibility of learning on this platform is amazing. I can learn whenever and wherever I want."</p>
                <h5>Samuel Lee</h5>
                <p class="role">Digital Marketer</p>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </section>
    <!-- Become a Teacher Section -->
    <section id="becometeacher" class="become-teacher-section">
      <div class="container">
        <h2 class="become-teacher-header">Become a Teacher</h2>
        <p class="text-center mb-5">Are you passionate about teaching? Share your knowledge with students worldwide and make a real difference. It's time to start your teaching journey!</p>

        <div class="row">
          <!-- Section 1 -->
          <div class="col-md-4 mb-4">
            <div class="become-teacher-item">
              <h3>Share Your Passion</h3>
              <p>Teach a variety of subjects you're passionate about. Connect with students eager to learn and inspire them with your expertise.</p>
              <a href="teacher/teacher-registeration.php" class="btn">Apply Now</a>
            </div>
          </div>
          <!-- Section 2 -->
          <div class="col-md-4 mb-4">
            <div class="become-teacher-item">
              <h3>Set Your Own Schedule</h3>
              <p>Have full control over your teaching schedule. Whether you have a few hours or a few days, you decide when to teach!</p>
              <a href="teacher/teacher-registeration.php" class="btn">Apply Now</a>
            </div>
          </div>
          <!-- Section 3 -->
          <div class="col-md-4 mb-4">
            <div class="become-teacher-item">
              <h3>Earn Money While You Teach</h3>
              <p>Start earning income by sharing your knowledge. Set up your courses, and start earning money with every student who enrolls.</p>
              <a href="teacher/teacher-registeration.php" class="btn">Apply Now</a>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Section 4 -->
          <div class="col-md-4 mb-4">
            <div class="become-teacher-item">
              <h3>Access Comprehensive Resources</h3>
              <p>Get access to exclusive tools and resources to create high-quality lessons and courses that engage your students.</p>
              <a href="teacher/teacher-registeration.php" class="btn">Apply Now</a>
            </div>
          </div>
          <!-- Section 5 -->
          <div class="col-md-4 mb-4">
            <div class="become-teacher-item">
              <h3>Reach a Global Audience</h3>
              <p>Expand your reach and share your knowledge with learners from all over the world. Join a global community of instructors!</p>
              <a href=teacher/teacher-registeration.php" class="btn">Apply Now</a>
            </div>
          </div>
          <!-- Section 6 -->
          <div class="col-md-4 mb-4">
            <div class="become-teacher-item">
              <h3>Grow Your Career</h3>
              <p>Build your reputation as a top-tier teacher. Grow your audience, earn a consistent income, and develop your career.</p>
              <a href="teacher/teacher-registeration.php" class="btn">Apply Now</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>


  <!-- scroll to top -->
  <?php include 'includes/scroll-top-btn.php' ?>
  <!-- right sidebar -->
  <?php include 'includes/right-side-bar.php' ?>
  <!-- footer -->
  <?php include 'includes/footer.php' ?>
  <?php include 'includes/js-links.php' ?>

  <!-- Initialize Swiper -->

</body>

</html>
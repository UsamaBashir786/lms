<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'includes/css-links.php' ?>
  <link rel="stylesheet" href="assets/css/contact-css.css">
</head>

<body>
  <!-- Banner Section -->
  <div class="banner">
    <h1>Contact Us <br>
      <a href="index.php" class="btn custom-btn text-white" style="background-color: #0056b3;">Go Back</a>
    </h1>
  </div>

  <!-- Main Content Section -->
  <div class="container custom-container">
    <div class="row">
      <!-- Contact Information Area -->
      <div class="col-md-6">
        <div class="feature-container">
          <h2>Get In Touch</h2>
          <p>We'd love to hear from you! Whether you have a question, feedback, or need support, feel free to reach out.</p>
          <ul class="feature-list">
            <li><i class="fas fa-phone-alt"></i> Call us at: +1234567890</li>
            <li><i class="fas fa-envelope"></i> Email us at: contact@company.com</li>
            <li><i class="fas fa-map-marker-alt"></i> Visit us: 123 Company Street, City, Country</li>
          </ul>
        </div>
      </div>

      <!-- Contact Form Area -->
      <div class="col-md-6">
        <div class="form-section">
          <h2>Send us a Message</h2>
          <form id="contactForm">
            <div>
              <label for="name" class="form-label">Full Name</label>
              <input type="text" id="name" placeholder="Enter your full name" required>
            </div>
            <div>
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" placeholder="Enter your email" required>
            </div>
            <div>
              <label for="message" class="form-label">Your Message</label>
              <textarea id="message" rows="4" placeholder="Write your message here..." required></textarea>
            </div>
            <button type="submit">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php' ?>
  <?php include 'includes/js-links.php' ?>

  <!-- Form Validation -->
  <script src="assets/js/contact.js"></script>
</body>

</html>
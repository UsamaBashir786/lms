<footer class="footer">
  <div class="container">
    <div class="row">
      <!-- Logo and Contact -->
      <div class="col-md-4 footer-section">
        <div class="footer-logo">E-Learning</div>
        <p><strong>Call:</strong> <a href="tel:1234567890">(123) 456-7890</a></p>
        <p><strong>Email:</strong> <a href="mailto:info@elearning.cc">info@elearning.cc</a></p>
      </div>

      <!-- Links -->
      <div class="col-md-4 footer-section">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="#">HTML</a></li>
          <li><a href="#">CSS</a></li>
          <li><a href="#">Bootstrap</a></li>
          <li><a href="#">PHP</a></li>
          <li><a href="#">React</a></li>
          <li><a href="#">XML</a></li>
        </ul>
      </div>

      <!-- Help and Legal -->
      <div class="col-md-4 footer-section">
        <h5>Support & Legal</h5>
        <ul class="list-unstyled">
          <li><a href="#">Help Desk</a></li>
          <li><a href="#">About Us</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">Legal Concerns</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms & Conditions</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2024. All rights reserved.</p>
    </div>
  </div>
</footer>

<style>
  :root {
    --hover-color: var(--primary-color) ;  /* Hover effect color */
  }

  .footer {
    background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)),
      url('assets/images/background-contact.jpg') center/cover no-repeat;
    color: #f8f9fa;
    padding: 40px 0;
  }

  .footer a {
    color: #f8f9fa;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  .footer a:hover {
    color: var(--hover-color);
    text-decoration: underline;
  }

  .footer .footer-logo {
    font-size: 28px;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--primary-color);
    margin-bottom: 15px;
    display: inline-block;
    transition: color 0.3s ease;
  }

  .footer .footer-logo:hover {
    color: var(--hover-color);
  }

  .footer-section h5 {
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 600;
    color: #f8f9fa;
  }

  .footer-section ul {
    padding: 0;
    list-style: none;
  }

  .footer-section ul li {
    margin-bottom: 10px;
  }

  .footer-section ul li a {
    font-size: 16px;
  }

  .footer-bottom {
    text-align: center;
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 14px;
  }

  @media (max-width: 768px) {
    .footer-section {
      margin-bottom: 30px;
    }
    .footer-logo {
      font-size: 24px;
    }
  }
</style>

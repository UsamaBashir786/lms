<!-- Floating Hamburger Icon -->
<div class="formal-hamburger-circle" onclick="toggleFormalSidebar()">
  <div class="formal-hamburger">
    <span></span>
    <span></span>
    <span></span>
  </div>
</div>

<!-- Right Sidebar -->
<div class="formal-sidebar" id="formalSidebar">
  <div class="formal-sidebar-header">
    <h2 class="text-white">LMS</h2>
    <button class="formal-close-btn" onclick="toggleFormalSidebar()">×</button>
  </div>
  <ul class="formal-sidebar-menu">
    <li><a href="student/student-portal-login.php">Student Portal</a></li>
    <li><a href="teacher/teacher-portal-login.php">Teacher Portal</a></li>
    <li><a href="teacher/teacher-registeration.php">Register As Teacher</a></li>
    <li><a href="student/student-registeration.php">Register As Student</a></li>
    <li><a href="contact.php">Contact US</a></li>
  </ul>

  <!-- Social Icons Section -->
  <div class="social-icons">
    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
  </div>
</div>
<script>
  function toggleFormalSidebar() {
    const sidebar = document.getElementById("formalSidebar");
    const hamburgerCircle = document.querySelector(".formal-hamburger-circle");
    sidebar.classList.toggle("open");
    hamburgerCircle.classList.toggle("open");
  }
</script>











































<style>
  :root {
    /* Colors */
    --circle-bg: goldenrod;
    --circle-hover: #008CBA;
    --icon-color: white;
    --sidebar-bg: rgba(0, 0, 0, 0.95);
    --header-bg: goldenrod;
    --link-color: #fff;
    --link-hover: #fff;
    --close-btn-color: #ecf0f1;
    --transition-speed: 0.4s;
    --circle-size: 37px;
    --icon-bar-width: 23px;
    --icon-bar-height: 4px;
    --sidebar-width: 320px;
  }

  /* Floating Circle */
  .formal-hamburger-circle {
    position: fixed;
    bottom: 70px;
    right: 20px;
    width: var(--circle-size);
    height: var(--circle-size);
    background-color: var(--circle-bg);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    transition: background-color var(--transition-speed), transform var(--transition-speed);
  }

  .formal-hamburger-circle:hover {
    background-color: var(--circle-hover);
    transform: scale(1.1);
  }

  /* Hamburger Icon */
  .formal-hamburger {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: var(--icon-bar-width);
    height: calc(var(--icon-bar-height) * 4);
  }

  .formal-hamburger span {
    display: block;
    background-color: var(--icon-color);
    height: var(--icon-bar-height);
    border-radius: 3px;
    transition: all var(--transition-speed);
  }

  /* Hamburger Animation */
  .formal-hamburger-circle.open .formal-hamburger span:nth-child(1) {
    transform: rotate(45deg) translate(7px, 7px);
  }

  .formal-hamburger-circle.open .formal-hamburger span:nth-child(2) {
    opacity: 0;
  }

  .formal-hamburger-circle.open .formal-hamburger span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -7px);
  }

  /* Sidebar */
  .formal-sidebar {
    position: fixed;
    top: 0;
    right: calc(-1 * var(--sidebar-width));
    width: var(--sidebar-width);
    height: 100%;
    background-color: var(--sidebar-bg);
    color: var(--icon-color);
    box-shadow: -3px 0 10px rgba(0, 0, 0, 0.3);
    overflow-y: auto;
    transition: right var(--transition-speed);
    z-index: 999;
    overflow-x: hidden;
  }

  .formal-sidebar.open {
    right: 0;
  }

  .formal-sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: var(--header-bg);
    color: var(--icon-color);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .formal-close-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--close-btn-color);
    cursor: pointer;
    transition: color var(--transition-speed);
  }

  .formal-close-btn:hover {
    color: var(--link-hover);
  }

  .formal-sidebar-menu {
    list-style: none;
    padding: 20px;
  }

  .formal-sidebar-menu li {
    margin: 15px 0;
    opacity: 0;
    /* Start hidden for animation */
    animation: slideInFromRight var(--transition-speed) ease-out forwards;
  }

  .formal-sidebar.open .formal-sidebar-menu li:nth-child(1) {
    animation-delay: 0.1s;
  }

  .formal-sidebar.open .formal-sidebar-menu li:nth-child(2) {
    animation-delay: 0.2s;
  }

  .formal-sidebar.open .formal-sidebar-menu li:nth-child(3) {
    animation-delay: 0.3s;
  }

  .formal-sidebar.open .formal-sidebar-menu li:nth-child(4) {
    animation-delay: 0.4s;
  }

  /* Sidebar Links */
  .formal-sidebar-menu a {
    text-decoration: none;
    color: var(--circle-bg);
    font-size: 18px;
    display: block;
    padding: 10px;
    position: relative;
    transition: color var(--transition-speed);
  }

  /* Line animation on hover */
  .formal-sidebar-menu a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--link-hover);
    transition: width var(--transition-speed) ease-in-out;
  }

  .formal-sidebar-menu a:hover::after {
    width: 100%;
  }

  .formal-sidebar-menu a:hover {
    color: var(--link-hover);
    /* Optional link color change */
    transform: scaleY(1.5);
  }

  /* Social Icons */
  .social-icons {
    display: flex;
    justify-content: center;
    margin-top: 30px;
  }

  .social-icon {
    font-size: 24px;
    margin: 10px;
    color: var(--icon-color);
    transition: color var(--transition-speed);
  }

  .social-icon:hover {
    color: var(--link-hover);
    /* Change color on hover */
  }

  .social-icon i {
    display: block;
  }

  /* Keyframes for slide-in animation */
  @keyframes slideInFromRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }

    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  /* Media Query for small screens */
  @media (max-width: 768px) {
    .social-icons {
      margin-top: 20px;
    }
  }

  /* Social Icons */
  .social-icons {
    display: flex;
    justify-content: center;
    margin-top: 30px;
  }

  .social-icon {
    font-size: 24px;
    margin: 10px;
    color: #fff;
    transition: transform 0.3s ease, color 0.3s ease;
  }

  .social-icon:hover {
    transform: scale(1.2);
    color: #008CBA;
    /* Hover color */
  }

  .social-icon i {
    display: block;
  }

  /* Add animation effect for hover */
  @keyframes bounce {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.3);
    }

    100% {
      transform: scale(1);
    }
  }

  .social-icon:hover {
    animation: bounce 0.6s ease;
  }
</style>
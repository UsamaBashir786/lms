<?php include 'includes/header.php'; ?>

  <!-- Contact Us -->
  <div class="contact">
    <div class="banner">
      <div class="content">
        <h1 class="text-center">Contact Information</h1>
        <p class="mt-3 text-center">Lorem ipsum dolor sit amet consectetur adipisicing elit. Numquam quo voluptatibus <br> nesciunt consequatur libero modi deserunt id cum assumenda rerum.</p>
      </div>
    </div>
  </div>

  <!-- Info -->
  <div class="info py-5">
    <div class="container">
      <div class="row">
        <div class="col-4">
          <div class="card bg-white border-0 rounded-0 mb-4 py-2">
            <div class="card-body">
              <ul class="list-unstyled d-flex align-items-center m-0">
                <li class="d-flex align-items-center">
                  <i class="me-2"><img src="/assets/images/call2.png" alt="call" width="24"></i>
                  <a href="tel: (123) 456-7890" class="text-decoration-none text-dark">(123) 456-7890</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="card bg-white border-0 rounded-0 mb-4 py-2">
            <div class="card-body">
              <ul class="list-unstyled d-flex align-items-center m-0">
                <li class="d-flex align-items-center">
                  <i class="me-2"><img src="/assets/images/mail2.png" alt="call" width="24"></i>
                  <a href="email: info@elearning.cc" class="text-decoration-none text-dark">info@elearning.cc</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="card bg-white border-0 rounded-0 py-2">
            <div class="card-body">
              <ul class="list-unstyled d-flex align-items-center m-0">
                <li class="d-flex align-items-center">
                  <i class="me-2"><img src="/assets/images/location.png" alt="call" width="24"></i>
                  <p class="d-flex align-items-center m-0">111 karbala road Sahiwal Pakistan</p>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-8">
          <div class="card border-0 rounded-0 bg-white">
            <div class="card-body" style="padding: 20px;">
              <h5><b>Send Message</b></h5>
              <form method="post" action="">
                <div class="row">
                  <div class="col-6">
                    <div class="mb-3">
                      <input type="name" name="name" class="form-control" id="name" placeholder="Your name" required>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <input type="email" name="email" class="form-control" id="email" placeholder="Email address" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6">
                    <div class="mb-3">
                      <input type="phone" name="phone" class="form-control" id="phone" placeholder="Phone no" required>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <input type="subject" name="subject" class="form-control" id="subject" placeholder="Subject" required>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" id="message" name="message" placeholder="Message" rows="3" required></textarea>
                </div>
                <div class="msg mb-2">
                  <a href="#" class="btn btn-light custom-btn rounded-0 pe-3 ps-3 py-2">Send Message</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <?php include 'includes/footer.php'; ?>
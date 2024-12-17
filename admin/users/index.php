<?php require_once '../header.php'; ?>
<?php require_once '../sidebar.php'; ?>

<div class="col-lg-10">
  <div class="container">
    <div class="row mt-4 ps-2">
      <div class="col-lg-12 p-0 d-flex justify-content-between align-items-center border-bottom pb-3">
        <h3>Users</h3>
        <a href="add.php"><button type="button" class="btn btn-primary rounded-0 pe-3 ps-3">Add user</button></a>
      </div>
    </div>
  </div>

  <!-- Start of the user table -->
  <div class="user">
    <table class="table table-hover table-striped text-center mt-3">
      <thead>
        <tr>
          <th><input type="checkbox"></th>
          <th scope="col">ID</th>
          <th scope="col">First Name</th>
          <th scope="col">Last Name</th>
          <th scope="col">Email</th>
          <th scope="col">Phone</th>
          <th scope="col">DOB</th>
          <th scope="col">Gender</th>
          <th scope="col">Action</th>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>1</td>
          <td>John</td>
          <td>Doe</td>
          <td>john@gmail.com</td>
          <td>123-456-7890</td>
          <td>01-01-2020</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>2</td>
          <td>Jack</td>
          <td>William</td>
          <td>jack@gmail.com</td>
          <td>123-456-0987</td>
          <td>02-02-2010</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>1</td>
          <td>John</td>
          <td>Doe</td>
          <td>john@gmail.com</td>
          <td>123-456-7890</td>
          <td>01-01-2020</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>2</td>
          <td>Jack</td>
          <td>William</td>
          <td>jack@gmail.com</td>
          <td>123-456-0987</td>
          <td>02-02-2010</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>1</td>
          <td>John</td>
          <td>Doe</td>
          <td>john@gmail.com</td>
          <td>123-456-7890</td>
          <td>01-01-2020</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>2</td>
          <td>Jack</td>
          <td>William</td>
          <td>jack@gmail.com</td>
          <td>123-456-0987</td>
          <td>02-02-2010</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>1</td>
          <td>John</td>
          <td>Doe</td>
          <td>john@gmail.com</td>
          <td>123-456-7890</td>
          <td>01-01-2020</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>2</td>
          <td>Jack</td>
          <td>William</td>
          <td>jack@gmail.com</td>
          <td>123-456-0987</td>
          <td>02-02-2010</td>
          <td>Male</td>
          <td>
            <a href="#">View</a>
            <a href="#">Edit</a>
            <a href="#">Delete</a>
          </td>
        </tr>
      </thead>
    </table>
  </div>
  <!-- End of the user table -->

  <?php require_once '../footer.php'; ?>
</div>
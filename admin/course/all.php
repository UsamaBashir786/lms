<?php require_once '../header.php' ?>
<?php require_once '../sidebar.php' ?>

<div class="col-10">
  <div class="course">
    <div class="col-lg-12 p-0 d-flex justify-content-between align-items-center border-bottom py-3">
      <h3>Courses</h3>
      <a href="create.php"><button type="button" class="btn btn-primary rounded-0 pe-3 ps-3">Add Course</button></a>
    </div>
    <table class="table table-hover table-striped text-center mt-3">
      <thead>
        <tr>
          <th scope="col"><input type="checkbox"></th>
          <th scope="col">Image</th>
          <th scope="col">Title</th>
          <th scope="col">Category</th>
          <th scope="col">Price</th>
          <th scope="col">Duration</th>
          <th scope="col">Actions</th>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>custom.png</td>
          <td>Html</td>
          <td>Front-End</td>
          <td>30$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>custom.jpg</td>
          <td>CSS</td>
          <td>Front-End</td>
          <td>20$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>custom.svg</td>
          <td>Javascript</td>
          <td>Front-End</td>
          <td>50$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>custom.png</td>
          <td>Html</td>
          <td>Front-End</td>
          <td>30$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>custom.jpg</td>
          <td>CSS</td>
          <td>Front-End</td>
          <td>20$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td>custom.svg</td>
          <td>Javascript</td>
          <td>Front-End</td>
          <td>50$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <tr class="custom-row">
          <td><input type="checkbox"></td>
          <td>custom.svg</td>
          <td>Javascript</td>
          <td>Front-End</td>
          <td>50$</td>
          <td>2 Months</td>
          <td>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">View</a></li>
                <li><a class="dropdown-item" href="#">Edit</a></li>
                <li><a class="dropdown-item" href="#">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
      </thead>
    </table>
  </div>
</div>

<?php require_once '../footer.php' ?>
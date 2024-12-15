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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading1">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse1" aria-expanded="false" aria-controls="usersCollapse1">
                  Select
                </button>
              </h2>
              <div id="usersCollapse1" class="accordion-collapse collapse" aria-labelledby="usersHeading1"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading2">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse2" aria-expanded="false" aria-controls="usersCollapse2">
                  Select
                </button>
              </h2>
              <div id="usersCollapse2" class="accordion-collapse collapse" aria-labelledby="usersHeading2"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading3">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse3" aria-expanded="false" aria-controls="usersCollapse3">
                  Select
                </button>
              </h2>
              <div id="usersCollapse3" class="accordion-collapse collapse" aria-labelledby="usersHeading3"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading4">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse4" aria-expanded="false" aria-controls="usersCollapse4">
                  Select
                </button>
              </h2>
              <div id="usersCollapse4" class="accordion-collapse collapse" aria-labelledby="usersHeading4"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading5">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse5" aria-expanded="false" aria-controls="usersCollapse5">
                  Select
                </button>
              </h2>
              <div id="usersCollapse5" class="accordion-collapse collapse" aria-labelledby="usersHeading5"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading6">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse6" aria-expanded="false" aria-controls="usersCollapse6">
                  Select
                </button>
              </h2>
              <div id="usersCollapse6" class="accordion-collapse collapse" aria-labelledby="usersHeading6"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
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
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="usersHeading7">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="collapse"
                  data-bs-target="#usersCollapse7" aria-expanded="false" aria-controls="usersCollapse7">
                  Select
                </button>
              </h2>
              <div id="usersCollapse7" class="accordion-collapse collapse" aria-labelledby="usersHeading7"
                data-bs-parent="#sidebarAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-unstyled ms-3">
                    <li><a class="dropdown-item" href="#">View</a></li>
                    <li><a class="dropdown-item" href="#">Edit</a></li>
                    <li><a class="dropdown-item" href="#">Delete</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </thead>
    </table>
  </div>
</div>

<?php require_once '../footer.php' ?>
<?php require_once '../header.php'; ?>

<?php require_once '../sidebar.php'; ?>
<div class="col-lg-10">
  <div class="container">
    <div class="row mt-4 ps-4">
      <div class="col-12 d-flex justify-content-between align-items-center pb-4">
        <h3 class="mb-0">Add Course</h3>
        <a href="#" class="arrow-link">
          <a href="all.php"><img src="/assets/images/icons8-arrow-left-48.png" alt="png" width="30"></a>
        </a>
      </div>
    </div>
  </div>

  <body>

    <div class="container">
      <!-- form -->
      <div class="row justify-content-center">
        <div class="col-md-12 ms-4">
          <div class="card mb-3">
            <div class="card-body">
              <form action="submit_course.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="courseTitle" class="form-label">Course Title</label>
                      <input type="text" class="form-control" id="courseTitle" name="course_title" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="courseInstructor" class="form-label">Instructor</label>
                      <select class="form-select" id="courseInstructor" name="instructor">
                        <option value="1">John Doe</option>
                        <option value="2">Jane Smith</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="courseCategory" class="form-label">Category</label>
                      <select class="form-select" id="courseCategory" name="category">
                        <option value="programming">Programming</option>
                        <option value="design">Design</option>
                        <option value="business">Business</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="courseThumbnail" class="form-label">Thumbnail</label>
                      <input type="file" class="form-control" id="courseThumbnail" name="thumbnail">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="coursePrice" class="form-label">Price</label>
                      <input type="number" class="form-control" id="coursePrice" name="price" min="0">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="courseDuration" class="form-label">Duration</label>
                      <input type="text" class="form-control" id="courseDuration" name="duration">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="startDate" class="form-label">Start Date</label>
                      <input type="date" class="form-control" id="startDate" name="start_date">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="courseDescription" class="form-label">Description</label>
                      <textarea class="form-control" id="courseDescription" name="course_description"></textarea>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="status" class="form-label">Status</label>
                      <div>
                        <input type="radio" id="active" name="status" value="active" checked>
                        <label for="active">Active</label>
                        <input type="radio" id="inactive" name="status" value="inactive">
                        <label for="inactive">Inactive</label>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Course</button>
                  </div>
                </div>
              </form>
            </div> <!-- card body -->
          </div> <!-- card -->
        </div> <!-- col -->
      </div> <!-- row -->
    </div>
    <?php require_once '../footer.php'; ?>
</div>
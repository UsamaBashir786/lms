<?php
include 'includes/header.php'; // Include header section

// Get category ID from URL
$categoryId = isset($_GET['id']) ? $_GET['id'] : null;

// Example categories array
$categories = [
  1 => [
    'title' => 'Web Development',
    'description' => 'Learn web development from scratch with HTML, CSS, JavaScript, PHP, and more.',
    'image' => 'assets/images/webdev.jpg',
    'courses' => [
      1 => ['title' => 'HTML', 'price' => '$49.99', 'image' => 'assets/images/htmllogo.png'],
      2 => ['title' => 'CSS', 'price' => '$59.99', 'image' => 'assets/images/css-3.png'],
      3 => ['title' => 'JavaScript', 'price' => '$69.99', 'image' => 'assets/images/java-script.png'],
    ],
    'related' => [2, 3] // Related categories
  ],
  2 => [
    'title' => 'Design',
    'description' => 'Master design principles and tools, including Photoshop, Illustrator, and UI/UX design.',
    'image' => 'assets/images/design.jpg',
    'courses' => [
      4 => ['title' => 'Photoshop Basics', 'price' => '$39.99', 'image' => 'assets/images/photoshop.png'],
      5 => ['title' => 'UI/UX Design', 'price' => '$79.99', 'image' => 'assets/images/uiux.png'],
    ],
    'related' => [1, 4] // Related categories
  ],
  // Add more categories as needed
];

// Get current category details
$currentCategory = isset($categories[$categoryId]) ? $categories[$categoryId] : null;
?>

<!-- Banner Section for the Category -->
<section class="banner" style="background-image: url('<?php echo $currentCategory['image']; ?>');">
  <div class="banner-content text-center py-5">
    <h1><?php echo $currentCategory['title']; ?></h1>
    <p><?php echo $currentCategory['description']; ?></p>
  </div>
</section>

<!-- Courses Grid Section -->
<section class="courses">
  <div class="container">
    <h2 class="mb-3">Courses in <?php echo $currentCategory['title']; ?></h2>
    <div class="row">
      <?php
      // Loop through courses in the category
      foreach ($currentCategory['courses'] as $course) {
        echo '<div class="col-md-4">
                <div class="course-card">
                  <img src="' . $course['image'] . '" alt="' . $course['title'] . '" class="course-image w-50 h-auto">
                  <h3 class="course-title mt-3">' . $course['title'] . '</h3>
                  <p class="course-price">' . $course['price'] . '</p>
                  <a href="course.php?id=' . $course['title'] . '" class="btn btn-primary">View Course</a>
                </div>
              </div>';
      }
      ?>
    </div>
  </div>
</section>

<!-- Related Categories Grid -->
<section class="related-categories">
  <div class="container">
    <h2 class="py-3">Related Categories</h2>
    <div class="row">
      <?php
      // Loop through related categories
      foreach ($currentCategory['related'] as $relatedId) {
        // Check if the relatedId exists in the $categories array
        if (isset($categories[$relatedId])) {
          $relatedCategory = $categories[$relatedId];
          echo '<div class="col-md-3">
                  <div class="category-card">
                    <img src="' . $relatedCategory['image'] . '" alt="' . $relatedCategory['title'] . '" class="category-image">
                    <h3 class="category-title py-3">' . $relatedCategory['title'] . '</h3>
                    <a href="category.php?id=' . $relatedId . '" class="btn btn-secondary mb-5">View Courses</a>
                  </div>
                </div>';
        } else {
          // If related category doesn't exist, you can skip or show a message
          echo '<div class="col-md-3">Related category not found.</div>';
        }
      }
      ?>
    </div>
  </div>
</section>


<?php
include 'includes/footer.php' // Include footer section
?>
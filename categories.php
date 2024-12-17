<?php
include 'includes/header.php' // Include header section
?>

<!-- Banner Section -->
<section class="banner" style="background-image: url('assets/images/banner.jpg');">
  <div class="banner-content text-center py-5">
    <h1>Our Categories</h1>
    <p>Explore courses in various categories and enhance your skills.</p>
  </div>
</section>

<!-- Categories Grid Section -->
<section class="categories mb-5">
  <div class="container">
    <div class="row">
      <?php
      // Assuming you have an array of categories
      $categories = [
        1 => ['title' => 'Web Development', 'image' => 'assets/images/webdesign.png'],
        2 => ['title' => 'Design', 'image' => 'assets/images/design.png'],
        3 => ['title' => 'Programming', 'image' => 'assets/images/programming.png'],
        4 => ['title' => 'Data Science', 'image' => 'assets/images/datascience.png'],
        // Add more categories as needed
      ];

      foreach ($categories as $id => $category) {
        echo '<div class="col-md-3">
                <div class="category-card">
                  <img src="' . $category['image'] . '" alt="' . $category['title'] . '" class="category-image w-50 h-auto">
                  <h3 class="category-title py-2">' . $category['title'] . '</h3>
                  <a href="category.php?id=' . $id . '" class="btn btn-primary">View Courses</a>
                </div>
              </div>';
      }
      ?>
    </div>
  </div>
</section>

<?php
include 'includes/footer.php' // Include footer section
?>

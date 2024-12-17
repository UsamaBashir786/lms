<?php include 'includes/header.php'; ?>

<?php
// Example courses array for demonstration. Replace with database queries in a real application.
$courses = [
  1 => [
    'title' => 'HTML',
    'description' => 'Learn the fundamentals of HTML to create the structure of your web pages.',
    'price' => '$49.99',
    'image' => 'assets/images/htmllogo.png',
    'author' => 'John Doe',
    'content' => 'Introduction, Elements, Attributes, Forms, Tables.',
    'reviews' => [
      ['student' => 'Alice', 'review' => 'Great course! Very informative.'],
      ['student' => 'Bob', 'review' => 'Perfect for beginners!']
    ],
    'related' => [2, 3]
  ],
  2 => [
    'title' => 'CSS',
    'description' => 'Master CSS to style your web pages with stunning layouts and designs.',
    'price' => '$59.99',
    'image' => 'assets/images/css-3.png',
    'author' => 'Jane Smith',
    'content' => 'Selectors, Box Model, Flexbox, Grid, Transitions.',
    'reviews' => [
      ['student' => 'Carol', 'review' => 'Very helpful for learning styling techniques.']
    ],
    'related' => [1, 3]
  ],
  3 => [
    'title' => 'JavaScript',
    'description' => 'Get hands-on experience with JavaScript to make your websites interactive.',
    'price' => '$69.99',
    'image' => 'assets/images/java-script.png',
    'author' => 'Alice Johnson',
    'content' => 'Variables, Functions, DOM Manipulation, Events, ES6.',
    'reviews' => [
      ['student' => 'Dave', 'review' => 'Excellent course for learning JS basics.']
    ],
    'related' => [1, 2]
  ],
  4 => [
    'title' => 'PHP',
    'description' => 'Dive into PHP to develop dynamic server-side web applications.',
    'price' => '$79.99',
    'image' => 'assets/images/phplogo.png',
    'author' => 'Michael Brown',
    'content' => 'Variables, Functions, Arrays, Forms, Databases, Sessions.',
    'reviews' => [
      ['student' => 'Eve', 'review' => 'Amazing course! Very thorough explanation of PHP fundamentals.'],
      ['student' => 'Tom', 'review' => 'I was able to build dynamic websites with this course.']
    ],
    'related' => [5, 6]
  ],
  5 => [
    'title' => 'Bootstrap',
    'description' => 'Learn Bootstrap to quickly build responsive and mobile-friendly websites.',
    'price' => '$49.99',
    'image' => 'assets/images/bootstrap.png',
    'author' => 'Sarah Green',
    'content' => 'Grid System, Components, Modals, Forms, Buttons.',
    'reviews' => [
      ['student' => 'Michael', 'review' => 'Great for rapid front-end development.'],
      ['student' => 'Laura', 'review' => 'This helped me build responsive websites quickly.']
    ],
    'related' => [1, 2]
  ],
  6 => [
    'title' => 'XML',
    'description' => 'Understand XML for data storage and web services.',
    'price' => '$59.99',
    'image' => 'assets/images/xml.png',
    'author' => 'Robert Black',
    'content' => 'Elements, Attributes, Parsing, XSLT, SOAP.',
    'reviews' => [
      ['student' => 'Greg', 'review' => 'Good introduction to XML and its uses in web services.'],
      ['student' => 'Nancy', 'review' => 'I gained solid knowledge of XML with this course.']
    ],
    'related' => [4, 7]
  ],
  7 => [
    'title' => 'Java',
    'description' => 'Master Java to build robust applications and software solutions.',
    'price' => '$89.99',
    'image' => 'assets/images/javalogo.png',
    'author' => 'David Williams',
    'content' => 'OOP, Collections, Streams, Threads, Spring Framework.',
    'reviews' => [
      ['student' => 'James', 'review' => 'Java basics covered in great detail.'],
      ['student' => 'Kathy', 'review' => 'I learned to build powerful applications with Java.']
    ],
    'related' => [4, 5]
  ]
];


// Get course ID from URL
$courseId = $_GET['id'] ?? null;

// Fetch course details
$course = $courses[$courseId] ?? null;

// Redirect if course ID is invalid
if (!$course) {
  header('Location: courses.php');
  exit;
}
?>

<div class="container my-5">
  <div class="row">
    <!-- Course Details Section -->
    <div class="col-md-8">
      <h1 class="mb-4"><?php echo $course['title']; ?></h1>
      <p class="lead"><?php echo $course['description']; ?></p>
      <p><strong>Price: </strong><?php echo $course['price']; ?></p>
      <img src="<?php echo $course['image']; ?>" alt="<?php echo $course['title']; ?>" class="img-fluid w-50 h-auto my-4">
      <h3>Author: <?php echo $course['author']; ?></h3>
      <h4>Course Content</h4>
      <p><?php echo $course['content']; ?></p>
    </div>

    <!-- Sidebar Section -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body text-center">
          <h4>Enroll Now</h4>
          <a href="#" class="btn btn-success btn-lg">Enroll Now</a>
        </div>
      </div>

      <h4 class="mt-5">Student Reviews</h4>
      <ul class="list-group">
        <?php foreach ($course['reviews'] as $review): ?>
          <li class="list-group-item">
            <strong><?php echo $review['student']; ?>:</strong> <?php echo $review['review']; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Related Courses Section -->
  <h3 class="mt-5">Related Courses</h3>
  <div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($course['related'] as $relatedId): ?>
      <?php if (isset($courses[$relatedId])): ?>
        <div class="col">
          <div class="card h-100 shadow">
            <img src="<?php echo $courses[$relatedId]['image']; ?>" class="mt-3 w-50 h-auto mx-auto d-block" alt="<?php echo $courses[$relatedId]['title']; ?>">
            <div class="card-body text-center">
              <h5 class="card-title"><?php echo $courses[$relatedId]['title']; ?></h5>
              <a href="course.php?id=<?php echo $relatedId; ?>" class="btn btn-dark">Details</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
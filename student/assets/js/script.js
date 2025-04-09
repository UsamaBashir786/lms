
// register form validation
document.getElementById("student-form").addEventListener("submit", function(event) {
  event.preventDefault();

  const fullName = document.getElementById("full-name").value;
  const email = document.getElementById("email").value;
  const phone = document.getElementById("phone").value;
  const dob = document.getElementById("dob").value;
  const course = document.getElementById("course").value;
  const password = document.getElementById("password").value;

  if (!fullName || !email || !phone || !dob || !course || !password) {
    alert("Please fill out all fields.");
    return;
  }

  alert("Registration Successful!");
});
// preview image
document.getElementById('profile-img').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('img-preview');

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.src = '#';
    preview.style.display = 'none';
  }
});
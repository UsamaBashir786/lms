document.getElementById("student-login-form").addEventListener("submit", function(event) {
  event.preventDefault();

  const email = document.getElementById("student-email").value;
  const password = document.getElementById("student-password").value;

  // Add form validation if necessary
  if (!email || !password) {
    alert("Please fill out both fields.");
    return;
  }

  // Simulate successful login
  alert("Login Successful! Welcome to the Student Portal.");
});
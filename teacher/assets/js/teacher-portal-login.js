document.getElementById("teacher-login-form").addEventListener("submit", function(event) {
  event.preventDefault();

  const email = document.getElementById("teacher-email").value;
  const password = document.getElementById("teacher-password").value;

  // Add form validation if necessary
  if (!email || !password) {
    alert("Please fill out both fields.");
    return;
  }

  // Simulate successful login
  alert("Login Successful! Welcome to the Teacher Portal.");
});
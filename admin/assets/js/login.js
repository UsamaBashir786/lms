function validateLogin() {
  var username = document.getElementById('username').value;
  var password = document.getElementById('password').value;
  var errorMessage = document.getElementById('error-message');

  // In a real application, this would be a server-side validation
  if (username === 'admin' && password === 'admin') {
    return true;
  } else {
    errorMessage.style.display = 'block';
    return false;
  }
}
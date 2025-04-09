function toggleDropdown() {
  const dropdown = document.getElementById('dropdown-menu');
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Close the dropdown if clicked outside
window.addEventListener('click', function (e) {
  const userProfile = document.querySelector('.user-profile');
  const dropdown = document.getElementById('dropdown-menu');
  if (!userProfile.contains(e.target)) {
      dropdown.style.display = 'none';
  }
});

// Prevent the click event from propagating to the window when clicking inside the user profile
document.querySelector('.user-profile').addEventListener('click', function (e) {
  e.stopPropagation();
});

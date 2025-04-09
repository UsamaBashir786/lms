    // Form validation code
    document.getElementById("contactForm").addEventListener("submit", function(event) {
      event.preventDefault(); // Prevent form submission to check validation

      // Get form values
      var name = document.getElementById("name").value.trim();
      var email = document.getElementById("email").value.trim();
      var message = document.getElementById("message").value.trim();

      // Validate Name
      if (name === "") {
        alert("Full Name is required");
        return;
      }

      // Validate Email
      var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      if (!emailPattern.test(email)) {
        alert("Please enter a valid email address");
        return;
      }

      // Validate Message
      if (message === "") {
        alert("Message is required");
        return;
      }

      // If all validations pass, submit the form
      alert("Message sent successfully!");
      this.submit();
    });
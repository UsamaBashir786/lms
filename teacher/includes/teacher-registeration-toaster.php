<script>
  // Check if there is a message set from PHP
  <?php if (isset($message)) { ?>
    var message = "<?php echo $message['text']; ?>";
    var type = "<?php echo $message['type']; ?>";

    // Create the toast container if not already present
    var toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      document.body.appendChild(toastContainer);
    }

    // Create the toast div
    var toast = document.createElement("div");
    toast.className = "toast " + type;

    // Add close button
    var closeButton = document.createElement("button");
    closeButton.innerHTML = "&times;";
    closeButton.onclick = function() {
      toast.classList.remove('show');
      setTimeout(function() {
        toast.remove();
      }, 400); // Wait for the animation to finish
    };

    toast.innerHTML = message;
    toast.appendChild(closeButton);

    // Append to the container
    toastContainer.appendChild(toast);

    // Show the toaster with animation
    setTimeout(function() {
      toast.classList.add('show');
    }, 10); // Small delay to trigger CSS animation

    // Automatically hide the toast after 5 seconds
    setTimeout(function() {
      toast.classList.remove('show');
      setTimeout(function() {
        toast.remove();
      }, 400); // Wait for the animation to finish
    }, 5000); // Display for 5 seconds
  <?php } ?>
</script>
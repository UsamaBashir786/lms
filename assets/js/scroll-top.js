 // Get the button
 const scrollToTopBtn = document.getElementById("scrollToTop");

 // Show the button when the user scrolls down 100px
 window.onscroll = function() {
   if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
     scrollToTopBtn.style.display = "block";
   } else {
     scrollToTopBtn.style.display = "none";
   }
 };

 // Scroll to the top when the button is clicked
 scrollToTopBtn.addEventListener("click", function() {
   window.scrollTo({
     top: 0,
     behavior: "smooth"
   });
 });
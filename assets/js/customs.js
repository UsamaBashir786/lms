// famous-categorie
$('.famous-categorie .owl-carousel').owlCarousel({
  loop: true,
  margin: 20,
  dots: true,
  nav: false,
  responsiveClass: true,
  responsive: {
    0: {
      items: 1,
    },
    600: {
      items: 2,
    },
    767: {
      items: 3,
    },
    1200: {
      items: 4,
    }
  }
})


// side bar js
              // JavaScript for toggling sidebar
              const toggleButton = document.getElementById("sidebar-toggle");
              const sidebar = document.getElementById("sidebar");
              const closeButton = document.getElementById("sidebar-close");
              
              // Toggle sidebar when the sidebar toggler button is clicked
              toggleButton.addEventListener("click", () => {
                  sidebar.classList.toggle("active");
              });
              
              // Close sidebar when the close button is clicked
              closeButton.addEventListener("click", () => {
                  sidebar.classList.remove("active");
              });
                        
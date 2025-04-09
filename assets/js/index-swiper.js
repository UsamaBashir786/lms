var swiper = new Swiper(".mySwiper", {
  pagination: {
    el: ".swiper-pagination",
  },
  loop: true, // Enable looping
  autoplay: {
    delay: 2000, // Set autoplay delay (optional)
    disableOnInteraction: false, // Continue autoplay even after user interaction
  },
});
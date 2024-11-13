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
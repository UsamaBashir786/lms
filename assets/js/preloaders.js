        // Hide preloader after 3 seconds
        window.addEventListener('load', () => {
          setTimeout(() => {
              document.getElementById('preloader').style.display = 'none';
              document.getElementById('main-content').style.display = 'block';
          }, 2000);
      });
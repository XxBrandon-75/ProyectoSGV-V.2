  document.addEventListener('DOMContentLoaded', function() {
    const slide = document.querySelector('.carousel-slide');
    const images = document.querySelectorAll('.carousel-slide img');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');
    let counter = 0;
    const size = images[0].clientWidth;

    nextBtn.addEventListener('click', () => {
      if (counter >= images.length - 1) return;
      counter++;
      slide.style.transform = 'translateX(' + (-size * counter) + 'px)';
    });

    prevBtn.addEventListener('click', () => {
      if (counter <= 0) return;
      counter--;
      slide.style.transform = 'translateX(' + (-size * counter) + 'px)';
    });
  });
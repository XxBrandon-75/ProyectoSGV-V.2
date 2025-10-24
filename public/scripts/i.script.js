document.addEventListener('DOMContentLoaded', function() {
    const slide = document.querySelector('.carousel-slide');
    const images = document.querySelectorAll('.carousel-slide img');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');

    // 1. Verificación de Elementos (Importante para evitar el error de 'null')
    if (!slide || images.length === 0 || !prevBtn || !nextBtn) {
        console.warn("Carrusel no encontrado o incompleto. El script no se ejecutará.");
        return; // Detiene la ejecución si falta algún elemento
    }

    let counter = 0;
    const intervalTime = 5000; // Avance automático cada 5 segundos (5000ms)
    let autoSlideInterval;

    // --- Funciones de Desplazamiento ---

    // Obtiene el ancho actual de una sola imagen (CLAVE para la responsividad)
    const getSize = () => images[0].clientWidth;

    // Aplica la transformación CSS para mover el carrusel
    const applyTransform = () => {
        const size = getSize();
        slide.style.transition = 'transform 0.4s ease-in-out';
        slide.style.transform = 'translateX(' + (-size * counter) + 'px)';
    };

    // Avanza una diapositiva
    const nextSlide = () => {
        counter++;
        if (counter >= images.length) {
            counter = 0; // Vuelve al inicio
        }
        applyTransform();
    };

    // Retrocede una diapositiva
    const prevSlide = () => {
        counter--;
        if (counter < 0) {
            counter = images.length - 1; // Va al final
        }
        applyTransform();
    };

    // --- Funciones de Control del Temporizador ---

    // Inicia el avance automático
    const startAutoSlide = () => {
        // Limpiamos cualquier temporizador anterior para evitar duplicados
        clearInterval(autoSlideInterval); 
        autoSlideInterval = setInterval(nextSlide, intervalTime);
    };

    // Reinicia el temporizador (llamado después de un clic manual)
    const resetAutoSlide = () => {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    };

    // --- Event Listeners ---

    // Inicializar la posición y empezar el auto-avance
    applyTransform();
    startAutoSlide();

    // Eventos de botones
    nextBtn.addEventListener('click', () => {
        nextSlide();
        resetAutoSlide(); // Reinicia el temporizador después del clic
    });

    prevBtn.addEventListener('click', () => {
        prevSlide();
        resetAutoSlide(); // Reinicia el temporizador después del clic
    });

    // CLAVE: Recalcular la posición al redimensionar la ventana (Responsividad)
    window.addEventListener('resize', () => {
        slide.style.transition = 'none'; // Desactiva la transición temporalmente
        applyTransform();
    });

    // Opcional: Detener el carrusel al pasar el ratón por encima (UX)
    slide.parentNode.addEventListener('mouseenter', () => {
        clearInterval(autoSlideInterval);
    });
    slide.parentNode.addEventListener('mouseleave', () => {
        startAutoSlide();
    });

});

// Animación "fade-in" al hacer scroll
document.addEventListener("scroll", () => {
  const fadeElements = document.querySelectorAll(".fade-in");
  const triggerBottom = window.innerHeight * 0.9;

  fadeElements.forEach((el) => {
    const top = el.getBoundingClientRect().top;
    if (top < triggerBottom) {
      el.classList.add("visible");
    }
  });
});

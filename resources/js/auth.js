/**
 * Funcionalidad específica para las páginas de autenticación (login, register)
 */

// Carrusel automático
function initCarousel() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    
    if (slides.length === 0) return;
    
    function nextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }
    
    setInterval(nextSlide, 5000); // Cambia cada 5 segundos
}

// Función para cerrar el modal de error
function closeModal() {
    const modal = document.getElementById('errorModal');
    if (modal) {
        modal.style.animation = 'modalFadeOut 0.3s ease-out';
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

window.closeModal = closeModal;

// Contador regresivo para el bloqueo
function initLockoutCountdown(remainingSeconds) {
    if (!remainingSeconds) return;
    
    let timeLeft = remainingSeconds;
    const minutesEl = document.getElementById('minutes');
    const secondsEl = document.getElementById('seconds');
    
    if (!minutesEl || !secondsEl) return;
    
    function updateCountdown() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        minutesEl.textContent = minutes;
        secondsEl.textContent = seconds < 10 ? '0' + seconds : seconds;
        
        if (timeLeft <= 0) {
            // Recargar la página cuando termine el tiempo
            window.location.reload();
        } else {
            timeLeft--;
            setTimeout(updateCountdown, 1000);
        }
    }
    
    updateCountdown();
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initCarousel();
    
    // Cerrar modal al hacer clic fuera de él
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('errorModal');
        if (modal && event.target === modal) {
            closeModal();
        }
    });

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
    
    // Inicializar contador si existe el elemento
    const lockoutSeconds = window.lockoutSeconds || null;
    if (lockoutSeconds) {
        initLockoutCountdown(lockoutSeconds);
    }
});

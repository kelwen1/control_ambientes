/**
 * Funcionalidad de navegación (transiciones, etc.)
 */

export function initNavigationTransitions() {
    // Animación suave en links de navegación
    document.querySelectorAll('a[href^="/"]:not([href*="#"])').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#')) {
                document.body.style.opacity = '0.7';
                document.body.style.transition = 'opacity 0.2s';
            }
        });
    });
}

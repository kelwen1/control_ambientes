/**
 * Funcionalidad de búsqueda con debounce
 */

export function initSearchInput(inputId, formId = null) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;

    // Limpiar búsqueda cuando el input esté vacío
    searchInput.addEventListener('input', function() {
        if (this.value === '') {
            const url = new URL(window.location.href);
            if (url.searchParams.has('search')) {
                url.searchParams.delete('search');
                window.location.href = url.toString();
            }
        }
    });

    // Permitir búsqueda con Enter
    searchInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const form = formId ? document.getElementById(formId) : this.closest('form');
            if (form) {
                form.submit();
            }
        }
    });

    // Limpiar al pegar texto
    searchInput.addEventListener('paste', function(e) {
        setTimeout(() => {
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            if (paste.trim() !== '') {
                const form = formId ? document.getElementById(formId) : this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        }, 10);
    });
}

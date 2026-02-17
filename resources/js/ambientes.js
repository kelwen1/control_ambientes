/**
 * Funcionalidad específica para la página de ambientes
 */
import { openDeleteModal, closeDeleteModal } from './modals';
import { initSearchInput } from './search';

// Función específica para ambientes (eliminación de reservas)
window.openDeleteModal = function(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');

    if (form) {
        // Usar la URL base generada por Laravel (soporta subcarpetas)
        const baseUrl = form.dataset.baseUrl || (window.location.origin + '/reservas');
        form.action = `${baseUrl}/${id}`;
    }

    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
};

window.closeDeleteModal = function() {
    closeDeleteModal('deleteModal');
};

// Búsqueda solo numérica para ambientes
function initNumericSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    // Validar que solo se ingresen números
    searchInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value === '') {
            const url = new URL(window.location.href);
            if (url.searchParams.has('search')) {
                url.searchParams.delete('search');
                window.location.href = url.toString();
            }
        }
    });

    // Prevenir que se ingresen caracteres no numéricos al pegar
    searchInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const numbersOnly = paste.replace(/[^0-9]/g, '');
        this.value = numbersOnly;
    });

    // Permitir búsqueda con Enter
    searchInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('searchForm')?.submit();
        }
    });

    // Prevenir que se ingresen caracteres no numéricos directamente
    searchInput.addEventListener('keydown', function(event) {
        // Permitir teclas especiales: backspace, delete, tab, escape, enter, etc.
        if ([8, 9, 27, 13, 46, 110, 190].indexOf(event.keyCode) !== -1 ||
            // Permitir Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (event.keyCode === 65 && event.ctrlKey === true) ||
            (event.keyCode === 67 && event.ctrlKey === true) ||
            (event.keyCode === 86 && event.ctrlKey === true) ||
            (event.keyCode === 88 && event.ctrlKey === true) ||
            // Permitir home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
            return;
        }
        // Asegurar que es un número y prevenir el punto decimal
        if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
            event.preventDefault();
        }
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initNumericSearch();
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
});

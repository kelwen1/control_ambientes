/**
 * Funcionalidad específica para la página de fichas
 */
import { openDeleteModal, closeDeleteModal } from './modals';
import { initSearchInput } from './search';

// Hacer funciones disponibles globalmente para onclick
window.openDeleteModal = function(id, fichaNum) {
    const baseUrl = window.location.origin + '/fichas';
    openDeleteModal(id, fichaNum, baseUrl, 'deleteModal', 'fichaNum');
};

window.closeDeleteModal = function() {
    closeDeleteModal('deleteModal');
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initSearchInput('searchInput', 'searchForm');
});

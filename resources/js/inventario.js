/**
 * Funcionalidad específica para la página de inventario
 */
import { openDeleteModal, closeDeleteModal } from './modals';
import { initSearchInput } from './search';

// Función específica para inventario
window.openDeleteModal = function(id, ambienteName) {
    const baseUrl = window.location.origin + '/inventario';
    openDeleteModal(id, ambienteName, baseUrl, 'deleteModal', 'ambienteName');
};

window.closeDeleteModal = function() {
    closeDeleteModal('deleteModal');
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initSearchInput('searchInput', 'searchForm');
});

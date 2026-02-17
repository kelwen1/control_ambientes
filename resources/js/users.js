/**
 * Funcionalidad específica para la página de usuarios
 */
import { openDeleteModal, closeDeleteModal } from './modals';
import { initSearchInput } from './search';

// Función específica para usuarios
window.openDeleteModal = function(id, userName) {
    const baseUrl = window.location.origin + '/users';
    openDeleteModal(id, userName, baseUrl, 'deleteModal', 'userName');
};

window.closeDeleteModal = function() {
    closeDeleteModal('deleteModal');
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initSearchInput('searchInput', 'searchForm');
});

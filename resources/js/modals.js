/**
 * Funciones para manejar modales de confirmación
 */

// Modal de logout
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.animation = 'modalFadeOut 0.3s ease-out';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.animation = '';
        }, 300);
    }
}

function confirmLogout() {
    const form = document.getElementById('logoutForm');
    if (form) {
        form.submit();
    }
}

// Hacer funciones disponibles globalmente INMEDIATAMENTE (antes de que se carguen otros módulos)
window.showLogoutModal = showLogoutModal;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;

// Exportar también para módulos ES6
export { showLogoutModal, closeLogoutModal, confirmLogout };

// Modal de eliminación genérico
export function openDeleteModal(id, name, baseUrl, modalId = 'deleteModal', nameElementId = null) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Si hay un elemento para mostrar el nombre
    if (nameElementId) {
        const nameElement = document.getElementById(nameElementId);
        if (nameElement) {
            nameElement.textContent = name;
        }
    }
    
    // Configurar el formulario de eliminación
    const deleteForm = document.getElementById('deleteForm');
    if (deleteForm) {
        deleteForm.action = `${baseUrl}/${id}`;
    }
}

export function closeDeleteModal(modalId = 'deleteModal') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Modal de edición (ajustes)
export function openEditModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

export function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.animation = 'modalFadeOut 0.3s ease-out';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.animation = '';
        }, 300);
    }
}

// Inicializar event listeners para modales
export function initModalListeners() {
    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal-overlay:not(.hidden)');
        modals.forEach(modal => {
            if (event.target === modal) {
                if (modal.id === 'logoutModal') {
                    closeLogoutModal();
                } else if (modal.id === 'deleteModal') {
                    closeDeleteModal();
                } else {
                    closeModal(modal.id);
                }
            }
        });
    });

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const visibleModals = document.querySelectorAll('.modal-overlay:not(.hidden)');
            visibleModals.forEach(modal => {
                if (modal.id === 'logoutModal') {
                    closeLogoutModal();
                } else if (modal.id === 'deleteModal') {
                    closeDeleteModal();
                } else {
                    closeModal(modal.id);
                }
            });
        }
    });
}

// Hacer funciones disponibles globalmente para onclick en HTML (ya están arriba, pero por si acaso)
if (!window.showLogoutModal) window.showLogoutModal = showLogoutModal;
if (!window.closeLogoutModal) window.closeLogoutModal = closeLogoutModal;
if (!window.confirmLogout) window.confirmLogout = confirmLogout;
window.openDeleteModal = openDeleteModal;
window.closeDeleteModal = closeDeleteModal;
window.openEditModal = openEditModal;
window.closeModal = closeModal;

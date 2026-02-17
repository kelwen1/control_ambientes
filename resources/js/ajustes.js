/**
 * Funcionalidad específica para la página de ajustes
 */
import { openEditModal, closeModal } from './modals';
import { initPasswordConfirmation } from './forms';

// Función para abrir modales de edición
window.openEditModal = function(field) {
    const modalMap = {
        'nombre': 'editNombreModal',
        'apellido': 'editApellidoModal',
        'telefono': 'editTelefonoModal',
        'correo': 'editCorreoModal',
        'usuario': 'editUsuarioModal',
        'contraseña': 'editContraseñaModal'
    };
    
    const modalId = modalMap[field];
    if (modalId) {
        openEditModal(modalId);
    }
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        // Limpiar formulario
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initPasswordConfirmation();
    
    // Cerrar modales al hacer clic fuera
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            const modal = event.target.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }
    });
});

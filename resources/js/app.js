import './bootstrap';
import { initModalListeners } from './modals';
import { initFormLoadingStates, initPasswordConfirmation } from './forms';
import { initNavigationTransitions } from './navigation';

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initModalListeners();
    initFormLoadingStates();
    initPasswordConfirmation();
    initNavigationTransitions();
});

/**
 * Funcionalidad para formularios (loading states, validaciones)
 */

export function initFormLoadingStates() {
    // Loading states en botones y enlaces
    document.querySelectorAll('form, a.button-loading, button.button-loading').forEach(element => {
        element.addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]') || this;
            if (button) {
                button.classList.add('loading');
                setTimeout(() => {
                    if (button.parentElement) {
                        button.classList.remove('loading');
                    }
                }, 3000);
            }
        });
        
        if (element.tagName === 'A' && element.classList.contains('button-loading')) {
            element.addEventListener('click', function(e) {
                if (!this.href.includes('#')) {
                    this.classList.add('loading');
                }
            });
        }
    });
}

export function initPasswordConfirmation() {
    const form = document.getElementById('contraseñaForm');
    if (!form) return;

    const nuevaInput = document.getElementById('contraseña_nueva');
    const confirmacionInput = document.getElementById('contraseña_nueva_confirmacion');
    const matchError = document.getElementById('passwordMatch');

    if (!nuevaInput || !confirmacionInput || !matchError) return;

    form.addEventListener('submit', function(e) {
        const nueva = nuevaInput.value;
        const confirmacion = confirmacionInput.value;

        if (nueva !== confirmacion) {
            e.preventDefault();
            matchError.classList.remove('hidden');
            matchError.textContent = 'Las contraseñas no coinciden.';
            return false;
        }

        matchError.classList.add('hidden');
        return true;
    });

    confirmacionInput.addEventListener('input', function() {
        const nueva = nuevaInput.value;
        const confirmacion = this.value;

        if (confirmacion && nueva !== confirmacion) {
            matchError.classList.remove('hidden');
            matchError.textContent = 'Las contraseñas no coinciden.';
        } else {
            matchError.classList.add('hidden');
        }
    });
}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestión de Ambientes') - SENA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logos/logo_sena.png') }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sena-green': '#39B54A',
                    }
                }
            }
        }
    </script>
    
    <!-- JavaScript inline consolidado -->
    <script>
        // Funciones de logout - disponibles inmediatamente
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

        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            if (!input) return;
            var isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            var open = btn.querySelector('.eye-open');
            var closed = btn.querySelector('.eye-closed');
            if (open && closed) {
                open.classList.toggle('hidden', isPass);
                closed.classList.toggle('hidden', !isPass);
            }
            btn.setAttribute('aria-label', isPass ? 'Ocultar contraseña' : 'Mostrar contraseña');
        }
        
        // Funciones de modales genéricos
        function openDeleteModal(id, name, baseUrl, modalId = 'deleteModal', nameElementId = null) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Mostrar nombre si se proporciona
            if (nameElementId) {
                const nameElement = document.getElementById(nameElementId);
                if (nameElement) {
                    nameElement.textContent = name;
                }
            }

            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                // Priorizar la URL base que viene como parámetro,
                // pero si no viene (por ejemplo, se llamó solo con el ID),
                // usar el atributo data-base-url del formulario.
                let finalBaseUrl = baseUrl;

                if (!finalBaseUrl) {
                    finalBaseUrl = deleteForm.dataset.baseUrl || deleteForm.getAttribute('data-base-url') || '';
                }

                if (finalBaseUrl) {
                    deleteForm.action = `${finalBaseUrl}/${id}`;
                } else {
                    console.error('No se pudo determinar la URL base para la eliminación (baseUrl indefinida).');
                }
            }
        }
        
        function closeDeleteModal(modalId = 'deleteModal') {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
        
        function openEditModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        
        function closeModal(modalId) {
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
        
        // Hacer funciones disponibles globalmente
        window.showLogoutModal = showLogoutModal;
        window.closeLogoutModal = closeLogoutModal;
        window.confirmLogout = confirmLogout;
        window.openDeleteModal = openDeleteModal;
        window.closeDeleteModal = closeDeleteModal;
        window.openEditModal = openEditModal;
        window.closeModal = closeModal;
        
        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Validación de contraseña
            const contraseñaForm = document.getElementById('contraseñaForm');
            if (contraseñaForm) {
                const nuevaInput = document.getElementById('contraseña_nueva');
                const confirmacionInput = document.getElementById('contraseña_nueva_confirmacion');
                const matchError = document.getElementById('passwordMatch');

                if (nuevaInput && confirmacionInput && matchError) {
                    contraseñaForm.addEventListener('submit', function(e) {
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
            }
            
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
        });
    </script>
    <style>
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .modal-container {
            animation: modalFadeIn 0.3s ease-out;
        }
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        @keyframes modalFadeOut {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            to {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Animaciones de transición de página */
        @keyframes pageSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        main {
            animation: pageSlideIn 0.4s ease-out;
        }
        
        /* Loading states para botones */
        .button-loading {
            position: relative;
            overflow: hidden;
        }
        
        .button-loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .button-loading:hover::before {
            left: 100%;
        }
        
        .button-loading.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .button-loading.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    @include('components.navbar')

    <!-- Contenido principal -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full">
        @yield('content')
    </main>

    @include('components.footer')

    <!-- Modal de Confirmación de Cierre de Sesión -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <!-- Icono de Advertencia -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-4">
                    <svg class="h-10 w-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                
                <!-- Título -->
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Cerrar Sesión?</h3>
                
                <!-- Mensaje -->
                <p class="text-gray-600 mb-6">
                    ¿Estás seguro de que deseas cerrar tu sesión? Serás redirigido a la página de inicio.
                </p>
                
                <!-- Botones -->
                <div class="flex gap-3 sm:gap-4">
                    <button onclick="closeLogoutModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                        Cancelar
                    </button>
                    <button onclick="confirmLogout()" 
                            class="flex-1 bg-red-600 text-white py-3 rounded-lg font-semibold text-base hover:bg-red-700 transition-colors shadow-lg transform hover:scale-105">
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var tabId = sessionStorage.getItem('_tabId');
        if (!tabId) {
            tabId = 't_' + Math.random().toString(36).slice(2) + Date.now();
            sessionStorage.setItem('_tabId', tabId);
        }
        var registerUrl = '{{ route("tab.register") }}';
        var unregisterUrl = '{{ route("tab.unregister") }}';
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(registerUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ tab_id: tabId })
        }).catch(function() {});

        sessionStorage.removeItem('_tabNav');

        document.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (a && a.href && a.origin === location.origin && a.pathname.startsWith('/')) {
                sessionStorage.setItem('_tabNav', '1');
            }
        }, true);
        document.addEventListener('submit', function() {
            sessionStorage.setItem('_tabNav', '1');
        }, true);

        window.addEventListener('pagehide', function() {
            if (sessionStorage.getItem('_tabNav')) return;
            var fd = new FormData();
            fd.append('_token', token);
            fd.append('tab_id', tabId);
            navigator.sendBeacon(unregisterUrl, fd);
        });
    })();
    </script>
</body>
</html>


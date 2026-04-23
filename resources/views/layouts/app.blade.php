<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestión de Ambientes') - SENA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logos/logo_sena.png') }}">
    
    <!-- Google Fonts - Plus Jakarta Sans (moderna, legible) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500&display=swap" rel="stylesheet">
    
    <!-- CSS Premium -->
    <link rel="stylesheet" href="{{ asset('css/app-premium.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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

        function isDesktopViewport() {
            return window.innerWidth >= 1024;
        }

        function setSidebarDesktopState() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (!sidebar || !overlay) return;

            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
            if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'false');
            if (document.activeElement === sidebarToggle) sidebarToggle.blur();
        }

        function setSidebarMobileState(isOpen) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (!sidebar || !overlay) return;

            if (isOpen) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'true');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
                if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'false');
                if (document.activeElement === sidebarToggle) sidebarToggle.blur();
            }
        }

        // Menú: sidebar fijo en desktop y desplegable en móvil
        function toggleSidebarMobile(open) {
            if (isDesktopViewport()) {
                setSidebarDesktopState();
                return;
            }

            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            const isOpen = open ?? !sidebar.classList.contains('translate-x-0');
            setSidebarMobileState(isOpen);
        }
        window.toggleSidebarMobile = toggleSidebarMobile;

        function syncSidebarByViewport() {
            if (isDesktopViewport()) {
                setSidebarDesktopState();
            } else {
                // Al entrar en móvil se fuerza cerrado para evitar quedar "pegado" desde desktop.
                setSidebarMobileState(false);
            }
        }

        function makeTablesResponsive() {
            document.querySelectorAll('main table').forEach(function (table) {
                if (table.dataset.mobileResponsive === '1') return;
                const wrapper = document.createElement('div');
                wrapper.className = 'w-full overflow-x-auto';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
                table.classList.add('min-w-[720px]');
                table.dataset.mobileResponsive = '1';
            });
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
            // Sincroniza estado al cargar (evita heredar estados cruzados entre viewport móvil/escritorio).
            syncSidebarByViewport();
            makeTablesResponsive();

            // Toggle menú móvil
            document.getElementById('sidebarToggle')?.addEventListener('click', function() {
                toggleSidebarMobile(); // Toggle: abre si está cerrado, cierra si está abierto
            });
            // Cerrar menú al hacer clic en un enlace (móvil)
            document.querySelectorAll('#sidebar a.sidebar-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) toggleSidebarMobile(false);
                });
            });

            // Cambio de viewport: sincroniza de inmediato el sidebar y evita foco/overlay pegados.
            let resizeSyncTimer = null;
            window.addEventListener('resize', function() {
                if (resizeSyncTimer) clearTimeout(resizeSyncTimer);
                resizeSyncTimer = setTimeout(function () {
                    syncSidebarByViewport();
                    makeTablesResponsive();
                }, 80);
            });
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
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
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
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12);
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
        /* Sidebar fijo en escritorio; hamburguesa solo para móvil */
    </style>
</head>
@php
    $globalFlash = [
        'show' => false,
        'type' => 'success',
        'title' => '',
        'lines' => [],
    ];
    if (isset($errors) && $errors->any()) {
        $globalFlash['show'] = true;
        $globalFlash['type'] = 'error';
        $globalFlash['title'] = 'Revisa la información';
        $globalFlash['lines'] = $errors->all();
    } elseif (session()->has('error')) {
        $globalFlash['show'] = true;
        $globalFlash['type'] = 'error';
        $globalFlash['title'] = 'No se pudo completar';
        $globalFlash['lines'] = \Illuminate\Support\Arr::wrap(session('error'));
    } elseif (session()->has('warning')) {
        $globalFlash['show'] = true;
        $globalFlash['type'] = 'warning';
        $globalFlash['title'] = 'Aviso';
        $globalFlash['lines'] = \Illuminate\Support\Arr::wrap(session('warning'));
    } elseif (session()->has('success')) {
        $globalFlash['show'] = true;
        $globalFlash['type'] = 'success';
        $globalFlash['title'] = 'Operación exitosa';
        $globalFlash['lines'] = \Illuminate\Support\Arr::wrap(session('success'));
    } elseif (session()->has('status')) {
        $globalFlash['show'] = true;
        $globalFlash['type'] = 'success';
        $globalFlash['title'] = 'Listo';
        $globalFlash['lines'] = \Illuminate\Support\Arr::wrap(session('status'));
    }
@endphp
<body class="bg-gray-50 min-h-screen min-w-0 flex font-sans antialiased overflow-x-hidden"@if (!empty($globalFlash['show'])) data-global-flash="1"@endif>
    @include('components.sidebar')

    <div id="mainContent" class="flex-1 flex flex-col min-w-0 max-w-full">
        @include('components.navbar')

        <!-- Contenido principal -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full min-w-0">
            @yield('content')
        </main>

        @include('components.footer')
    </div>

    <!-- Modal de Confirmación de Cierre de Sesión -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay modal-overlay-premium">
        <div class="modal-container glass-container glass-premium rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
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
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-xl font-semibold text-base hover:bg-gray-400 transition-all duration-200 shadow-md">
                        Cancelar
                    </button>
                    <button onclick="confirmLogout()" 
                            class="flex-1 bg-red-600 text-white py-3 rounded-xl font-semibold text-base hover:bg-red-700 hover:shadow-lg transition-all duration-200 shadow-md">
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('components.flash-modal', ['globalFlash' => $globalFlash])
    @include('components.app-message-modal')

</body>
</html>


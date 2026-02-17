<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gestión de Ambientes</title>
    
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
    <style>
        .carousel-container {
            position: relative;
            width: 100%;
            height: 100vh;
            min-height: 600px;
            overflow: hidden;
        }
        
        @media (max-width: 640px) {
            .carousel-container {
                min-height: 100vh;
                height: auto;
            }
        }
        .carousel-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }
        .carousel-slide.active {
            opacity: 1;
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
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
    </style>
</head>
<body class="overflow-hidden">
    <!-- Carrusel de imágenes -->
    <div class="carousel-container">
        <div class="carousel-slide active" style="background-image: url('{{ asset('images/posters/carrusel_1.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_2.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_3.jpeg') }}');"></div>
    </div>

    <!-- Enlace al Manual de Usuario -->
    <div class="absolute top-4 right-4 z-20">
        <a href="{{ route('manual.usuario') }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white/90 text-[#39B54A] text-xs sm:text-sm font-semibold shadow hover:bg-white hover:text-[#2d8f3a] transition-colors">
            Manual de usuario
        </a>
    </div>

    <!-- Contenedor de login -->
    <div class="absolute inset-0 flex items-center justify-center p-4 py-8">
        <div class="glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center mb-6 sm:mb-8">
                <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-12 sm:h-16 mx-auto mb-3 sm:mb-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">Iniciar Sesión</h2>
                <p class="text-sm sm:text-base text-gray-600">Ingresa tus credenciales para continuar</p>
            </div>

            @if (isset($isLocked) && $isLocked)
                <div class="mb-4 p-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-red-800 font-semibold">
                                Has alcanzado el límite de intentos.
                            </p>
                        </div>
                        <div class="text-sm text-red-700 font-bold" id="countdown">
                            <span id="minutes">0</span>:<span id="seconds">00</span>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" @if (isset($isLocked) && $isLocked) onsubmit="event.preventDefault(); return false;" @endif>
                @csrf

                <div class="mb-4 sm:mb-6">
                    <label for="user" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Usuario</label>
                    <input type="text" 
                           id="user" 
                           name="user" 
                           value="{{ old('user') }}"
                           required
                           @if (isset($isLocked) && $isLocked) disabled @endif
                           class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base disabled:opacity-50 disabled:cursor-not-allowed"
                           placeholder="Ingresa tu usuario">
                </div>

                <div class="mb-4 sm:mb-6">
                    <label for="contraseña" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Contraseña</label>
                    <div class="relative">
                        <input type="password" 
                               id="contraseña" 
                               name="contraseña" 
                               required
                               @if (isset($isLocked) && $isLocked) disabled @endif
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 pr-10 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base disabled:opacity-50 disabled:cursor-not-allowed"
                               placeholder="Ingresa tu contraseña">
                        <button type="button" 
                                onclick="togglePassword('contraseña', this)" 
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 focus:outline-none disabled:opacity-50"
                                tabindex="-1"
                                aria-label="Mostrar contraseña">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="mb-4 sm:mb-6 flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember"
                           @if (isset($isLocked) && $isLocked) disabled @endif
                           class="w-4 h-4 text-[#39B54A] border-gray-300 rounded focus:ring-[#39B54A] disabled:opacity-50 disabled:cursor-not-allowed">
                    <label for="remember" class="ml-2 text-sm sm:text-base text-gray-700">Recordarme</label>
                </div>

                <button type="submit" 
                        @if (isset($isLocked) && $isLocked) disabled @endif
                        class="w-full bg-[#39B54A] text-white py-2.5 sm:py-3 rounded-lg font-semibold text-base sm:text-lg hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    Ingresar
                </button>
            </form>

            {{-- Enlace de registro comentado temporalmente --}}
            {{-- <div class="mt-4 sm:mt-6 text-center">
                <a href="{{ route('register') }}" class="text-sm sm:text-base text-[#39B54A] hover:underline font-medium">
                    ¿No tienes cuenta? Regístrate aquí
                </a>
            </div> --}}
        </div>
    </div>

    <!-- Modal de Error de Credenciales -->
    @if (session('show_modal'))
    <div id="errorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <!-- Icono de Error -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                
                <!-- Título -->
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Credenciales Incorrectas</h3>
                
                <!-- Mensaje -->
                <p class="text-gray-600 mb-6">
                    Las credenciales proporcionadas no son válidas. Por favor, verifica tu usuario y contraseña e intenta nuevamente.
                </p>
                
                @php
                    $attemptsToShow = session('remaining_attempts', 0);
                @endphp
                @if ($attemptsToShow > 0)
                <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <span class="font-semibold">Te quedan {{ $attemptsToShow }} intento(s) restante(s).</span>
                    </p>
                </div>
                @endif
                
                <!-- Botón de Cerrar -->
                <button onclick="closeModal()" 
                        class="w-full bg-[#39B54A] text-white py-3 rounded-lg font-semibold text-base hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                    Entendido
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Carrusel automático
        function initCarousel() {
            let currentSlide = 0;
            const slides = document.querySelectorAll('.carousel-slide');
            
            if (slides.length === 0) return;
            
            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }
            
            setInterval(nextSlide, 5000);
        }

        // Función para cerrar el modal de error
        function closeModal() {
            const modal = document.getElementById('errorModal');
            if (modal) {
                modal.style.animation = 'modalFadeOut 0.3s ease-out';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }

        window.closeModal = closeModal;

        // Contador regresivo para el bloqueo
        function initLockoutCountdown(remainingSeconds) {
            if (!remainingSeconds) return;
            
            let timeLeft = remainingSeconds;
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');
            
            if (!minutesEl || !secondsEl) return;
            
            function updateCountdown() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                minutesEl.textContent = minutes;
                secondsEl.textContent = seconds < 10 ? '0' + seconds : seconds;
                
                if (timeLeft <= 0) {
                    window.location.reload();
                } else {
                    timeLeft--;
                    setTimeout(updateCountdown, 1000);
                }
            }
            
            updateCountdown();
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

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            initCarousel();
            
            document.addEventListener('click', function(event) {
                const modal = document.getElementById('errorModal');
                if (modal && event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
            
            const lockoutSeconds = window.lockoutSeconds || null;
            if (lockoutSeconds) {
                initLockoutCountdown(lockoutSeconds);
            }
        });
    </script>
    @if (isset($isLocked) && $isLocked && isset($remainingLockoutSeconds))
    <script>
        window.lockoutSeconds = {{ $remainingLockoutSeconds }};
    </script>
    @endif
    <style>
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
    </style>
</body>
</html>


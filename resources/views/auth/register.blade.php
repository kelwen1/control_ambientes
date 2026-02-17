<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Gestión de Ambientes</title>
    
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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="overflow-y-auto">
    <!-- Carrusel de imágenes -->
    <div class="carousel-container fixed inset-0 -z-10">
        <div class="carousel-slide active" style="background-image: url('{{ asset('images/posters/carrusel_1.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_2.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_3.jpeg') }}');"></div>
    </div>

    <!-- Contenedor de registro -->
    <div class="min-h-screen flex items-center justify-center p-4 py-8 sm:py-12">
        <div class="glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-2xl">
            <div class="text-center mb-6 sm:mb-8">
                <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-12 sm:h-16 mx-auto mb-3 sm:mb-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">Registro de Usuario</h2>
                <p class="text-sm sm:text-base text-gray-600">Crea tu cuenta de administración</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- ID Cédula -->
                    <div>
                        <label for="id_cedula" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Cédula *</label>
                        <input type="text" 
                               id="id_cedula" 
                               name="id_cedula" 
                               value="{{ old('id_cedula') }}"
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Número de cédula">
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Nombre *</label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre') }}"
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Primer nombre">
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label for="apellido" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Apellido *</label>
                        <input type="text" 
                               id="apellido" 
                               name="apellido" 
                               value="{{ old('apellido') }}"
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Apellidos">
                    </div>

                    <!-- Correo -->
                    <div>
                        <label for="correo" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Correo *</label>
                        <input type="email" 
                               id="correo" 
                               name="correo" 
                               value="{{ old('correo') }}"
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="correo@ejemplo.com">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Teléfono</label>
                        <input type="text" 
                               id="telefono" 
                               name="telefono" 
                               value="{{ old('telefono') }}"
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Número de teléfono">
                    </div>

                    <!-- Usuario -->
                    <div>
                        <label for="user" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Usuario *</label>
                        <input type="text" 
                               id="user" 
                               name="user" 
                               value="{{ old('user') }}"
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Nombre de usuario">
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="contraseña" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Contraseña *</label>
                        <input type="password" 
                               id="contraseña" 
                               name="contraseña" 
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Mínimo 8 caracteres">
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <label for="contraseña_confirmation" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Confirmar Contraseña *</label>
                        <input type="password" 
                               id="contraseña_confirmation" 
                               name="contraseña_confirmation" 
                               required
                               class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                               placeholder="Confirma tu contraseña">
                    </div>

                    <!-- ID Rol -->
                    <div>
                        <label for="id_rol" class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Rol *</label>
                        <select id="id_rol" 
                                name="id_rol" 
                                required
                                class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                            <option value="2" {{ old('id_rol') == 2 ? 'selected' : '' }}>Usuario</option>
                            <option value="1" {{ old('id_rol') == 1 ? 'selected' : '' }}>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <button type="submit" 
                            class="flex-1 bg-[#39B54A] text-white py-2.5 sm:py-3 rounded-lg font-semibold text-base sm:text-lg hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                        Registrarse
                    </button>
                    <a href="{{ route('login') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 py-2.5 sm:py-3 rounded-lg font-semibold text-base sm:text-lg hover:bg-gray-400 transition-colors shadow-lg text-center">
                        Cancelar
                    </a>
                </div>
            </form>

            <div class="mt-4 sm:mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm sm:text-base text-[#39B54A] hover:underline font-medium">
                    ¿Ya tienes cuenta? Inicia sesión aquí
                </a>
            </div>
        </div>
    </div>

    <script>
        // Carrusel automático
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        
        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        
        setInterval(nextSlide, 5000); // Cambia cada 5 segundos
    </script>
</body>
</html>


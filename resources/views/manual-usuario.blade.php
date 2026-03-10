<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - Sistema de Control de Ambientes</title>
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
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
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
        @media (max-width: 640px) {
            .carousel-container {
                height: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 relative min-h-screen">
    <!-- Carrusel de fondo -->
    <div class="carousel-container">
        <div class="carousel-slide active" style="background-image: url('{{ asset('images/posters/carrusel_1.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_2.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_3.jpeg') }}');"></div>
    </div>

    <!-- Contenido principal sobre el carrusel -->
    <div class="relative max-w-5xl mx-auto px-4 py-8">
        <header class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-sena-green mb-1">
                    Manual de Usuario
                </h1>
                <p class="text-sm text-gray-600">
                    Sistema de Control de Ambientes - SENA
                </p>
            </div>
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-sena-green text-white text-sm font-semibold hover:bg-emerald-700 shadow">
                Volver al inicio de sesión
            </a>
        </header>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">1. Descripción general</h2>
            <p class="mb-2 text-sm">
                Este sistema permite administrar de forma centralizada:
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li><span class="font-semibold">Fichas de formación</span>: grupos de aprendices y su programa.</li>
                <li><span class="font-semibold">Ambientes</span>: salones/laboratorios disponibles en el centro.</li>
                <li><span class="font-semibold">Reservas</span>: asignación de ambientes a fichas por día y horario.</li>
                <li><span class="font-semibold">Usuarios</span>: cuentas de acceso (solo administradores).</li>
                <li><span class="font-semibold">Ajustes</span>: datos personales y credenciales del usuario actual.</li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">2. Roles y permisos</h2>
            <p class="text-sm mb-2">Existen tres tipos principales de usuarios:</p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>
                    <span class="font-semibold">Administrador</span>: acceso total al sistema.
                    <ul class="list-disc list-inside ml-5 mt-1">
                        <li>Gestiona usuarios (crear, editar, eliminar).</li>
                        <li>Gestiona fichas, ambientes y reservas.</li>
                        <li>Accede a todos los reportes (exportaciones en PDF).</li>
                    </ul>
                </li>
                <li>
                    <span class="font-semibold">Coordinador</span>: acceso de solo consulta a algunos módulos.
                    <ul class="list-disc list-inside ml-5 mt-1">
                        <li>Puede buscar y visualizar fichas.</li>
                        <li>Puede consultar ambientes y reservas.</li>
                        <li>Puede descargar reportes en PDF.</li>
                        <li>No puede crear/editar/eliminar registros.</li>
                    </ul>
                </li>
                <li>
                    <span class="font-semibold">Usuario</span>: normalmente instructores o personal operativo.
                    <ul class="list-disc list-inside ml-5 mt-1">
                        <li>Puede gestionar fichas y reservas (según permisos).</li>
                        <li>Puede consultar la información actualizada de los ambientes.</li>
                    </ul>
                </li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">3. Inicio de sesión</h2>
            <ol class="list-decimal list-inside space-y-1 text-sm">
                <li>Ingrese a la pantalla de <span class="font-semibold">Iniciar Sesión</span>.</li>
                <li>Digite su <span class="font-semibold">usuario</span> y <span class="font-semibold">contraseña</span>.</li>
                <li>Use el ícono de <span class="font-semibold">ojo</span> para mostrar u ocultar la contraseña.</li>
                <li>Haga clic en <span class="font-semibold">Ingresar</span>.</li>
                <li>Si supera el número de intentos permitidos, el sistema bloqueará temporalmente el acceso y mostrará una cuenta regresiva.</li>
            </ol>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">4. Panel principal (Dashboard)</h2>
            <p class="text-sm mb-2">
                Después de iniciar sesión, el sistema muestra un resumen general del estado de ambientes, reservas y fichas.
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>Accesos rápidos a los módulos principales.</li>
                <li>Indicadores básicos del uso de ambientes y cantidad de registros.</li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">5. Gestión de Fichas</h2>
            <p class="text-sm mb-2">
                Módulo para administrar las fichas de formación (número de ficha, programa, fechas y cantidad de aprendices).
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm mb-2">
                <li><span class="font-semibold">Listado</span>: muestra todas las fichas con paginación.</li>
                <li><span class="font-semibold">Buscar</span>: permite filtrar por número de ficha, programa, fechas o cantidad de aprendices.</li>
                <li><span class="font-semibold">Crear / Editar / Eliminar</span>: disponible para administradores/usuarios con permisos (no para coordinadores).</li>
                <li><span class="font-semibold">Reportes</span>: botón que abre un modal y permite descargar un <span class="font-semibold">PDF</span> con la tabla completa de fichas visibles según el filtro aplicado.</li>
            </ul>
            <p class="text-xs text-gray-500">
                Nota: el número de ficha es el identificador que se comparte con el sistema académico y se utiliza en las reservas.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">6. Gestión de Ambientes y Reservas</h2>
            <p class="text-sm mb-2">
                Permite consultar la ocupación de los ambientes y gestionar las reservas (asignación de fichas a ambientes en días y horarios).
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm mb-2">
                <li><span class="font-semibold">Listado de reservas</span>: muestra ambiente, ficha asignada, estado, día, hora y rango de fechas.</li>
                <li><span class="font-semibold">Buscar por ambiente</span>: ingrese el número del ambiente para ver solo sus reservas.</li>
                <li><span class="font-semibold">Disponibilidad por jornada</span>: vista especial para ver qué ambientes están libres u ocupados por jornada.</li>
                <li><span class="font-semibold">Crear / Editar / Cancelar reservas</span>: disponible solo para usuarios con permisos (no coordinadores).</li>
                <li><span class="font-semibold">Reportes</span>: botón con modal para descargar un <span class="font-semibold">PDF</span> con las reservas listadas.</li>
            </ul>
            <p class="text-xs text-gray-500">
                Sugerencia: antes de crear una reserva, revise la disponibilidad para evitar solapamientos innecesarios.
            </p>
        </section>

        {{-- Sección de inventario eliminada: el módulo ya no está disponible --}}

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">8. Gestión de Usuarios (solo administradores)</h2>
            <p class="text-sm mb-2">
                Permite administrar las cuentas de acceso al sistema.
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm mb-2">
                <li><span class="font-semibold">Crear usuario</span>: registrar nueva cuenta con cédula, nombre, correo, usuario, contraseña y rol.</li>
                <li><span class="font-semibold">Editar usuario</span>: actualizar datos personales, rol y opcionalmente la contraseña.</li>
                <li><span class="font-semibold">Eliminar usuario</span>: eliminar cuentas que ya no deban tener acceso.</li>
            </ul>
            <p class="text-xs text-gray-500">
                Por seguridad, al agregar o editar usuarios se puede mostrar/ocultar la contraseña con el ícono de ojo para verificar que esté bien escrita.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">9. Ajustes del usuario</h2>
            <p class="text-sm mb-2">
                Desde el módulo de <span class="font-semibold">Ajustes</span>, cada usuario puede actualizar su propia información:
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm mb-2">
                <li>Nombre y apellido.</li>
                <li>Correo electrónico y teléfono.</li>
                <li>Nombre de usuario.</li>
                <li>Contraseña (requiere confirmar la contraseña actual y escribir la nueva dos veces).</li>
            </ul>
            <p class="text-xs text-gray-500">
                En el cambio de contraseña, los tres campos tienen ícono de ojo para facilitar la escritura sin errores.
            </p>
        </section>

        <section class="mb-10 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">10. Reportes en PDF</h2>
            <p class="text-sm mb-2">
                En varios módulos (Fichas, Ambientes/Reservas e Inventario) existe un botón <span class="font-semibold">Reportes</span>.
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm mb-2">
                <li>Al hacer clic, se abre un <span class="font-semibold">modal</span> con la opción de descargar en PDF.</li>
                <li>El reporte respeta los filtros de búsqueda aplicados en la pantalla (por ejemplo, ambiente o ficha filtrada).</li>
                <li>Los PDF incluyen encabezados claros, fechas de generación y tablas ordenadas por los campos más relevantes.</li>
            </ul>
        </section>

        <footer class="text-center text-xs text-gray-500 pb-6">
            Sistema de Control de Ambientes - SENA · Manual de usuario
        </footer>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let currentSlide = 0;
            const slides = document.querySelectorAll('.carousel-slide');
            
            if (!slides.length) return;
            
            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }
            
            setInterval(nextSlide, 5000);
        });
    </script>
</body>
</html>


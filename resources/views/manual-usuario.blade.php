<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de usuario v4.5 · Sistema de control de ambientes</title>
    <link rel="stylesheet" href="{{ asset('css/app-premium.css') }}">
    @vite(['resources/css/app.css'])
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
    <div class="carousel-container">
        <div class="carousel-slide active" style="background-image: url('{{ asset('images/posters/carrusel_1.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_2.jpeg') }}');"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('images/posters/carrusel_3.jpeg') }}');"></div>
    </div>

    <div class="relative max-w-5xl mx-auto px-4 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-sena-green mb-1">Versión 4.5.17 · 27 de abril de 2026</p>
                <h1 class="text-3xl font-extrabold text-sena-green mb-1">
                    Manual de usuario
                </h1>
                <p class="text-sm text-gray-600">
                    Sistema de control de ambientes · SENA
                </p>
            </div>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-sena-green text-white text-sm font-semibold hover:bg-emerald-700 shadow shrink-0">
                Volver al inicio de sesión
            </a>
        </header>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">Novedades de la versión 4.5.17 (27 de abril de 2026)</h2>
            <p class="mb-3 text-sm text-gray-600">Esta versión actualiza el manual y refleja mejoras recientes de pantalla, administración y experiencia de uso. Resumen orientado al usuario final:</p>
            <ol class="list-decimal list-outside pl-5 space-y-1.5 text-sm">
                <li>Manual de usuario y enlace desde el inicio de sesión alineados a la <span class="font-semibold">versión 4.5</span> y a la fecha indicada arriba.</li>
                <li><span class="font-semibold">Resultados de aprendizaje</span>: textos de ayuda más breves y centrados en lo que puede hacer en pantalla (buscar, filtrar, crear); la cabecera con filtro por competencia muestra competencia, buscador y acciones en una sola línea adaptada al ancho.</li>
                <li><span class="font-semibold">Formularios de resultado</span> (crear / editar): campos alineados entre <span class="font-semibold">Horas</span> y <span class="font-semibold">Sesiones</span>; el aviso de cupo del complejo va a ancho completo entre las etiquetas y los campos para no desalinear las cajas; una sola nota inferior resume mínimo de horas, cálculo de sesiones y ajuste por cupo.</li>
                <li>En <span class="font-semibold">Editar reserva</span>, la sección visual de <span class="font-semibold">Día</span> (días L–D) se retiró; el día de la semana sigue determinándose automáticamente a partir de la fecha de inicio y la validación del sistema; el resto del flujo de edición no cambia.</li>
                <li><span class="font-semibold">Administración de usuarios</span> (solo administrador): nuevo botón <span class="font-semibold">Actualizar roles</span> entre «Buscar» y «Agregar usuario», con pantalla <span class="font-semibold">Actualización de roles</span>: verificación por cédula (solo números), elección entre <span class="font-semibold">Instructor</span>, <span class="font-semibold">Coordinador</span> y <span class="font-semibold">Coordinador líder</span>, resumen en tarjeta antes de aplicar. No puede cambiarse el propio rol aquí ni dejarse sin administrador si es el único.</li>
                <li><span class="font-semibold">Ajustes de perfil</span>: los modales de editar nombre, apellidos, correo, teléfono, usuario y cambio de contraseña vuelven a abrirse correctamente (se unificó la lógica con el resto de la aplicación cargada por Vite).</li>
                <li>Se mantienen las capacidades ya documentadas en versiones anteriores: <span class="font-semibold">Reportes</span> (PDF y Excel), <span class="font-semibold">Programación</span> en espacios, roles (administrador, coordinación L, coordinador, instructor), <span class="font-semibold">Mi jornada</span> del instructor y medidas de seguridad habituales.</li>
            </ol>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">1. Descripción general</h2>
            <p class="mb-2 text-sm">
                Esta aplicación centraliza la <span class="font-semibold">Gestión de ambientes de formación</span> (salones y laboratorios), su <span class="font-semibold">Asignación a fichas</span> mediante reservas por jornada y día de la semana, y la <span class="font-semibold">Visibilidad para instructores y coordinación</span>, alineada con programas, competencias y resultados de aprendizaje.
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li><span class="font-semibold">Programas, competencias y resultados</span>: Estructura curricular y cupos de sesiones por resultado.</li>
                <li><span class="font-semibold">Fichas</span>: Grupos de formación vinculados a un programa y a una jornada.</li>
                <li><span class="font-semibold">Ambientes (espacios)</span>: Consulta, disponibilidad y, según el rol, gestión del catálogo. La vista de <span class="font-semibold">Horarios</span> presenta la <span class="font-semibold">Programación</span> (ocupación). Las descargas globales están en <span class="font-semibold">Reportes</span>.</li>
                <li><span class="font-semibold">Asignación (reservas)</span>: Vinculan ambiente, ficha, instructor, competencia o resultado, día de la semana y rango de fechas.</li>
                <li><span class="font-semibold">Instructor</span>: Calendario semanal, detalle por día, liberar o recuperar días, festivos de Colombia y reporte PDF propio (por año, mes o semana).</li>
                <li><span class="font-semibold">Usuarios y ajustes de cuenta</span>: Administración de cuentas (según el rol) y datos personales del usuario actual.</li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">2. Cómo se accede (URLs)</h2>
            <p class="text-sm mb-2">
                Tras iniciar sesión, las pantallas autenticadas usan rutas con prefijo <span class="font-mono bg-gray-100 px-1 rounded">/s/…</span> (por ejemplo <span class="font-mono text-xs">/s/espacios</span>, <span class="font-mono text-xs">/s/asignacion/nuevo</span>). El manual y el enlace desde el inicio de sesión siguen siendo accesibles sin ese prefijo.
            </p>
            <p class="text-xs text-gray-500">
                En producción se recomienda HTTPS. La zona autenticada puede forzar conexión segura según la configuración del servidor.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">3. Roles y permisos</h2>
            <p class="text-sm mb-2">Los permisos concretos dependen del rol asignado a cada usuario:</p>
            <ul class="list-disc list-inside space-y-2 text-sm">
                <li>
                    <span class="font-semibold">Administrador</span>: Acceso amplio, incluida la <span class="font-semibold">Administración de usuarios</span> (crear, editar, eliminar cuentas, y <span class="font-semibold">Actualización de roles</span> para instructor, coordinador o coordinador líder) y el centro de reportes en PDF y Excel, salvo las restricciones técnicas del sistema.
                </li>
                <li>
                    <span class="font-semibold">Coordinación L (coordinador líder)</span>: Misma operación diaria que el administrador, <span class="font-semibold">excepto el módulo de usuarios</span> (no crea ni gestiona cuentas de acceso). Puede gestionar fichas, catálogos (programas, niveles, competencias, resultados), <span class="font-semibold">Gestión de ambientes</span> y <span class="font-semibold">Reservas</span> (crear, editar, eliminar), consultas y <span class="font-semibold">Reportes</span> (PDF y Excel).
                </li>
                <li>
                    <span class="font-semibold">Coordinador</span> (coordinación sin la «L»): <span class="font-semibold">Consulta</span> en fichas, horarios, catálogos y resultados (no gestiona usuarios). Puede <span class="font-semibold">Crear reservas</span> (asignar ambiente) y <span class="font-semibold">Descargar reportes</span> (PDF y Excel) según el mismo centro que el administrador y el coordinador líder, sin editar ni eliminar reservas ajenas ni modificar fichas ni catálogos, conforme a las restricciones de permiso en la aplicación.
                </li>
                <li>
                    <span class="font-semibold">Instructor</span>: Ve el <span class="font-semibold">Panel principal</span> y <span class="font-semibold">Mi jornada</span>, el <span class="font-semibold">Detalle por día</span>, y puede <span class="font-semibold">Liberar o recuperar días</span> de sus reservas, gestionar <span class="font-semibold">Festivos</span> en el rango y generar su <span class="font-semibold">Reporte PDF</span> (solo sus sesiones) con filtro por año, mes o semana. <span class="font-semibold">No</span> accede al centro global de <span class="font-semibold">Reportes</span> (listados de centro en PDF/Excel reservado a administración y coordinación).
                </li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">4. Inicio de sesión</h2>
            <ol class="list-decimal list-inside space-y-1 text-sm">
                <li>Abra la pantalla de <span class="font-semibold">Iniciar sesión</span> (desde la página de bienvenida o el enlace de este manual).</li>
                <li>Ingrese <span class="font-semibold">Usuario</span> y <span class="font-semibold">Contraseña</span>.</li>
                <li>Puede usar el <span class="font-semibold">Icono (símbolo de ojo)</span> junto al campo para mostrar u ocultar la contraseña.</li>
                <li>Haga clic en <span class="font-semibold">Ingresar</span>.</li>
                <li>Tras varios intentos fallidos, el acceso puede bloquearse temporalmente; espere el tiempo indicado y vuelva a intentar.</li>
            </ol>
            <p class="text-xs text-gray-500 mt-2">
                El inicio de sesión y otras acciones pueden estar sujetas a límites de frecuencia de peticiones para reducir abusos.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">5. Panel principal (inicio)</h2>
            <p class="text-sm mb-2">
                El contenido depende del rol: los instructores ven un resumen de sus reservas y accesos a la jornada semanal; el resto de los roles ve indicadores y accesos a formación, espacios y asignación según permisos.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">6. Formación (fichas, programas, competencias, resultados)</h2>
            <ul class="list-disc list-inside space-y-2 text-sm">
                <li><span class="font-semibold">Fichas</span>: Listado, búsqueda, alta, edición o baja (si el rol lo permite). La exportación global de fichas está en <span class="font-semibold">Reportes</span>. Cada ficha incluye programa, fechas, jornada, entre otros datos.</li>
                <li><span class="font-semibold">Programas, competencias y resultados</span>: Mantenimiento del catálogo académico; los resultados incluyen <span class="font-semibold">horas</span> y <span class="font-semibold">sesiones</span> (cálculo alineado con reglas del sistema) usadas al validar reservas. Puede buscar y filtrar resultados por competencia; las descargas de listados de <span class="font-semibold">Programas</span> y de <span class="font-semibold">Competencias</span> siguen en <span class="font-semibold">Reportes</span>.</li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">7. Espacios y asignación (horarios, ambientes, reservas)</h2>
            <p class="text-sm mb-2">
                <span class="font-semibold">Espacios (horarios)</span> concentra la <span class="font-semibold">Programación</span>: consulta de ocupación, filtros y disponibilidad. Las descargas globalizadas de esa programación (PDF y Excel) están en <span class="font-semibold">Reportes</span>. La <span class="font-semibold">Gestión</span> del catálogo de ambientes (crear, editar o eliminar salones) está en la sección de gestión para quien tenga permiso. El <span class="font-semibold">Listado de catálogo de ambientes</span> (PDF/Excel) también se descarga desde <span class="font-semibold">Reportes</span>.
            </p>
            <p class="text-sm mb-2">
                <span class="font-semibold">Asignación</span> permite crear y mantener <span class="font-semibold">Reservas</span>: se eligen ambiente, ficha, instructor, competencia y resultado de aprendizaje, y un <span class="font-semibold">Rango de fechas de inicio y fin</span> acorde al <span class="font-semibold">Día de la semana</span> (derivado de la fecha de inicio) y a la <span class="font-semibold">Jornada de la ficha</span>. El sistema valida solapes, cupos de sesiones del resultado y reglas de jornada (entre semana o fin de semana).
            </p>
            <p class="text-xs text-gray-500">
                Antes de reservar, revise la disponibilidad para evitar conflictos innecesarios.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">8. Instructor: «Mi jornada» y detalle del día</h2>
            <p class="text-sm mb-2">
                Desde el inicio puede acceder a <span class="font-semibold">Mi jornada</span> (vista semanal) y al <span class="font-semibold">Detalle por día</span>, donde verá ambiente, ficha, programa, competencia, resultado, fechas de la reserva, indicadores de sesiones y acciones para:
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li><span class="font-semibold">Liberar un día</span>: El ambiente queda disponible para otro instructor en esa fecha (el comportamiento frente a sesiones del resultado se explica en pantalla).</li>
                <li><span class="font-semibold">Recuperar</span> un día liberado, si no hay conflicto con otra reserva.</li>
                <li><span class="font-semibold">Festivos de Colombia</span> en el rango de la reserva, con opción de liberarlos en bloque.</li>
            </ul>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">9. Reportes (PDF, Excel) y acceso</h2>
            <p class="text-sm mb-2">
                <span class="font-semibold">Administración y coordinación (ambos perfiles de coordinación y el administrador)</span> utilizan el menú lateral <span class="font-semibold">Reportes</span> → <span class="font-semibold">Descargar</span>. Allí se ofrecen, para cada bloque, dos botones: <span class="font-semibold">Descargar PDF</span> y <span class="font-semibold">Descargar Excel</span> (archivo <span class="font-mono text-xs">.xls</span> de tabla HTML, apto para abrir en Microsoft Excel o equivalentes).
            </p>
            <p class="text-sm mb-2">Contenido disponible: <span class="font-semibold">Programación</span> (ocupación y fechas, equivalente a la vista global de reservas por ambiente), <span class="font-semibold">Fichas de formación</span>, <span class="font-semibold">Catálogo de ambientes</span> (número, estado, capacidad, tipo), <span class="font-semibold">Programas</span> (nivel, duración) y <span class="font-semibold">Competencias</span> (norma, códigos, horas, porcentaje, enlace a programa cuando aplique o «Catálogo»).</p>
            <p class="text-sm mb-2">Opcionalmente puede añadir a la URL de la exportación el parámetro de búsqueda <span class="font-mono text-xs">?search=…</span> cuando el módulo correspondiente lo use en su listado, para acotar el informe (véase el texto de ayuda en la propia pantalla de reportes).</p>
            <p class="text-sm mb-2">
                El <span class="font-semibold">Instructor</span> no usa este centro global; dispone del botón <span class="font-semibold">Mi reporte PDF</span> (o ruta similar) en el inicio, con filtros por <span class="font-semibold">Período</span> (año, mes, semana), sin listados de todo el centro.
            </p>
            <p class="text-xs text-gray-500">Los <span class="font-semibold">Resultados de aprendizaje</span> se consultan desde <span class="font-semibold">Competencias</span> (enlace «Resultados» en cada fila), no como una fila separada en el centro de reportes descrito aquí.</p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">10. Administración de usuarios</h2>
            <p class="text-sm mb-2">
                Solo el <span class="font-semibold">Administrador</span> accede al listado de usuarios: búsqueda por cédula, creación de cuentas y edición o eliminación según reglas del sistema.
            </p>
            <p class="text-sm mb-2">
                Desde el mismo listado, el botón <span class="font-semibold">Actualizar roles</span> abre el flujo <span class="font-semibold">Actualización de roles</span>, pensado para cuando un docente o coordinación cambia de función: ingrese la <span class="font-semibold">cédula</span> (solo números), pulse <span class="font-semibold">Verificar cédula</span>, elija <span class="font-semibold">Instructor</span>, <span class="font-semibold">Coordinador</span> o <span class="font-semibold">Coordinador líder</span> y revise la tarjeta de resumen antes de <span class="font-semibold">Aplicar actualización</span>. No puede modificarse su propia cuenta desde aquí, ni reasignar el único administrador del sistema.
            </p>
            <p class="text-sm text-gray-600">Se recomiendan contraseñas robustas y no compartir cuentas.</p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">11. Ajustes de la cuenta</h2>
            <p class="text-sm mb-2">
                Cada usuario puede actualizar, desde <span class="font-semibold">Ajustes</span>, su nombre, apellidos, correo, teléfono, nombre de usuario y contraseña mediante los modales abiertos con <span class="font-semibold">Editar</span> o <span class="font-semibold">Cambiar contraseña</span>. El correo, la cédula y el <span class="font-semibold">Rol</span> se muestran con las restricciones indicadas en pantalla. El cambio de contraseña exige la contraseña actual y la confirmación de la nueva.
            </p>
        </section>

        <section class="mb-8 bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-3">12. Privacidad y seguridad en la aplicación</h2>
            <p class="text-sm mb-2">
                El sistema incluye medidas habituales: cabeceras de seguridad, política de contenido, protección frente a envío de formularios cruzados (tokens CSRF en formularios), límites de peticiones en rutas sensibles y sesión configurada según el entorno.
            </p>
            <p class="text-xs text-gray-500">
                Mantenga el equipo y el navegador actualizados y cierre la sesión al usar equipos compartidos.
            </p>
        </section>

        <footer class="text-center text-xs text-gray-500 pb-6">
            Sistema de control de ambientes · SENA · Manual de usuario <span class="font-semibold">v4.5</span> · 17 de abril de 2026
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

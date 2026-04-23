<!-- Header superior (botón retroceso, menú hamburguesa, logo; usuario a la derecha) -->
<nav class="bg-white/95 backdrop-blur-sm border-b border-gray-200/80 shadow-soft w-full max-w-full min-w-0">
    <div class="px-2 sm:px-4 md:px-6 w-full max-w-full min-w-0">
        <div class="flex justify-between items-center min-h-[3.5rem] py-2 gap-2 min-w-0">
            <div class="flex items-center gap-1 sm:gap-2 md:gap-3 min-w-0">
                {{-- Botón menú hamburguesa (primero) --}}
                <button type="button" id="sidebarToggle" aria-label="Abrir menú" aria-expanded="false"
                        class="lg:hidden p-2 -ml-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-[#39B54A] transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                {{-- Botón retroceso: sigue jerarquía lógica (evita ciclos competencias↔resultados) --}}
                @php
                    $backUrl = route('dashboard');
                    if (request()->routeIs('resultados.create') || request()->routeIs('resultados.edit')) {
                        $backUrl = route('resultados.index');
                    } elseif (request()->routeIs('resultados.index')) {
                        $backUrl = route('competencias.index');
                    } elseif (request()->routeIs('competencias.create') || request()->routeIs('competencias.edit')) {
                        $backUrl = route('competencias.index');
                    } elseif (request()->routeIs('competencias.index')) {
                        $backUrl = route('dashboard');
                    } elseif (request()->routeIs('programas.create') || request()->routeIs('programas.edit')) {
                        $backUrl = route('programas.index');
                    } elseif (request()->routeIs('programas.index')) {
                        $backUrl = route('dashboard');
                    } elseif (request()->routeIs('fichas.create') || request()->routeIs('fichas.edit')) {
                        $backUrl = route('fichas.index');
                    } elseif (request()->routeIs('ambientes.gestion.create') || request()->routeIs('ambientes.gestion.edit')) {
                        $backUrl = route('ambientes.gestion.index');
                    } elseif (request()->routeIs('ambientes.gestion.index')) {
                        $backUrl = route('dashboard');
                    } elseif (request()->routeIs('instructor.reporte-reservas') || request()->routeIs('instructor.reporte-reservas-filtro') || request()->routeIs('instructor.export-reservas') || request()->routeIs('instructor.export-reservas-filtro')) {
                        $backUrl = route('dashboard');
                    } elseif (request()->routeIs('reportes.index')) {
                        $backUrl = route('dashboard');
                    } elseif (request()->routeIs('reservas.create') || request()->routeIs('reservas.edit') || request()->routeIs('ambientes.disponibilidad') || request()->routeIs('ambientes.disponibilidad-ambiente')) {
                        $backUrl = route('ambientes.index');
                    } elseif (request()->routeIs('ambientes.index')) {
                        $backUrl = route('dashboard');
                    } elseif (request()->routeIs('users.create') || request()->routeIs('users.edit')) {
                        $backUrl = route('users.index');
                    }
                @endphp
                <a href="{{ $backUrl }}"
                   class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-[#39B54A] transition-colors duration-200"
                   aria-label="Volver">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-7 sm:h-8 w-auto max-w-[7.5rem] sm:max-w-none object-contain object-left drop-shadow-sm shrink-0">
            </div>

            @php
                $navUser = auth()->user();
                $navRolId = (int) ($navUser?->persona?->id_rol ?? 0);
                $rid = config('roles.ids');
                $navNombreClases = match (true) {
                    $navRolId === ($rid['administrador'] ?? 1) => 'text-violet-700 bg-violet-50 border-violet-200/80',
                    $navRolId === ($rid['coordinacion_L'] ?? 2) => 'text-[#2d8f3a] bg-emerald-50 border-emerald-200/80',
                    $navRolId === ($rid['coordinacion'] ?? 3) => 'text-teal-700 bg-teal-50 border-teal-200/80',
                    $navRolId === ($rid['instructor'] ?? 4) => 'text-gray-600 bg-gray-100 border-gray-200/90',
                    default => 'text-gray-600 bg-gray-50 border-gray-200/80',
                };
            @endphp
            <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm shrink-0">
                <span class="hidden sm:inline-flex items-center min-w-0 max-w-[9rem] md:max-w-[14rem] lg:max-w-[16rem] px-2 sm:px-3 py-1.5 font-semibold tracking-tight rounded-xl border {{ $navNombreClases }} shadow-sm">
                    <span class="truncate">{{ $navUser->name ?? '' }}</span>
                </span>
                <div class="hidden sm:block h-5 w-px bg-gray-200 mx-0.5 shrink-0" aria-hidden="true"></div>
                <button type="button" 
                        onclick="showLogoutModal()"
                        class="px-2 sm:px-3.5 py-1.5 font-medium text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 hover:shadow-sm active:scale-[0.98] whitespace-nowrap shrink-0">
                    <span class="hidden sm:inline">🚪</span>
                    <span class="sm:ml-0.5">Salir</span>
                </button>
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</nav>


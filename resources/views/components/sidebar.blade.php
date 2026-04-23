@php
    $user = auth()->user();
@endphp

{{-- Overlay oscuro cuando el menú está abierto (móvil y desktop) --}}
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden transition-opacity duration-300" 
     aria-hidden="true" onclick="toggleSidebarMobile(false)"></div>

<aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col w-64 lg:w-56 bg-white/98 backdrop-blur-sm border-r border-gray-200/80 shadow-soft
    transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out">
    <div class="flex items-center gap-2 px-4 py-4 border-b border-gray-200/80">
        <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-8 w-auto drop-shadow-sm">
        <div class="flex flex-col">
            <span class="text-sm font-semibold text-[#39B54A] tracking-tight">Gestión de</span>
            <span class="text-sm font-semibold text-[#39B54A] tracking-tight">Ambientes</span>
        </div>
    </div>

    <nav class="flex-1 px-2 py-4 space-y-1 text-sm">
        <a href="{{ route('dashboard') }}"
           class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
            <span>🏠</span>
            <span>Inicio</span>
        </a>

        @if($user && ($user->isAdmin() || $user->isCoordinatorL() || $user->isCoordinatorOnly()))
        <div class="mt-3">
            <details {{ request()->routeIs('ambientes.*') || request()->routeIs('fichas.*') || request()->routeIs('users.*') || request()->routeIs('programas.*') || request()->routeIs('niveles-programa.*') || request()->routeIs('competencias.*') || request()->routeIs('resultados.*') ? 'open' : '' }}>
                <summary class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer text-gray-700 hover:bg-gray-100/90 transition-all duration-200">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">{{ $user->isCoordinatorOnly() ? 'Visualización' : 'Administración' }}</span>
                    <span class="text-xs transition-transform duration-200">▾</span>
                </summary>
                <div class="mt-1 space-y-1 pl-3">
                    <a href="{{ route('ambientes.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('ambientes.index') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>🏛️</span>
                        <span>Programación</span>
                    </a>
                    <a href="{{ route('fichas.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('fichas.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>📋</span>
                        <span>Fichas</span>
                    </a>
                    @if($user->canManageCatalog() || $user->isCoordinatorOnly())
                    <a href="{{ route('ambientes.gestion.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('ambientes.gestion.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>🏗️</span>
                        <span>Ambientes</span>
                    </a>
                    @endif
                    @if($user->isAdmin())
                    <a href="{{ route('users.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>👥</span>
                        <span>Usuarios</span>
                    </a>
                    @endif
                    @if($user->canManageCatalog() || $user->isCoordinatorOnly())
                    <a href="{{ route('programas.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('programas.*', 'niveles-programa.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>🎓</span>
                        <span>Programas</span>
                    </a>
                    <a href="{{ route('competencias.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('competencias.*') || request()->routeIs('resultados.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>🧩</span>
                        <span>Competencias</span>
                    </a>
                    @endif
                </div>
            </details>
        </div>

        <div class="mt-3">
            <details {{ request()->routeIs('reportes.*') ? 'open' : '' }}>
                <summary class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer text-gray-700 hover:bg-gray-100/90 transition-all duration-200">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Reportes</span>
                    <span class="text-xs transition-transform duration-200">▾</span>
                </summary>
                <div class="mt-1 space-y-1 pl-3">
                    <a href="{{ route('reportes.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('reportes.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>📊</span>
                        <span>Descargar</span>
                    </a>
                </div>
            </details>
        </div>
        @endif

        <div class="mt-3">
            <details {{ request()->routeIs('ajustes.*') ? 'open' : '' }}>
                <summary class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer text-gray-700 hover:bg-gray-100/90 transition-all duration-200">
                    <span class="text-[11px] font-semibold uppercase tracking-wider">Cuenta</span>
                    <span class="text-xs transition-transform duration-200">▾</span>
                </summary>
                <div class="mt-1 space-y-1 pl-3">
                    <a href="{{ route('ajustes.index') }}"
                       class="sidebar-link flex items-center gap-2 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('ajustes.*') ? 'bg-[#39B54A] text-white active shadow-glow' : 'text-gray-700 hover:bg-gray-100/90' }}">
                        <span>⚙️</span>
                        <span>Ajustes</span>
                    </a>
                </div>
            </details>
        </div>
    </nav>
</aside>


<!-- Navbar (~12% más grande que antes) -->
<nav class="bg-white border-b-4 border-[#39B54A] shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex justify-between items-center min-h-[4.5rem] sm:min-h-[5.5rem] py-2">
            <!-- Logo y título -->
            <div class="flex items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-9 sm:h-11 w-auto">
                <span class="text-base sm:text-xl font-semibold text-[#39B54A] hidden sm:inline">Gestión de Ambientes</span>
            </div>

            <!-- Menú de navegación -->
            <div class="flex items-center gap-1.5 sm:gap-2.5">
                <a href="{{ route('dashboard') }}" 
                   class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#39B54A] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="hidden sm:inline">🏠</span>
                    <span class="sm:ml-0.5">Home</span>
                </a>
                
                <a href="{{ route('fichas.index') }}" 
                   class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium rounded-md transition-colors {{ request()->routeIs('fichas.*') ? 'bg-[#39B54A] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="hidden sm:inline">📋</span>
                    <span class="sm:ml-0.5">Fichas</span>
                </a>
                
                <a href="{{ route('ambientes.index') }}" 
                   class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium rounded-md transition-colors {{ request()->routeIs('ambientes.*') ? 'bg-[#39B54A] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="hidden sm:inline">🏛️</span>
                    <span class="sm:ml-0.5">Ambientes</span>
                </a>
                <a href="{{ route('ajustes.index') }}" 
                   class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium rounded-md transition-colors {{ request()->routeIs('ajustes.*') ? 'bg-[#39B54A] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="hidden sm:inline">⚙️</span>
                    <span class="sm:ml-0.5">Ajustes</span>
                </a>

                @if(auth()->user()->isInstructor())
                <a href="{{ route('instructor.tablero') }}" 
                   class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium rounded-md transition-colors {{ request()->routeIs('instructor.*') ? 'bg-[#39B54A] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="hidden sm:inline">📅</span>
                    <span class="sm:ml-0.5">Mi jornada</span>
                </a>
                @endif
                
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" 
                       class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium rounded-md transition-colors {{ request()->routeIs('users.*') ? 'bg-[#39B54A] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <span class="hidden sm:inline">👥</span>
                        <span class="sm:ml-0.5">User</span>
                    </a>
                @endif

                <!-- Separador -->
                <div class="h-5 w-px bg-gray-300 mx-0.5"></div>

                <!-- Cerrar Sesión -->
                <button type="button" 
                        onclick="showLogoutModal()"
                        class="px-2.5 sm:px-3.5 py-2 text-[11px] sm:text-[13px] font-medium text-red-600 hover:bg-red-50 rounded-md transition-colors">
                    <span class="hidden sm:inline">🚪</span>
                    <span class="sm:ml-0.5">Salir</span>
                </button>
                
                <!-- Formulario de logout oculto -->
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</nav>


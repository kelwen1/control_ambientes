{{-- Modal global: éxito, error, validación (requiere $globalFlash del layout) --}}
@if (!empty($globalFlash['show']))
    @php
        $ft = $globalFlash['type'] ?? 'success';
        $iconWrap = match ($ft) {
            'success' => 'bg-emerald-100',
            'warning' => 'bg-amber-100',
            default => 'bg-red-100',
        };
        $iconColor = match ($ft) {
            'success' => 'text-emerald-600',
            'warning' => 'text-amber-600',
            default => 'text-red-600',
        };
    @endphp
    <div id="globalFlashModal"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/45 backdrop-blur-[2px]"
         role="dialog"
         aria-modal="true"
         aria-labelledby="globalFlashTitle">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 sm:p-8 border border-gray-100 animate-[fadeIn_0.2s_ease-out]">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 sm:h-16 sm:w-16 rounded-full {{ $iconWrap }} mb-4">
                    @if ($ft === 'success')
                        <svg class="h-8 w-8 sm:h-9 sm:w-9 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @elseif ($ft === 'warning')
                        <svg class="h-8 w-8 sm:h-9 sm:w-9 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg class="h-8 w-8 sm:h-9 sm:w-9 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <h3 id="globalFlashTitle" class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">
                    {{ $globalFlash['title'] ?? 'Mensaje' }}
                </h3>
                <div class="text-left text-gray-700 text-sm sm:text-base leading-relaxed space-y-2 mb-6">
                    @foreach ($globalFlash['lines'] ?? [] as $line)
                        <p class="break-words">{{ $line }}</p>
                    @endforeach
                </div>
                <button type="button"
                        onclick="closeGlobalFlashModal()"
                        class="w-full py-3 rounded-xl font-semibold text-base text-white bg-[#39B54A] hover:bg-[#2d8f3a] shadow-md transition-colors duration-200">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
    <script>
        function closeGlobalFlashModal() {
            var m = document.getElementById('globalFlashModal');
            if (m) {
                m.classList.add('hidden');
                m.classList.remove('flex');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            var m = document.getElementById('globalFlashModal');
            if (m) {
                document.body.style.overflow = 'hidden';
                m.addEventListener('click', function (e) {
                    if (e.target === m) {
                        closeGlobalFlashModal();
                    }
                });
            }
            document.querySelectorAll('main p.text-sm.text-red-600').forEach(function (el) {
                el.classList.add('hidden');
                el.setAttribute('aria-hidden', 'true');
            });
            document.querySelectorAll('main p.text-green-800.font-medium, main p.text-red-800.font-medium').forEach(function (p) {
                var box = p.closest('div.mb-6, div.mb-4');
                if (box && box.querySelector('.border-l-4')) {
                    box.classList.add('hidden');
                }
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.getElementById('globalFlashModal') && !document.getElementById('globalFlashModal').classList.contains('hidden')) {
                closeGlobalFlashModal();
            }
        });
    </script>
@endif

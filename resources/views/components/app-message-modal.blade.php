{{-- Modal para mensajes desde JS (misma línea visual que flash-modal). Siempre en DOM; se muestra con showAppMessageModal() --}}
<div id="appMessageModal"
     class="fixed inset-0 z-[70] hidden items-center justify-center p-4 bg-black/45 backdrop-blur-[2px]"
     role="dialog"
     aria-modal="true"
     aria-labelledby="appMessageModalTitle">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 sm:p-8 border border-gray-100">
        <div class="text-center">
            <div id="appMessageModalIconWrap" class="mx-auto flex items-center justify-center h-14 w-14 sm:h-16 sm:w-16 rounded-full mb-4 bg-red-100">
                <span id="appMessageModalIconError" class="hidden">
                    <svg class="h-8 w-8 sm:h-9 sm:w-9 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span id="appMessageModalIconWarning" class="hidden">
                    <svg class="h-8 w-8 sm:h-9 sm:w-9 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <span id="appMessageModalIconSuccess" class="hidden">
                    <svg class="h-8 w-8 sm:h-9 sm:w-9 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <h3 id="appMessageModalTitle" class="text-xl sm:text-2xl font-bold text-gray-900 mb-3"></h3>
            <p id="appMessageModalBody" class="text-left text-gray-700 text-sm sm:text-base leading-relaxed mb-6 whitespace-pre-wrap break-words"></p>
            <button type="button"
                    id="appMessageModalBtn"
                    onclick="closeAppMessageModal()"
                    class="w-full py-3 rounded-xl font-semibold text-base text-white bg-[#39B54A] hover:bg-[#2d8f3a] shadow-md transition-colors duration-200">
                Aceptar
            </button>
        </div>
    </div>
</div>
<script>
    (function () {
        function setIcon(type) {
            var err = document.getElementById('appMessageModalIconError');
            var warn = document.getElementById('appMessageModalIconWarning');
            var ok = document.getElementById('appMessageModalIconSuccess');
            var wrap = document.getElementById('appMessageModalIconWrap');
            if (!err || !warn || !ok || !wrap) return;
            err.classList.add('hidden');
            warn.classList.add('hidden');
            ok.classList.add('hidden');
            wrap.classList.remove('bg-red-100', 'bg-amber-100', 'bg-emerald-100');
            if (type === 'success') {
                ok.classList.remove('hidden');
                wrap.classList.add('bg-emerald-100');
            } else if (type === 'warning') {
                warn.classList.remove('hidden');
                wrap.classList.add('bg-amber-100');
            } else {
                err.classList.remove('hidden');
                wrap.classList.add('bg-red-100');
            }
        }

        window.showAppMessageModal = function (opts) {
            opts = opts || {};
            var type = opts.type || 'error';
            if (type !== 'success' && type !== 'warning') {
                type = 'error';
            }
            var title = opts.title != null ? String(opts.title) : 'Mensaje';
            var message = opts.message != null ? String(opts.message) : '';
            var modal = document.getElementById('appMessageModal');
            var titleEl = document.getElementById('appMessageModalTitle');
            var bodyEl = document.getElementById('appMessageModalBody');
            if (!modal || !titleEl || !bodyEl) {
                return;
            }
            titleEl.textContent = title;
            bodyEl.textContent = message;
            setIcon(type);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        window.closeAppMessageModal = function () {
            var m = document.getElementById('appMessageModal');
            if (m) {
                m.classList.add('hidden');
                m.classList.remove('flex');
                document.body.style.overflow = '';
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('appMessageModal');
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        closeAppMessageModal();
                    }
                });
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var m = document.getElementById('appMessageModal');
            if (m && !m.classList.contains('hidden')) {
                closeAppMessageModal();
            }
        });

        var _nativeAlert = window.alert;
        window.alert = function (msg) {
            if (document.getElementById('appMessageModal')) {
                showAppMessageModal({
                    type: 'warning',
                    title: 'Aviso',
                    message: String(msg),
                });
            } else {
                _nativeAlert(msg);
            }
        };
    })();
</script>

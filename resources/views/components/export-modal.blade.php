{{-- Modal reutilizable: Reportes en PDF --}}
@props([
    'modalId' => 'exportModal',
    'title' => 'Reportes',
])

<div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay" onclick="if (event.target === this) closeExportModal('{{ $modalId }}')">
    <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md" onclick="event.stopPropagation()">
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-[#39B54A]/20 mb-4">
                <span class="text-3xl">📥</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Qué reporte quieres descargar?</h3>
            <p class="text-gray-600 text-sm">Descarga el reporte en formato PDF.</p>
        </div>

        <div class="flex flex-col gap-3 mb-6">
            <a href="{{ $exportPdfUrl ?? '#' }}" 
               class="w-full flex items-center justify-center gap-3 px-4 py-4 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-all shadow-lg transform hover:scale-[1.02]">
                <span class="text-2xl">📄</span>
                <span>Formato PDF</span>
            </a>
        </div>

        <button type="button" 
                onclick="closeExportModal('{{ $modalId }}')"
                class="w-full bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition-colors shadow-lg">
            Cancelar
        </button>
    </div>
</div>

<script>
    function closeExportModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
    function openExportModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
</script>

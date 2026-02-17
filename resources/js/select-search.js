/**
 * Funcionalidad de búsqueda en selects (para reservas)
 */

export function initSelectSearch(searchInputId, selectId) {
    const searchInput = document.getElementById(searchInputId);
    const select = document.getElementById(selectId);

    if (!searchInput || !select) return;

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const options = select.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                // Mantener la opción vacía siempre visible
                return;
            }
            
            const numValue = option.getAttribute('data-num')?.toLowerCase() || '';
            const optionText = option.textContent.toLowerCase();
            
            if (searchTerm === '' || numValue.includes(searchTerm) || optionText.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Sincronizar el valor del select con el input de búsqueda
    select.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            searchInput.value = selectedOption.getAttribute('data-num') || '';
        } else {
            searchInput.value = '';
        }
    });
}

export function initReservaSelects() {
    // Inicializar búsqueda de ambientes
    initSelectSearch('ambiente_search', 'id_ambiente');
    
    // Inicializar búsqueda de fichas
    initSelectSearch('ficha_search', 'id_ficha');

    // Sincronizar valores iniciales al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const ambienteSelect = document.getElementById('id_ambiente');
        const fichaSelect = document.getElementById('id_ficha');
        const ambienteSearch = document.getElementById('ambiente_search');
        const fichaSearch = document.getElementById('ficha_search');

        if (ambienteSelect && ambienteSearch && ambienteSelect.value) {
            const selectedAmbiente = ambienteSelect.options[ambienteSelect.selectedIndex];
            if (selectedAmbiente) {
                ambienteSearch.value = selectedAmbiente.getAttribute('data-num') || '';
            }
        }

        if (fichaSelect && fichaSearch && fichaSelect.value) {
            const selectedFicha = fichaSelect.options[fichaSelect.selectedIndex];
            if (selectedFicha) {
                fichaSearch.value = selectedFicha.getAttribute('data-num') || '';
            }
        }
    });
}

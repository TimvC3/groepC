document.addEventListener('DOMContentLoaded', () => {

    // 1. Luister naar het begin van de sleepactie (werkt voor library EN grid)
    document.addEventListener('dragstart', (e) => {
        if (e.target.tagName === 'IMG') {
            // Sla de bron van de afbeelding op
            e.dataTransfer.setData('text/plain', e.target.src);
            e.target.classList.add('opacity-50');
            
            // Als de afbeelding uit het grid komt, geven we de oude cel een ID mee
            // zodat we die eventueel leeg kunnen maken (verplaatsen ipv kopiëren)
            const parentCell = e.target.closest('.cell');
            if (parentCell) {
                e.dataTransfer.setData('source-cell', parentCell.id);
            }
        }
    });

    document.addEventListener('dragend', (e) => {
        if (e.target.tagName === 'IMG') {
            e.target.classList.remove('opacity-50');
        }
    });

    // 2. Behandel de dropzones (de buttons)
    const cells = document.querySelectorAll('.cell');

    cells.forEach(cell => {
        cell.addEventListener('dragover', (e) => {
            e.preventDefault(); // CRUCIAAL: laat de browser weten dat we hier mogen droppen
            cell.classList.add('bg-blue-50', 'border-blue-400');
        });

        cell.addEventListener('dragleave', () => {
            cell.classList.remove('bg-blue-50', 'border-blue-400');
        });

        cell.addEventListener('click', (e) => {
        // We controleren of er een afbeelding in de cel staat
        if (cell.getAttribute('aria-pressed') === 'true') {
            
            // Haal het nummer van de district op uit het data-attribuut
            const districtNummer = cell.dataset.district;

            // Zet de cel terug naar de originele staat met het nummer
            cell.innerHTML = `
                <span class="text-gray-400 font-bold text-lg md:text-3xl">${districtNummer}</span>
            `;
            
            // Zet de status weer op 'false'
            cell.setAttribute('aria-pressed', 'false');
            
            console.log(`Cel ${districtNummer} is leeggemaakt.`);
        }
        });

        cell.addEventListener('drop', (e) => {
            e.preventDefault();
            cell.classList.remove('bg-blue-50', 'border-blue-400');

            const imgSrc = e.dataTransfer.getData('text/plain');
            const sourceCellId = e.dataTransfer.getData('source-cell');

            if (imgSrc) {
                // Als de afbeelding uit een andere cel kwam: maak die oude cel leeg
                if (sourceCellId) {
                    const oldCell = document.getElementById(sourceCellId);
                    if (oldCell && oldCell !== cell) {
                        oldCell.innerHTML = `<span class="text-gray-400 font-bold text-xl">${oldCell.dataset.district}</span>`;
                        oldCell.setAttribute('aria-pressed', 'false');
                    }
                }

                // Vul de nieuwe cel met de afbeelding
                // We voegen draggable="true" expliciet toe aan de nieuwe IMG tag
                cell.innerHTML = `
                    <img src="${imgSrc}" 
                         alt=""
                         aria-hidden="true"
                         draggable="true" 
                         class="w-full h-full object-cover rounded-lg cursor-move">
                `;
                cell.setAttribute('aria-pressed', 'true');
            }
        });
    });
});

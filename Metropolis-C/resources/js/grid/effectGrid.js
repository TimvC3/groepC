const gridEffectData = window.gridEffectData || {};
const effectCategories = gridEffectData.categories || [];
const facilityScoreMatrix = gridEffectData.scoreMatrix || {};

let draggedData = null;
let sourceCell = null;
let droppedOnGrid = false;

function formatScore(score) {
    return score > 0 ? `+${score}` : String(score);
}

function scoreColorClass(score) {
    if (score > 0) {
        return 'text-green-700 dark:text-green-300';
    }

    if (score < 0) {
        return 'text-red-600 dark:text-red-300';
    }

    return 'text-gray-500 dark:text-gray-400';
}

function selectedFacilityIds() {
    return Array.from(document.querySelectorAll('.grid-cell'))
        .map((cell) => cell.dataset.facilityId)
        .filter(Boolean);
}

function updateStatus(totalScore, facilityCount) {
    const statusElement = document.getElementById('effect-status');

    if (!statusElement) {
        return;
    }

    statusElement.textContent = facilityCount === 0
        ? 'Geen faciliteiten geselecteerd. Totale score is 0.'
        : `${facilityCount} faciliteiten geselecteerd. Totale score is ${formatScore(totalScore)}.`;
}

function hideOpenTooltips(exceptCell = null) {
    document.querySelectorAll('.grid-cell').forEach((cell) => {
        if (cell === exceptCell) {
            return;
        }

        cell.querySelector('.facility-tooltip')?.classList.add('hidden');
        cell.querySelector('.facility-tooltip')?.classList.remove('block');
    });
}

function toggleCellTooltip(cell) {
    const tooltip = cell.querySelector('.facility-tooltip');

    if (!tooltip) {
        return;
    }

    const isHidden = tooltip.classList.contains('hidden');

    hideOpenTooltips(cell);
    tooltip.classList.toggle('hidden', !isHidden);
    tooltip.classList.toggle('block', isHidden);
}

function updateEffectView() {
    const totals = Object.fromEntries(effectCategories.map((category) => [category.id, 0]));
    const facilityIds = selectedFacilityIds();

    facilityIds.forEach((facilityId) => {
        const scores = facilityScoreMatrix[facilityId] || {};

        effectCategories.forEach((category) => {
            totals[category.id] += Number(scores[category.id] ?? 0);
        });
    });

    let totalScore = 0;

    effectCategories.forEach((category) => {
        const categoryScore = totals[category.id];
        const scoreElement = document.getElementById(`effect-category-score-${category.id}`);

        totalScore += categoryScore;

        if (!scoreElement) {
            return;
        }

        scoreElement.textContent = formatScore(categoryScore);
        scoreElement.className = [
            'text-sm font-bold',
            scoreColorClass(categoryScore),
        ].join(' ');
    });

    const totalElement = document.getElementById('effect-total-score');

    if (totalElement) {
        totalElement.textContent = formatScore(totalScore);
        totalElement.className = [
            'text-3xl font-bold',
            scoreColorClass(totalScore),
        ].join(' ');
    }

    document
        .getElementById('effect-empty-state')
        ?.classList
        .toggle('hidden', facilityIds.length > 0);

    updateStatus(totalScore, facilityIds.length);
}

function createFacilityCellContent(facility) {
    const wrapper = document.createElement('div');
    const icon = document.createElement('div');
    const tooltip = document.createElement('div');

    wrapper.className = 'relative flex flex-col items-center pointer-events-none';
    icon.className = 'text-2xl';
    icon.textContent = facility.icon;
    tooltip.className = [
        'facility-tooltip',
        'absolute bottom-full left-1/2 z-20 mb-2 hidden -translate-x-1/2 whitespace-nowrap',
        'rounded-md px-2 py-1 text-xs font-medium shadow-lg',
        'group-hover:block group-focus-within:block',
    ].join(' ');
    tooltip.style.backgroundColor = '#111827';
    tooltip.style.border = '1px solid #374151';
    tooltip.style.color = '#ffffff';
    tooltip.textContent = facility.name;

    wrapper.append(icon, tooltip);

    return wrapper;
}

function removeCellContent(cell) {
    const index = cell.dataset.index;
    const label = document.createElement('span');

    label.className = 'text-gray-400 text-xs font-mono';
    label.textContent = index;

    cell.replaceChildren(label);
    delete cell.dataset.facilityId;
    cell.removeAttribute('aria-label');
    cell.removeAttribute('title');
    cell.classList.remove('group', 'border-solid', 'bg-blue-50', 'dark:bg-blue-900/20');
    cell.classList.add('border-dashed');
    cell.removeAttribute('draggable');

    updateEffectView();
}

function fillCell(cell, facility) {
    cell.replaceChildren(createFacilityCellContent(facility));
    cell.dataset.facilityId = facility.id;
    cell.setAttribute('aria-label', facility.name);
    cell.setAttribute('title', facility.name);
    cell.classList.remove('border-dashed');
    cell.classList.add('group', 'border-solid', 'bg-blue-50', 'dark:bg-blue-900/20');
    cell.setAttribute('draggable', 'true');

    updateEffectView();
}

function bindLibraryItems() {
    document.querySelectorAll('.zoning-item').forEach((item) => {
        item.addEventListener('dragstart', () => {
            sourceCell = null;
            droppedOnGrid = false;
            draggedData = {
                id: item.dataset.id,
                name: item.dataset.name,
                icon: item.dataset.icon,
            };
        });
    });
}

function bindGridCells() {
    document.querySelectorAll('.grid-cell').forEach((cell) => {
        cell.addEventListener('dragstart', (event) => {
            if (!cell.dataset.facilityId) {
                event.preventDefault();
                return;
            }

            sourceCell = cell;
            droppedOnGrid = false;
            draggedData = {
                id: cell.dataset.facilityId,
                icon: cell.querySelector('.text-2xl').innerText,
                name: cell.getAttribute('aria-label'),
            };

            cell.style.opacity = '0.5';
        });

        cell.addEventListener('dragend', () => {
            cell.style.opacity = '1';

            if (sourceCell && !droppedOnGrid) {
                removeCellContent(sourceCell);
            }

            droppedOnGrid = false;
            sourceCell = null;
        });

        cell.addEventListener('dragover', (event) => {
            event.preventDefault();
            cell.classList.add('bg-indigo-50', 'border-indigo-400');
        });

        cell.addEventListener('dragleave', () => {
            cell.classList.remove('bg-indigo-50', 'border-indigo-400');
        });

        cell.addEventListener('drop', (event) => {
            event.preventDefault();
            cell.classList.remove('bg-indigo-50', 'border-indigo-400');

            if (!draggedData) {
                return;
            }

            droppedOnGrid = true;

            if (sourceCell && sourceCell !== cell) {
                removeCellContent(sourceCell);
            }

            fillCell(cell, draggedData);
            sourceCell = null;
        });

        cell.addEventListener('click', () => {
            if (cell.classList.contains('border-solid')) {
                toggleCellTooltip(cell);
            }
        });
    });
}

function bindSearch() {
    const searchInput = document.getElementById('designation-search');

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', (event) => {
        const query = event.target.value.toLowerCase();

        document.querySelectorAll('.zoning-item').forEach((item) => {
            const text = `${item.dataset.name}${item.dataset.category}`.toLowerCase();

            item.style.display = text.includes(query) ? 'block' : 'none';
        });
    });
}

function bindClearButton() {
    document.getElementById('clear-grid')?.addEventListener('click', () => {
        document.querySelectorAll('.grid-cell').forEach((cell) => removeCellContent(cell));
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindLibraryItems();
    bindGridCells();
    bindSearch();
    bindClearButton();
    updateEffectView();
});

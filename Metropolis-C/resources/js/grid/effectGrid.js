const gridEffectData = window.gridEffectData || {};
const effectCategories = gridEffectData.categories || [];
const facilityScoreMatrix = gridEffectData.scoreMatrix || {};

const gridColumns = 4;

let draggedData = null;
let sourceCell = null;
let droppedOnGrid = false;

function formatScore(score) {
    return score > 0 ? `+${score}` : String(score);
}

function scoreColorClass(score) {
    if (score > 0) return 'text-green-700 dark:text-green-300';
    if (score < 0) return 'text-red-600 dark:text-red-300';
    return 'text-gray-500 dark:text-gray-400';
}

function selectedFacilityIds() {
    return Array.from(document.querySelectorAll('.grid-cell'))
        .map((cell) => cell.dataset.facilityId)
        .filter(Boolean);
}

function updateStatus(totalScore, facilityCount) {
    const statusElement = document.getElementById('effect-status');
    if (!statusElement) return;

    statusElement.textContent = facilityCount === 0
        ? 'Geen faciliteiten geselecteerd. Totale score is 0.'
        : `${facilityCount} faciliteiten geselecteerd. Totale score is ${formatScore(totalScore)}.`;
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

        if (!scoreElement) return;

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

function getSurroundingCells(cell) {
    const index = Number(cell.dataset.index);
    const allCells = Array.from(document.querySelectorAll('.grid-cell'));
    const totalCells = allCells.length;
    const row = Math.floor((index - 1) / gridColumns);
    const column = (index - 1) % gridColumns;

    return allCells.filter((otherCell) => {
        const otherIndex = Number(otherCell.dataset.index);
        const otherRow = Math.floor((otherIndex - 1) / gridColumns);
        const otherColumn = (otherIndex - 1) % gridColumns;

        const isAroundCell = Math.abs(otherRow - row) <= 1
            && Math.abs(otherColumn - column) <= 1;

        return otherIndex >= 1 && otherIndex <= totalCells && isAroundCell;
    });
}

function getLocalScores(cell) {
    const localCells = getSurroundingCells(cell);
    const totals = Object.fromEntries(effectCategories.map((category) => [category.id, 0]));
    let facilityCount = 0;

    localCells.forEach((localCell) => {
        const facilityId = localCell.dataset.facilityId;

        if (!facilityId) return;

        facilityCount += 1;

        const scores = facilityScoreMatrix[facilityId] || {};

        effectCategories.forEach((category) => {
            totals[category.id] += Number(scores[category.id] ?? 0);
        });
    });

    return {
        facilityCount,
        scores: effectCategories.map((category) => ({
            name: category.name,
            score: totals[category.id],
        })),
    };
}

function createTooltip() {
    const tooltip = document.createElement('div');

    tooltip.className = [
        'facility-tooltip',
        'absolute bottom-full left-1/2 z-30 mb-3 hidden -translate-x-1/2',
        'w-64 rounded-lg p-3 text-left text-xs shadow-lg',
    ].join(' ');

    tooltip.style.backgroundColor = '#111827';
    tooltip.style.border = '1px solid #374151';
    tooltip.style.color = '#ffffff';

    return tooltip;
}

function updateTooltip(cell) {
    const tooltip = cell.querySelector('.facility-tooltip');
    if (!tooltip) return;

    const localData = getLocalScores(cell);
    const total = localData.scores.reduce((sum, item) => sum + item.score, 0);

    tooltip.innerHTML = `
        <div class="mb-2 font-bold">Local quality-of-life impact</div>
        <div class="mb-2 text-gray-300">
            Based on this area and surrounding areas.
        </div>

        <div class="mb-2 text-gray-300">
            ${localData.facilityCount} function(s) nearby
        </div>

        <div class="space-y-1">
            ${localData.scores.map((item) => `
                <div class="flex justify-between gap-3">
                    <span>${item.name}</span>
                    <span class="${item.score > 0 ? 'text-green-300' : item.score < 0 ? 'text-red-300' : 'text-gray-300'}">
                        ${formatScore(item.score)}
                    </span>
                </div>
            `).join('')}
        </div>

        <div class="mt-2 border-t border-gray-600 pt-2 flex justify-between font-bold">
            <span>Total</span>
            <span class="${total > 0 ? 'text-green-300' : total < 0 ? 'text-red-300' : 'text-gray-300'}">
                ${formatScore(total)}
            </span>
        </div>
    `;
}

function createFacilityCellContent(facility) {
    const wrapper = document.createElement('div');
    const icon = document.createElement('div');
    const tooltip = createTooltip();

    wrapper.className = 'relative flex flex-col items-center';
    icon.className = 'text-2xl pointer-events-none';
    icon.textContent = facility.icon;

    wrapper.append(icon, tooltip);

    return wrapper;
}

function showCellTooltip(cell) {
    const tooltip = cell.querySelector('.facility-tooltip');
    if (!tooltip) return;

    updateTooltip(cell);

    tooltip.classList.remove('hidden');
    tooltip.classList.add('block');
}

function hideCellTooltip(cell) {
    const tooltip = cell.querySelector('.facility-tooltip');
    if (!tooltip) return;

    tooltip.classList.add('hidden');
    tooltip.classList.remove('block');
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

            if (!draggedData) return;

            droppedOnGrid = true;

            if (sourceCell && sourceCell !== cell) {
                removeCellContent(sourceCell);
            }

            fillCell(cell, draggedData);
            sourceCell = null;
        });

        cell.addEventListener('mouseenter', () => {
            if (cell.dataset.facilityId) {
                showCellTooltip(cell);
            }
        });

        cell.addEventListener('mouseleave', () => {
            hideCellTooltip(cell);
        });
    });
}

function bindSearch() {
    const searchInput = document.getElementById('designation-search');
    if (!searchInput) return;

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

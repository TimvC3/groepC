const gridEffectData = window.gridEffectData || {};
const effectCategories = gridEffectData.categories || [];
const facilityScoreMatrix = gridEffectData.scoreMatrix || {};

const gridColumns = 4;

let draggedData = null;
let sourceCell = null;
let droppedOnGrid = false;
let activeTooltip = null;
let simulationDateTime = null;

const isTouchDevice = () => window.matchMedia('(pointer: coarse)').matches;

function formatScore(score) {
    return score > 0 ? `+${score}` : String(score);
}

function scoreColorClass(score) {
    if (score > 0) return 'text-green-700 dark:text-green-300';
    if (score < 0) return 'text-red-600 dark:text-red-300';
    return 'text-gray-500 dark:text-gray-400';
}

function isNightTime(dateTime) {
    const hour = dateTime.getHours();

    return hour >= 18 || hour < 6;
}

function updateSimulationDisplay() {
    const simulationDateTimeElement = document.getElementById('simulation-datetime');
    const dayNightStatusElement = document.getElementById('day-night-status');
    const eventStatusElement = document.getElementById('simulation-event-status');

    if (!simulationDateTimeElement || !dayNightStatusElement || !eventStatusElement || !simulationDateTime) {
        return;
    }

    simulationDateTimeElement.textContent = simulationDateTime.toLocaleString();

    if (isNightTime(simulationDateTime)) {
        dayNightStatusElement.textContent = 'Night Mode';
    } else {
        dayNightStatusElement.textContent = 'Day Mode';
    }

    const currentHour = simulationDateTime.getHours();

    if (currentHour >= 7 && currentHour <= 9) {
        eventStatusElement.textContent = 'Morning traffic event active.';
    } else if (currentHour >= 17 && currentHour <= 19) {
        eventStatusElement.textContent = 'Evening traffic event active.';
    } else if (isNightTime(simulationDateTime)) {
        eventStatusElement.textContent = 'Night rules active.';
    } else {
        eventStatusElement.textContent = 'No time-based event active.';
    }
}

function startSimulation() {
    const dateInput = document.getElementById('simulation-date');
    const timeInput = document.getElementById('simulation-time');

    if (!dateInput || !timeInput) {
        return;
    }

    const selectedDate = dateInput.value;
    const selectedTime = timeInput.value;

    if (!selectedDate || !selectedTime) {
        alert('Select a date and time first.');
        return;
    }

    simulationDateTime = new Date(`${selectedDate}T${selectedTime}`);
    updateSimulationDisplay();
}

function bindSimulationSettings() {
    const startButton = document.getElementById('start-simulation');

    if (!startButton) {
        return;
    }

    startButton.addEventListener('click', startSimulation);
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

function getOrCreateTooltip() {
    let tooltip = document.getElementById('grid-tooltip');

    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'grid-tooltip';
        tooltip.className = 'fixed hidden w-64 rounded-lg p-3 text-left text-xs shadow-lg pointer-events-none';
        tooltip.style.backgroundColor = '#111827';
        tooltip.style.border = '1px solid #374151';
        tooltip.style.color = '#ffffff';
        tooltip.style.zIndex = '9999';
        document.body.appendChild(tooltip);
    }

    return tooltip;
}

function positionTooltip(tooltip, x, y) {
    const offset = 14;
    const tooltipWidth = 256;
    const tooltipHeight = tooltip.offsetHeight || 200;
    const viewportWidth = window.innerWidth;

    let top = y - tooltipHeight - offset;
    let left = x - tooltipWidth / 2;

    left = Math.max(8, Math.min(left, viewportWidth - tooltipWidth - 8));

    if (top < 8) {
        top = y + offset;
    }

    tooltip.style.left = `${left}px`;
    tooltip.style.top = `${top}px`;
}

function updateTooltipContent(cell, tooltip) {
    const facilityName = cell.getAttribute('aria-label') || 'Unknown function';
    const localData = getLocalScores(cell);
    const total = localData.scores.reduce((sum, item) => sum + item.score, 0);

    tooltip.innerHTML = `
        <div class="mb-2 font-bold">${facilityName}</div>

        <div class="mb-2 text-gray-300">
            Local quality-of-life impact
        </div>

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

function hideCellTooltip() {
    getOrCreateTooltip().classList.add('hidden');
    activeTooltip = null;
}

function showCellTooltip(cell, x, y) {
    const tooltip = getOrCreateTooltip();
    activeTooltip = cell;
    updateTooltipContent(cell, tooltip);
    tooltip.classList.remove('hidden');
    positionTooltip(tooltip, x, y);
}

function createFacilityCellContent(facility) {
    const wrapper = document.createElement('div');
    const icon = document.createElement('div');

    wrapper.className = 'relative flex flex-col items-center';
    icon.className = 'text-2xl pointer-events-none';
    icon.textContent = facility.icon;

    wrapper.append(icon);

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
    cell.classList.remove('group', 'border-solid', 'bg-blue-50', 'dark:bg-blue-900/20');
    cell.classList.add('border-dashed');
    cell.removeAttribute('draggable');

    updateEffectView();
}

function fillCell(cell, facility) {
    cell.replaceChildren(createFacilityCellContent(facility));
    cell.dataset.facilityId = facility.id;
    cell.setAttribute('aria-label', facility.name);
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

        cell.addEventListener('mouseenter', (event) => {
            if (isTouchDevice()) return;
            if (cell.dataset.facilityId) {
                showCellTooltip(cell, event.clientX, event.clientY);
            }
        });

        cell.addEventListener('mousemove', (event) => {
            if (isTouchDevice()) return;
            if (!cell.dataset.facilityId) return;
            const tooltip = getOrCreateTooltip();
            if (!tooltip.classList.contains('hidden')) {
                positionTooltip(tooltip, event.clientX, event.clientY);
            }
        });

        cell.addEventListener('mouseleave', () => {
            if (isTouchDevice()) return;
            hideCellTooltip();
        });

        cell.addEventListener('touchstart', (event) => {
            if (!cell.dataset.facilityId) return;

            event.preventDefault();

            const touch = event.touches[0];

            if (activeTooltip === cell) {
                hideCellTooltip();
            } else {
                showCellTooltip(cell, touch.clientX, touch.clientY);
            }
        }, { passive: false });
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

function bindOutsideTap() {
    document.addEventListener('touchstart', (event) => {
        if (!activeTooltip) return;
        if (!event.target.closest('.grid-cell')) {
            hideCellTooltip();
        }
    }, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
    bindLibraryItems();
    bindGridCells();
    bindSearch();
    bindClearButton();
    bindOutsideTap();
    bindSimulationSettings();
    updateEffectView();

    console.log('Simulation JS loaded');

document.getElementById('start-simulation')?.addEventListener('click', () => {
    console.log('Button clicked');

    const date = document.getElementById('simulation-date').value;
    const time = document.getElementById('simulation-time').value;

    console.log(date, time);

    const simulationDateTime = new Date(`${date}T${time}`);

    document.getElementById('simulation-datetime').textContent =
        simulationDateTime.toLocaleString();

    const hour = simulationDateTime.getHours();

    document.getElementById('day-night-status').textContent =
        hour >= 18 || hour < 6
            ? 'Night Mode'
            : 'Day Mode';
});
});
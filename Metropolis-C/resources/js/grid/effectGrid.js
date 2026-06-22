const gridEffectData = window.gridEffectData || {};
const effectCategories = gridEffectData.categories || [];
const facilityScoreMatrix = gridEffectData.scoreMatrix || {};
const neighbourRules = gridEffectData.neighbourRules || {};
const gridEventEffectData = window.gridEventEffectData || {};
const eventImpactMatrix = Object.fromEntries(
    (gridEventEffectData.events || []).map((event) => [String(event.id), event])
);
const gridConditionData = window.gridConditionData || {};
const facilityRestrictions = window.gridRestrictions || [];

const gridPermissions = window.gridPermissions || {};
const approvedGridCells = window.approvedGridCells || {};
const approveCellUrl = window.approveCellUrl || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const gridColumns = 4;
const simulationStorageKey = 'metropolis.simulationDateTime';
const approvedStorageKey = 'metropolis.approvedCells';

let draggedData = null;
let sourceCell = null;
let droppedOnGrid = false;
let activeTooltip = null;
let touchTapState = null;
let simulationDateTime = null;
let simulationInterval = null;
let simulationRunning = false;
let simulationSpeed = 1;
let lastSimulationSpeed = 1;
let lastApprovedFeedbackAt = 0;
let lastNeighbourFeedbackAt = 0;

const isTouchDevice = () => window.matchMedia('(pointer: coarse)').matches;
const canApproveDestinations = Boolean(gridPermissions.canApproveDestinations);

function formatScore(score) {
    return score > 0 ? `+${score}` : String(score);
}

function scoreColorClass(score) {
    if (score > 0) return 'text-green-700 dark:text-green-300';
    if (score < 0) return 'text-red-600 dark:text-red-300';
    return 'text-gray-500 dark:text-gray-400';
}

function showPlacementFeedback(message, type = 'error') {
    document.getElementById('placement-feedback')?.remove();

    const feedback = document.createElement('div');
    feedback.id = 'placement-feedback';
    feedback.textContent = message;
    feedback.setAttribute('role', 'alert');

    feedback.style.position = 'fixed';
    feedback.style.right = '20px';
    feedback.style.bottom = '20px';
    feedback.style.zIndex = '999999';
    feedback.style.maxWidth = '360px';
    feedback.style.padding = '14px 18px';
    feedback.style.borderRadius = '10px';
    feedback.style.fontSize = '14px';
    feedback.style.fontWeight = '700';
    feedback.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.25)';

    if (type === 'error') {
        feedback.style.backgroundColor = '#fee2e2';
        feedback.style.color = '#991b1b';
        feedback.style.border = '1px solid #fecaca';
    } else {
        feedback.style.backgroundColor = '#dcfce7';
        feedback.style.color = '#166534';
        feedback.style.border = '1px solid #bbf7d0';
    }

    document.body.appendChild(feedback);

    const statusElement = document.getElementById('effect-status');

    if (statusElement) {
        statusElement.textContent = message;
    }

    setTimeout(() => {
        feedback.remove();
    }, 4000);
}

function isApprovedCell(cell) {
    return cell?.dataset?.approved === 'true';
}

function approvedMessage() {
    const now = Date.now();

    if (now - lastApprovedFeedbackAt < 1200) {
        return;
    }

    lastApprovedFeedbackAt = now;

    showPlacementFeedback(
        'This destination has already been approved and can no longer be changed or removed.',
        'error'
    );
}

let approvedCells = { ...approvedGridCells };

function loadApprovedCells() {
    return approvedCells;
}

function saveApprovedCells(cells) {
    approvedCells = { ...cells };
}

function storeApprovedCell(cell) {
    approvedCells[cell.dataset.index] = {
        itemId: cell.dataset.itemId,
        itemType: cell.dataset.itemType,
        name: cell.getAttribute('aria-label'),
    };
}

function removeStoredApprovedCell(cell) {
    delete approvedCells[cell.dataset.index];
}

function getApprovedItem(approvedCell) {
    if (approvedCell.itemType === 'facility') {
        const libraryItem = Array.from(document.querySelectorAll('.zoning-item'))
            .find((item) => String(item.dataset.id) === String(approvedCell.itemId));

        return {
            type: 'facility',
            id: approvedCell.itemId,
            name: approvedCell.name,
            icon: libraryItem?.dataset.icon || '✓',
        };
    }

    if (approvedCell.itemType === 'event') {
        const event = eventImpactMatrix[String(approvedCell.itemId)];

        return {
            ...(event || {}),
            type: 'event',
            id: approvedCell.itemId,
            name: approvedCell.name,
        };
    }

    return null;
}

function renderApprovedCell(cell, approvedCell) {
    const item = getApprovedItem(approvedCell);
    if (!item) return;

    const isEvent = item.type === 'event';

    cell.replaceChildren(isEvent ? createEventCellContent(item) : createFacilityCellContent(item));

    cell.dataset.itemId = approvedCell.itemId;
    cell.dataset.itemType = approvedCell.itemType;
    cell.dataset.approved = 'true';

    cell.setAttribute('aria-label', approvedCell.name);
    cell.classList.remove('border-dashed');
    cell.classList.add('group', 'border-solid');
    cell.classList.toggle('bg-blue-50', !isEvent);
    cell.classList.toggle('dark:bg-blue-900/20', !isEvent);
    cell.classList.toggle('bg-amber-50', isEvent);
    cell.classList.toggle('dark:bg-amber-900/20', isEvent);
    cell.setAttribute('draggable', 'true');

    updateApprovalUI(cell);
}

function restoreApprovedCellsFromStorage() {
    const cells = loadApprovedCells();

    document.querySelectorAll('.grid-cell').forEach((cell) => {
        const approvedCell = cells[cell.dataset.index];

        if (!approvedCell) return;

        renderApprovedCell(cell, approvedCell);
    });
}

function createApprovedBadge() {
    const badge = document.createElement('span');

    badge.className = [
        'approved-badge',
        'mt-1',
        'rounded-full',
        'border',
        'border-green-600',
        'bg-green-100',
        'px-2',
        'py-0.5',
        'text-[10px]',
        'font-bold',
        'uppercase',
        'tracking-wide',
        'text-green-700',
    ].join(' ');

    badge.textContent = 'Approved';

    return badge;
}

function createApproveButton(cell) {
    const button = document.createElement('button');

    button.type = 'button';
    button.className = [
        'approve-cell-button',
        'mt-2',
        'rounded-md',
        'bg-green-600',
        'px-2',
        'py-1',
        'text-xs',
        'font-semibold',
        'text-white',
        'shadow-sm',
        'hover:bg-green-700',
        'focus:outline-none',
        'focus:ring-2',
        'focus:ring-green-500',
    ].join(' ');

    button.textContent = 'Approve';

    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        approveCell(cell);
    });

    return button;
}

function applyApprovedStyle(cell) {
    cell.classList.remove(
        'border-dashed',
        'border-gray-300',
        'dark:border-gray-600',
        'border-indigo-400',
        'bg-indigo-50',
        'border-red-400',
        'bg-red-50'
    );

    cell.classList.add(
        'border-solid',
        'border-green-500',
        'bg-green-50',
        'dark:bg-green-900/20',
        'cursor-not-allowed'
    );

    cell.title = 'Approved destination: this cell can no longer be changed or removed.';
}

function removeApprovedStyle(cell) {
    cell.classList.remove(
        'border-green-500',
        'bg-green-50',
        'dark:bg-green-900/20',
        'cursor-not-allowed'
    );

    cell.title = '';
}

function getCellContentWrapper(cell) {
    return cell.querySelector('.relative');
}

function updateApprovalUI(cell) {
    cell.querySelector('.approved-badge')?.remove();
    cell.querySelector('.approve-cell-button')?.remove();

    if (!cell.dataset.itemId) {
        delete cell.dataset.approved;
        removeApprovedStyle(cell);
        return;
    }

    const wrapper = getCellContentWrapper(cell);

    if (isApprovedCell(cell)) {
        applyApprovedStyle(cell);

        if (wrapper) {
            wrapper.append(createApprovedBadge());
        }

        return;
    }

    removeApprovedStyle(cell);

    if (canApproveDestinations && wrapper) {
        wrapper.append(createApproveButton(cell));
    }
}

async function approveCell(cell) {
    if (!canApproveDestinations) {
        showPlacementFeedback('You are not authorized to approve destinations.', 'error');
        return;
    }

    if (!cell.dataset.itemId) {
        showPlacementFeedback('Only a cell with a destination can be approved.', 'error');
        return;
    }

    if (!approveCellUrl) {
        showPlacementFeedback('Approve route is missing.', 'error');
        return;
    }

    try {
        const response = await fetch(approveCellUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                cell_index: cell.dataset.index,
                item_type: cell.dataset.itemType,
                item_id: cell.dataset.itemId,
                item_name: cell.getAttribute('aria-label'),
            }),
        });

        if (!response.ok) {
            showPlacementFeedback('This destination could not be approved.', 'error');
            return;
        }

        cell.dataset.approved = 'true';
        storeApprovedCell(cell);
        updateApprovalUI(cell);

        showPlacementFeedback('Destination approved. This cell can no longer be changed or removed.', 'success');
    } catch {
        showPlacementFeedback('This destination could not be approved.', 'error');
    }
}

function isNightTime(dateTime) {
    const hour = dateTime.getHours();

    return hour >= 18 || hour < 6;
}

function selectedSimulationDateTime() {
    if (simulationDateTime) {
        return simulationDateTime;
    }

    return null;
}

function storedSimulationDateTime() {
    const storedValue = localStorage.getItem(simulationStorageKey);

    if (!storedValue) return null;

    const storedDateTime = new Date(storedValue);

    return Number.isNaN(storedDateTime.getTime()) ? null : storedDateTime;
}

function sameDate(dateTime, dateString) {
    const year = dateTime.getFullYear();
    const month = String(dateTime.getMonth() + 1).padStart(2, '0');
    const day = String(dateTime.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}` === dateString;
}

function formatDateInputValue(dateTime) {
    const year = dateTime.getFullYear();
    const month = String(dateTime.getMonth() + 1).padStart(2, '0');
    const day = String(dateTime.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function formatDisplayDateTime(dateTime) {
    const day = String(dateTime.getDate()).padStart(2, '0');
    const month = String(dateTime.getMonth() + 1).padStart(2, '0');
    const year = dateTime.getFullYear();
    const hours = String(dateTime.getHours()).padStart(2, '0');
    const minutes = String(dateTime.getMinutes()).padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

function formatDisplayTime(dateTime) {
    const hours = String(dateTime.getHours()).padStart(2, '0');
    const minutes = String(dateTime.getMinutes()).padStart(2, '0');

    return `${hours}:${minutes}`;
}

function timeToMinutes(time) {
    const [hours, minutes] = String(time || '00:00')
        .split(':')
        .map(Number);

    return (hours * 60) + minutes;
}

function eventOccursOn(event, dateTime) {
    if (!event.eventDate) return false;

    const eventDate = new Date(`${event.eventDate}T00:00`);
    const simulationDate = new Date(
        dateTime.getFullYear(),
        dateTime.getMonth(),
        dateTime.getDate()
    );

    if (simulationDate < eventDate) return false;

    switch (event.recurrenceType) {
        case 'daily':
            return true;
        case 'weekly':
            return simulationDate.getDay() === eventDate.getDay();
        case 'monthly':
            return simulationDate.getDate() === eventDate.getDate();
        case 'yearly':
            return simulationDate.getMonth() === eventDate.getMonth()
                && simulationDate.getDate() === eventDate.getDate();
        default:
            return sameDate(dateTime, event.eventDate);
    }
}

function eventIsActiveAt(event, dateTime = selectedSimulationDateTime()) {
    if (!dateTime) return false;
    if (!eventOccursOn(event, dateTime)) return false;

    const currentMinutes = (dateTime.getHours() * 60) + dateTime.getMinutes();
    const startMinutes = timeToMinutes(event.startTime);
    const endMinutes = timeToMinutes(event.endTime);

    if (endMinutes <= startMinutes) {
        return currentMinutes >= startMinutes || currentMinutes <= endMinutes;
    }

    return currentMinutes >= startMinutes && currentMinutes <= endMinutes;
}

function nextEventOccurrenceAt(event, dateTime = selectedSimulationDateTime()) {
    if (!dateTime || !event.eventDate) return null;

    const originalDate = new Date(`${event.eventDate}T00:00`);
    const startMinutes = timeToMinutes(event.startTime);
    const endMinutes = timeToMinutes(event.endTime);
    let candidateDate = new Date(
        dateTime.getFullYear(),
        dateTime.getMonth(),
        dateTime.getDate()
    );

    if (candidateDate < originalDate) {
        candidateDate = originalDate;
    }

    for (let daysChecked = 0; daysChecked < 370; daysChecked += 1) {
        if (eventOccursOn(event, candidateDate)) {
            const startsAt = new Date(candidateDate);
            startsAt.setMinutes(startMinutes);

            const endsAt = new Date(candidateDate);
            endsAt.setMinutes(endMinutes);
                        if (endMinutes <= startMinutes) {
                endsAt.setDate(endsAt.getDate() + 1);
            }

            if (dateTime <= endsAt) {
                return { startsAt, endsAt };
            }
        }

        candidateDate.setDate(candidateDate.getDate() + 1);
    }

    return null;
}

function activeSelectedEvents() {
    const dateTime = selectedSimulationDateTime();

    return selectedEvents()
        .map((selection) => ({
            ...selection,
            active: eventIsActiveAt(selection.event, dateTime),
        }));
}

function activeEventNames() {
    return activeSelectedEvents()
        .filter((selection) => selection.active)
        .map((selection) => selection.event.name);
}

function updateSimulationDisplay() {
    const simulationDateTimeElement = document.getElementById('simulation-datetime');
    const dayNightStatusElement = document.getElementById('day-night-status');

    if (!simulationDateTimeElement || !dayNightStatusElement || !simulationDateTime) {
        return;
    }

    simulationDateTimeElement.textContent = simulationDateTime.toLocaleString();
    localStorage.setItem(simulationStorageKey, simulationDateTime.toISOString());

    if (isNightTime(simulationDateTime)) {
        dayNightStatusElement.textContent = 'Night Mode';
    } else {
        dayNightStatusElement.textContent = 'Day Mode';
    }

    updateEffectView();
    updateEventEffectView();
    updateUpcomingEventList();
}

function clearSimulationDisplay() {
    const simulationDateTimeElement = document.getElementById('simulation-datetime');
    const dayNightStatusElement = document.getElementById('day-night-status');

    if (simulationDateTimeElement) {
        simulationDateTimeElement.textContent = 'Not started';
    }

    if (dayNightStatusElement) {
        dayNightStatusElement.textContent = 'No simulation time selected';
    }
}

function hydrateSimulationMoment() {
    simulationDateTime = storedSimulationDateTime();

    if (!simulationDateTime) {
        localStorage.removeItem(simulationStorageKey);
        clearSimulationDisplay();
        updateUpcomingEventList();
        return;
    }

    const dateInput = document.getElementById('simulation-date');
    const timeInput = document.getElementById('simulation-time');

    if (dateInput) {
        dateInput.value = formatDateInputValue(simulationDateTime);
    }

    if (timeInput) {
        const hours = String(simulationDateTime.getHours()).padStart(2, '0');
        const minutes = String(simulationDateTime.getMinutes()).padStart(2, '0');
        timeInput.value = `${hours}:${minutes}`;
    }

    setSimulationButtonState(
        'Resume Simulation',
        ['bg-amber-600', 'hover:bg-amber-700', 'bg-indigo-600', 'hover:bg-indigo-700'],
        ['bg-green-600', 'hover:bg-green-700']
    );

    updateSimulationDisplay();
}

function updateActiveEventDisplay() {
    const eventStatusElement = document.getElementById('simulation-event-status');
    if (!eventStatusElement) return;

    const names = activeEventNames();

    eventStatusElement.textContent = names.length > 0
        ? names.join(', ')
        : 'No dragged event active at simulation time';
}

function setSimulationButtonState(label, removeClasses = [], addClasses = []) {
    const button = document.getElementById('start-simulation');

    if (!button) return;

    button.textContent = label;
    button.classList.remove(...removeClasses);
    button.classList.add(...addClasses);
}

function runSimulationClock() {
    clearInterval(simulationInterval);

    simulationInterval = setInterval(() => {
        if (!simulationRunning || simulationSpeed === 0) {
            return;
        }

        simulationDateTime = new Date(
            simulationDateTime.getTime() + (simulationSpeed * 60000)
        );

        updateSimulationDisplay();
    }, 1000);
}

function startSimulation() {
    if (simulationRunning) {
        pauseSimulation();
        return;
    }

    if (simulationDateTime) {
        resumeSimulation();
        return;
    }

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

    simulationRunning = true;
    setSimulationButtonState(
        'Pause Simulation',
        ['bg-indigo-600', 'hover:bg-indigo-700', 'bg-green-600', 'hover:bg-green-700'],
        ['bg-amber-600', 'hover:bg-amber-700']
    );

    updateSimulationDisplay();
    runSimulationClock();
}

function pauseSimulation() {
    clearInterval(simulationInterval);
    simulationInterval = null;
    simulationRunning = false;

    setSimulationButtonState(
        'Resume Simulation',
        ['bg-amber-600', 'hover:bg-amber-700', 'bg-indigo-600', 'hover:bg-indigo-700'],
        ['bg-green-600', 'hover:bg-green-700']
    );
}

function resumeSimulation() {
    if (!simulationDateTime) {
        startSimulation();
        return;
    }

    if (simulationSpeed === 0) {
        simulationSpeed = lastSimulationSpeed || 1;
    }

    simulationRunning = true;
    setSimulationButtonState(
        'Pause Simulation',
        ['bg-green-600', 'hover:bg-green-700', 'bg-indigo-600', 'hover:bg-indigo-700'],
        ['bg-amber-600', 'hover:bg-amber-700']
    );
    updateSimulationDisplay();
    runSimulationClock();
}

function bindSimulationSpeedControls() {
    document.querySelectorAll('.sim-speed').forEach((button) => {
        button.addEventListener('click', () => {
            const selectedSpeed = Number(button.dataset.speed);

            if (selectedSpeed !== 0) {
                lastSimulationSpeed = selectedSpeed;
            }

            simulationSpeed = selectedSpeed;

            document.querySelectorAll('.sim-speed').forEach((btn) => {
                btn.classList.remove('bg-indigo-600', 'text-white');
            });

            button.classList.add('bg-indigo-600', 'text-white');

            if (simulationDateTime && !simulationRunning) {
                resumeSimulation();
            }
        });
    });
}

function bindSimulationSettings() {
    const startButton = document.getElementById('start-simulation');

    if (!startButton) {
        return;
    }

    startButton.addEventListener('click', startSimulation);

    const resetSimulationMoment = () => {
        simulationDateTime = null;
        simulationRunning = false;
        clearInterval(simulationInterval);
        simulationInterval = null;
        localStorage.removeItem(simulationStorageKey);
        setSimulationButtonState(
            'Start Simulation',
            ['bg-amber-600', 'hover:bg-amber-700', 'bg-green-600', 'hover:bg-green-700'],
            ['bg-indigo-600', 'hover:bg-indigo-700']
        );
        clearSimulationDisplay();
        updateEffectView();
        updateEventEffectView();
        updateUpcomingEventList();
    };

    document.getElementById('simulation-date')?.addEventListener('change', resetSimulationMoment);
    document.getElementById('simulation-time')?.addEventListener('change', resetSimulationMoment);
}

function selectedFacilityIds() {
    return Array.from(document.querySelectorAll('.grid-cell'))
        .filter((cell) => cell.dataset.itemType === 'facility')
        .map((cell) => cell.dataset.itemId)
        .filter(Boolean);
}

function currentGridState() {
    return new Map(
        Array.from(document.querySelectorAll('.grid-cell'))
            .filter((cell) => cell.dataset.itemId)
            .map((cell) => [
                Number(cell.dataset.index),
                {
                    type: cell.dataset.itemType,
                    id: String(cell.dataset.itemId),
                    name: cell.getAttribute('aria-label') || '',
                },
            ])
    );
}

function neighbouringIndexes(index) {
    const zeroBasedIndex = index - 1;
    const row = Math.floor(zeroBasedIndex / gridColumns);
    const column = zeroBasedIndex % gridColumns;
    const neighbours = [];

    if (row > 0) neighbours.push(index - gridColumns);
    if (row < 2) neighbours.push(index + gridColumns);
    if (column > 0) neighbours.push(index - 1);
    if (column < gridColumns - 1) neighbours.push(index + 1);

    return neighbours;
}

function conditionViolations(state) {
    const violations = [];

    state.forEach((item, index) => {
        if (item.type !== 'facility') return;

        const facilityConditions = gridConditionData[item.id]?.conditions || [];
        const neighbouringFacilityIds = neighbouringIndexes(index)
            .map((neighbourIndex) => state.get(neighbourIndex))
            .filter((neighbour) => neighbour?.type === 'facility')
            .map((neighbour) => neighbour.id);

        facilityConditions.forEach((condition) => {
            const hasNeighbour = neighbouringFacilityIds.includes(
                String(condition.neighbourFacilityId)
            );

            if (condition.type === 'required_neighbour' && !hasNeighbour) {
                violations.push(
                    `${item.name} requires ${condition.neighbourFacilityName} directly next to it.`
                );
            }

            if (condition.type === 'forbidden_neighbour' && hasNeighbour) {
                violations.push(
                    `${item.name} cannot be directly next to ${condition.neighbourFacilityName}.`
                );
            }
        });
    });

    return violations;
}

function proposedGridState(targetCell, item, originalCell = null) {
    const state = currentGridState();

    if (originalCell && originalCell !== targetCell) {
        state.delete(Number(originalCell.dataset.index));
    }

    state.set(Number(targetCell.dataset.index), {
        type: item.type,
        id: String(item.id),
        name: item.name || '',
    });

    return state;
}

function setConditionStatus(message, type = 'neutral') {
    const status = document.getElementById('condition-status');
    if (!status) return;

    const colorClasses = {
        neutral: 'border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300',
        success: 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300',
        error: 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300',
    };

    status.className = `mt-4 w-full max-w-md rounded-lg border px-4 py-3 text-sm ${colorClasses[type]}`;
    status.textContent = message;
}

function updateConditionStatus() {
    const state = currentGridState();
    const placedFacilities = Array.from(state.values())
        .filter((item) => item.type === 'facility');

    if (placedFacilities.length === 0) {
        setConditionStatus(
            'Function conditions are active. Place a function to evaluate its neighbour rules.'
        );
        return;
    }

    const violations = conditionViolations(state);

    if (violations.length > 0) {
        setConditionStatus(violations[0], 'error');
        return;
    }

    const appliedConditionCount = placedFacilities.reduce(
        (total, item) => total + (gridConditionData[item.id]?.conditions?.length || 0),
        0
    );

    setConditionStatus(
        appliedConditionCount > 0
            ? `${appliedConditionCount} neighbour condition(s) applied successfully.`
            : 'The placed functions have no neighbour conditions.',
        'success'
    );
}

function selectedEvents() {
    return Array.from(document.querySelectorAll('.grid-cell'))
        .filter((cell) => cell.dataset.itemType === 'event')
        .map((cell) => ({
            cellIndex: cell.dataset.index,
            event: eventImpactMatrix[String(cell.dataset.itemId)],
        }))
        .filter((item) => item.event);
}

function setDragPayload(event, payload) {
    draggedData = payload;

    if (!event.dataTransfer) return;

    event.dataTransfer.effectAllowed = 'copyMove';
    event.dataTransfer.setData('application/json', JSON.stringify(payload));
    event.dataTransfer.setData('text/plain', payload.name || '');
}

function getDropPayload(event) {
    if (draggedData) return draggedData;

    const payload = event.dataTransfer?.getData('application/json');
    if (!payload) return null;

    try {
        return JSON.parse(payload);
    } catch {
        return null;
    }
}

function emptyCategoryTotals() {
    return Object.fromEntries(effectCategories.map((category) => [category.id, 0]));
}

function facilityCategoryTotals() {
    const totals = emptyCategoryTotals();
    const facilityIds = selectedFacilityIds();

    facilityIds.forEach((facilityId) => {
        const scores = facilityScoreMatrix[facilityId] || {};

        effectCategories.forEach((category) => {
            totals[category.id] += Number(scores[category.id] ?? 0);
        });
    });

    return totals;
}

function eventCategoryTotals() {
    const totals = emptyCategoryTotals();

    activeSelectedEvents()
        .filter((selection) => selection.active)
        .forEach(({ event }) => {
            (event.impacts || []).forEach((impact) => {
                totals[impact.category_id] += Number(impact.score ?? 0);
            });
        });

    return totals;
}

function updateStatus(totalScore, facilityCount) {
    const statusElement = document.getElementById('effect-status');
    if (!statusElement) return;

    statusElement.textContent = facilityCount === 0
        ? 'No functions selected. The Quality of Life score is 0.'
        : `${facilityCount} function(s) selected. The Quality of Life score is ${formatScore(totalScore)}.`;
}

function updateEffectView() {
    const facilityTotals = facilityCategoryTotals();
    const eventTotals = eventCategoryTotals();
    const facilityIds = selectedFacilityIds();

    let totalScore = 0;

    effectCategories.forEach((category) => {
        const categoryScore = facilityTotals[category.id] + eventTotals[category.id];
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
        .toggle('hidden', facilityIds.length > 0 || activeEventNames().length > 0);

    updateStatus(totalScore, facilityIds.length);
    updateConditionStatus();
    updateActiveEventDisplay();
}

function updateEventEffectView() {
    const totals = eventCategoryTotals();
    const eventSelections = activeSelectedEvents();

    let totalScore = 0;

    effectCategories.forEach((category) => {
        const categoryScore = totals[category.id];
        const scoreElement = document.getElementById(`event-effect-category-score-${category.id}`);

        totalScore += categoryScore;

        if (!scoreElement) return;

        scoreElement.textContent = formatScore(categoryScore);
        scoreElement.className = [
            'text-sm font-bold',
            scoreColorClass(categoryScore),
        ].join(' ');
    });

    const totalElement = document.getElementById('event-effect-total-score');

    if (totalElement) {
        totalElement.textContent = formatScore(totalScore);
        totalElement.className = [
            'text-3xl font-bold',
            scoreColorClass(totalScore),
        ].join(' ');
    }

    document
        .getElementById('event-effect-empty-state')
        ?.classList
        .toggle('hidden', eventSelections.length > 0);

    renderEventEffectList(eventSelections);
}

function renderEventEffectList(eventSelections) {
    const list = document.getElementById('event-effect-list');
    if (!list) return;

    list.replaceChildren();

    eventSelections.forEach(({ cellIndex, event, active }) => {
        const score = active
            ? (event.impacts || []).reduce((sum, impact) => sum + Number(impact.score ?? 0), 0)
            : 0;
        const item = document.createElement('div');
        const header = document.createElement('div');
        const name = document.createElement('div');
        const scoreElement = document.createElement('div');
        const meta = document.createElement('div');

        item.className = active
            ? 'rounded-md border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-900/20'
            : 'rounded-md border border-gray-200 bg-gray-50 px-4 py-3 opacity-75 dark:border-gray-700 dark:bg-gray-900/40';
        header.className = 'flex items-start justify-between gap-3';
        name.className = 'text-sm font-semibold text-gray-900 dark:text-gray-100';
        scoreElement.className = [
            'text-sm font-bold',
            scoreColorClass(score),
        ].join(' ');
        meta.className = 'mt-1 text-xs text-gray-500 dark:text-gray-400';

        name.textContent = event.name;
        scoreElement.textContent = active ? formatScore(score) : 'inactive';
        meta.textContent = `Cell ${cellIndex} - ${active ? 'active' : 'not active'} at simulation time`;

        header.append(name, scoreElement);
        item.append(header, meta);

        (event.impacts || []).forEach((impact) => {
            const row = document.createElement('div');
            row.className = 'mt-1 flex justify-between gap-3 text-xs text-gray-500 dark:text-gray-400';
            row.innerHTML = `
                <span>${impact.category_name}</span>
                <span>${active ? formatScore(Number(impact.score ?? 0)) : '0'}</span>
            `;
            item.append(row);
        });

        list.append(item);
    });
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

function getHorizontalVerticalNeighbourCells(cell) {
    const index = Number(cell.dataset.index);
    const allCells = Array.from(document.querySelectorAll('.grid-cell'));
    const totalCells = allCells.length;
    const row = Math.floor((index - 1) / gridColumns);
    const column = (index - 1) % gridColumns;

    return allCells.filter((otherCell) => {
        const otherIndex = Number(otherCell.dataset.index);
        const otherRow = Math.floor((otherIndex - 1) / gridColumns);
        const otherColumn = (otherIndex - 1) % gridColumns;
        const isDirectNeighbour = Math.abs(otherRow - row) + Math.abs(otherColumn - column) === 1;

        return otherIndex >= 1 && otherIndex <= totalCells && isDirectNeighbour;
    });
}

function getNeighbourRule(facilityId) {
    return neighbourRules[String(facilityId)] || neighbourRules[facilityId] || null;
}

function directNeighbourIndexes(index, totalCells) {
    const row = Math.floor((index - 1) / gridColumns);
    const column = (index - 1) % gridColumns;
    const indexes = [];

    for (let otherIndex = 1; otherIndex <= totalCells; otherIndex += 1) {
        const otherRow = Math.floor((otherIndex - 1) / gridColumns);
        const otherColumn = (otherIndex - 1) % gridColumns;
        const isDirectNeighbour = Math.abs(otherRow - row) + Math.abs(otherColumn - column) === 1;

        if (isDirectNeighbour) {
            indexes.push(String(otherIndex));
        }
    }

    return indexes;
}

function requiredNeighbourIsPresent(cell, facilityId) {
    const rule = getNeighbourRule(facilityId);

    if (!rule) return true;

    return getHorizontalVerticalNeighbourCells(cell).some((neighbourCell) => {
        return neighbourCell.dataset.itemType === 'facility'
            && String(neighbourCell.dataset.itemId) === String(rule.requiredNeighbourId);
    });
}

function facilityNameById(facilityId) {
    return Array.from(document.querySelectorAll('.zoning-item'))
        .find((item) => String(item.dataset.id) === String(facilityId))
        ?.dataset
        ?.name;
}

function placementSnapshot(targetCell, payload) {
    const snapshot = new Map();

    document.querySelectorAll('.grid-cell').forEach((gridCell) => {
        if (gridCell.dataset.itemType !== 'facility') return;

        snapshot.set(String(gridCell.dataset.index), {
            id: gridCell.dataset.itemId,
            name: gridCell.getAttribute('aria-label') || facilityNameById(gridCell.dataset.itemId),
        });
    });

    if (sourceCell && sourceCell !== targetCell) {
        snapshot.delete(String(sourceCell.dataset.index));
    }

    if (payload.type === 'facility') {
        snapshot.set(String(targetCell.dataset.index), {
            id: payload.id,
            name: payload.name,
        });
    } else {
        snapshot.delete(String(targetCell.dataset.index));
    }

    return snapshot;
}

function requiredNeighbourViolationsForPlacement(targetCell, payload) {
    const snapshot = placementSnapshot(targetCell, payload);
    const totalCells = document.querySelectorAll('.grid-cell').length;

    return Array.from(snapshot.entries())
        .map(([cellIndex, facility]) => {
            const rule = getNeighbourRule(facility.id);

            if (!rule) return null;

            const hasRequiredNeighbour = directNeighbourIndexes(Number(cellIndex), totalCells)
                .some((neighbourIndex) => {
                    return String(snapshot.get(neighbourIndex)?.id) === String(rule.requiredNeighbourId);
                });

            if (hasRequiredNeighbour) return null;

            return {
                facilityName: facility.name || facilityNameById(facility.id) || 'This function',
                requiredNeighbourName: rule.requiredNeighbourName || 'the required neighbour',
            };
        })
        .filter(Boolean);
}

function showRequiredNeighbourViolation(violation) {
    showPlacementFeedback(
        `${violation.facilityName} cannot be placed here. It must be placed directly next to ${violation.requiredNeighbourName} horizontally or vertically.`,
        'error'
    );
}

function showRequiredNeighbourMessage(facility) {
    const rule = getNeighbourRule(facility.id);
    const requiredNeighbourName = rule?.requiredNeighbourName || 'the required neighbour';

    showPlacementFeedback(
        `${facility.name} cannot be placed here. It must be placed directly next to ${requiredNeighbourName} horizontally or vertically.`,
        'error'
    );
}

function showRequiredNeighbourToast(facility) {
    const now = Date.now();

    if (now - lastNeighbourFeedbackAt < 2000) {
        return;
    }

    lastNeighbourFeedbackAt = now;
    showRequiredNeighbourMessage(facility);
}

function getLocalScores(cell) {
    const localCells = getSurroundingCells(cell);
    const totals = Object.fromEntries(effectCategories.map((category) => [category.id, 0]));
    let facilityCount = 0;

    localCells.forEach((localCell) => {
        const facilityId = localCell.dataset.itemType === 'facility'
            ? localCell.dataset.itemId
            : null;

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
    const itemName = cell.getAttribute('aria-label') || 'Unknown item';
    const isEvent = cell.dataset.itemType === 'event';

    if (isEvent) {
        const event = eventImpactMatrix[String(cell.dataset.itemId)];
        const active = event ? eventIsActiveAt(event) : false;

        tooltip.innerHTML = `
            <div class="mb-2 font-bold">${itemName}</div>
            <div class="mb-2 text-gray-300">${event?.date || ''} ${event?.startTime || ''} - ${event?.endTime || ''}</div>
            <div class="mb-2 ${active ? 'text-green-300' : 'text-gray-300'}">
                ${active ? 'Active at simulation time' : 'Not active at simulation time'}
            </div>
            <div class="space-y-1">
                ${(event?.impacts || []).map((impact) => `
                    <div class="flex justify-between gap-3">
                        <span>${impact.category_name}</span>
                        <span class="${Number(impact.score ?? 0) > 0 ? 'text-green-300' : Number(impact.score ?? 0) < 0 ? 'text-red-300' : 'text-gray-300'}">
                            ${active ? formatScore(Number(impact.score ?? 0)) : '0'}
                        </span>
                    </div>
                `).join('')}
            </div>
        `;

        return;
    }

    const localData = getLocalScores(cell);
    const total = localData.scores.reduce((sum, item) => sum + item.score, 0);

    tooltip.innerHTML = `
        <div class="mb-2 font-bold">${itemName}</div>

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

function createEventCellContent(event) {
    const wrapper = document.createElement('div');
    const label = document.createElement('div');

    wrapper.className = 'relative flex h-full w-full items-center justify-center px-1';
    label.className = 'pointer-events-none line-clamp-3 break-words text-center text-[0.65rem] font-semibold leading-tight text-gray-900 dark:text-gray-100 sm:text-xs';
    label.textContent = event.name;

    wrapper.append(label);

    return wrapper;
}

function removeCellContent(cell) {
    if (isApprovedCell(cell)) {
        approvedMessage();
        return false;
    }

    const index = cell.dataset.index;
    const label = document.createElement('span');

    label.className = 'text-gray-400 text-xs font-mono';
    label.textContent = index;

    cell.replaceChildren(label);
    delete cell.dataset.itemId;
    delete cell.dataset.itemType;
    delete cell.dataset.approved;

    cell.removeAttribute('aria-label');
    cell.classList.remove(
        'group',
        'border-solid',
        'bg-blue-50',
        'dark:bg-blue-900/20',
        'bg-amber-50',
        'dark:bg-amber-900/20',
        'border-green-500',
        'bg-green-50',
        'dark:bg-green-900/20',
        'cursor-not-allowed'
    );
    cell.classList.add('border-dashed');
    cell.removeAttribute('draggable');

    removeStoredApprovedCell(cell);
    updateApprovalUI(cell);
    updateEffectView();
    updateEventEffectView();

    return true;
}
function fillCell(cell, item) {
    if (isApprovedCell(cell)) {
        approvedMessage();
        return false;
    }

    const isEvent = item.type === 'event';

    cell.replaceChildren(isEvent ? createEventCellContent(item) : createFacilityCellContent(item));

    cell.dataset.itemId = item.id;
    cell.dataset.itemType = item.type;
    delete cell.dataset.approved;

    cell.setAttribute('aria-label', item.name);
    cell.classList.remove(
        'border-dashed',
        'border-green-500',
        'bg-green-50',
        'dark:bg-green-900/20',
        'cursor-not-allowed'
    );
    cell.classList.add('group', 'border-solid');
    cell.classList.toggle('bg-blue-50', !isEvent);
    cell.classList.toggle('dark:bg-blue-900/20', !isEvent);
    cell.classList.toggle('bg-amber-50', isEvent);
    cell.classList.toggle('dark:bg-amber-900/20', isEvent);
    cell.setAttribute('draggable', 'true');

    removeStoredApprovedCell(cell);
    updateApprovalUI(cell);
    updateEffectView();
    updateEventEffectView();

    return true;
}

function getAdjacentFacilities(targetCell) {
    return getHorizontalVerticalNeighbourCells(targetCell)
        .filter((c) => c.dataset.itemType === 'facility')
        .map((c) => ({ id: c.dataset.itemId, name: c.getAttribute('aria-label') || `Facility ${c.dataset.itemId}` }));
}

function findRestrictionConflicts(targetCell, facilityId) {
    return getAdjacentFacilities(targetCell).filter(({ id }) =>
        facilityRestrictions.some((r) =>
            (String(r.facility_id_1) === String(facilityId) && String(r.facility_id_2) === String(id))
            || (String(r.facility_id_2) === String(facilityId) && String(r.facility_id_1) === String(id))
        )
    );
}

function showRestrictionError(conflicts, droppedName) {
    const conflictNames = conflicts.map((c) => c.name).join(', ');

    let errorEl = document.getElementById('restriction-error');

    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.id = 'restriction-error';
        errorEl.className = 'fixed bottom-4 right-4 z-50 max-w-sm rounded-lg border border-red-200 bg-red-50 px-4 py-3 shadow-lg dark:border-red-900/50 dark:bg-red-900/20';
        document.body.appendChild(errorEl);
    }

    errorEl.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-800 dark:text-red-300">Placement not allowed</p>
                <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                    <strong>${droppedName}</strong> cannot be placed next to: ${conflictNames}
                </p>
            </div>
            <button onclick="document.getElementById('restriction-error').remove()" class="text-lg leading-none text-red-400 hover:text-red-600">&times;</button>
        </div>
    `;

    clearTimeout(errorEl._hideTimeout);
    errorEl._hideTimeout = setTimeout(() => errorEl?.remove(), 4000);
}

function bindLibraryItems() {
    document.querySelectorAll('.zoning-item').forEach((item) => {
        item.addEventListener('dragstart', (event) => {
            sourceCell = null;
            droppedOnGrid = false;

            setDragPayload(event, {
                type: 'facility',
                id: item.dataset.id,
                name: item.dataset.name,
                icon: item.dataset.icon,
            });
        });
    });
}

function bindEventItems() {
    document.querySelectorAll('.event-item').forEach((item) => {
        item.addEventListener('dragstart', (event) => {
            sourceCell = null;
            droppedOnGrid = false;

            setDragPayload(event, {
                type: 'event',
                id: item.dataset.id,
                name: item.dataset.name,
                categoryId: item.dataset.categoryId,
                categoryName: item.dataset.category,
                score: Number(item.dataset.score ?? 0),
                status: item.dataset.status,
                date: item.dataset.date,
                startTime: item.dataset.startTime,
                endTime: item.dataset.endTime,
            });
        });
    });
}

function updateUpcomingEventList() {
    const list = document.querySelector('[data-upcoming-events-list]');
    const emptyMessage = document.querySelector('[data-upcoming-empty-message]');
    const cards = Array.from(document.querySelectorAll('[data-upcoming-event-card]'));
    const dateTime = selectedSimulationDateTime();

    if (!list || cards.length === 0) return;

    cards.forEach((card) => card.classList.add('hidden'));

    if (!dateTime) {
        emptyMessage?.classList.remove('hidden');
        return;
    }

    const upcomingItems = cards
        .map((card) => {
            const event = eventImpactMatrix[String(card.dataset.id)];
            const occurrence = event ? nextEventOccurrenceAt(event, dateTime) : null;

            return {
                card,
                event,
                occurrence,
                active: event ? eventIsActiveAt(event, dateTime) : false,
            };
        })
        .filter((item) => item.occurrence !== null)
        .sort((a, b) => {
            if (a.active && !b.active) return -1;
            if (!a.active && b.active) return 1;

            return a.occurrence.startsAt - b.occurrence.startsAt;
        });

    emptyMessage?.classList.toggle('hidden', upcomingItems.length > 0);

    upcomingItems.slice(0, 3).forEach(({ card, occurrence, active }) => {
        const dateLine = card.querySelector('[data-upcoming-date-line]');
        const statusBadge = card.querySelector('[data-upcoming-status-badge]');

        card.dataset.date = formatDisplayDateTime(occurrence.startsAt);
        card.dataset.startTime = formatDisplayTime(occurrence.startsAt);
        card.dataset.endTime = formatDisplayTime(occurrence.endsAt);

        if (dateLine) {
            dateLine.textContent = `${formatDisplayDateTime(occurrence.startsAt)} - ${formatDisplayTime(occurrence.endsAt)}`;
        }

        if (statusBadge) {
            statusBadge.textContent = active ? 'active' : 'planned';
            statusBadge.className = [
                'rounded-full px-2 py-1 text-xs font-semibold capitalize',
                active
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            ].join(' ');
        }

        card.classList.remove('hidden');
        list.appendChild(card);
    });
}

function bindGridCells() {
    document.querySelectorAll('.grid-cell').forEach((cell) => {
        updateApprovalUI(cell);

        cell.addEventListener('dragstart', (event) => {
            if (!cell.dataset.itemId) {
                event.preventDefault();
                return;
            }

            if (isApprovedCell(cell)) {
                event.preventDefault();
                approvedMessage();
                sourceCell = null;
                draggedData = null;
                droppedOnGrid = true;
                return;
            }

            sourceCell = cell;
            droppedOnGrid = false;

            const payload = cell.dataset.itemType === 'event'
                ? {
                    type: 'event',
                    id: cell.dataset.itemId,
                    name: cell.getAttribute('aria-label'),
                    score: eventImpactMatrix[String(cell.dataset.itemId)]?.score ?? 0,
                }
                : {
                    type: 'facility',
                    id: cell.dataset.itemId,
                    icon: cell.querySelector('.text-2xl')?.innerText ?? '',
                    name: cell.getAttribute('aria-label'),
                };

            setDragPayload(event, payload);
            cell.style.opacity = '0.5';
        });

        cell.addEventListener('dragend', () => {
            cell.style.opacity = '1';

            droppedOnGrid = false;
            sourceCell = null;
            draggedData = null;
        });

        cell.addEventListener('dragover', (event) => {
            event.preventDefault();

            if (isApprovedCell(cell)) {
                event.dataTransfer.dropEffect = 'move';
                cell.classList.add('bg-red-50', 'border-red-400');
                return;
            }

            if (draggedData?.type === 'facility' && !requiredNeighbourIsPresent(cell, draggedData.id)) {
                event.dataTransfer.dropEffect = 'move';
                cell.classList.add('bg-red-50', 'border-red-400');
                showRequiredNeighbourToast(draggedData);
                return;
            }

            event.dataTransfer.dropEffect = 'move';
            cell.classList.add('bg-indigo-50', 'border-indigo-400');
        });

        cell.addEventListener('dragleave', () => {
            cell.classList.remove(
                'bg-indigo-50',
                'border-indigo-400',
                'bg-red-50',
                'border-red-400'
            );
        });

        cell.addEventListener('drop', (event) => {
            event.preventDefault();

            cell.classList.remove(
                'bg-indigo-50',
                'border-indigo-400',
                'bg-red-50',
                'border-red-400'
            );

            const payload = getDropPayload(event);
            if (!payload) return;

            if (isApprovedCell(cell)) {
                droppedOnGrid = true;
                approvedMessage();
                sourceCell = null;
                draggedData = null;
                return;
            }

            const violations = conditionViolations(
                proposedGridState(cell, payload, sourceCell)
            );

            if (violations.length > 0) {
                droppedOnGrid = false;
                setConditionStatus(`Placement blocked: ${violations[0]}`, 'error');
                return;
            }

            droppedOnGrid = true;

            if (payload.type === 'facility') {
                const requiredNeighbourViolations = requiredNeighbourViolationsForPlacement(cell, payload);

                if (requiredNeighbourViolations.length > 0) {
                    showRequiredNeighbourViolation(requiredNeighbourViolations[0]);
                    sourceCell = null;
                    draggedData = null;
                    return;
                }

                const conflicts = findRestrictionConflicts(cell, payload.id);

                if (conflicts.length > 0) {
                    showRestrictionError(conflicts, payload.name);
                    sourceCell = null;
                    draggedData = null;
                    return;
                }
            }

            if (sourceCell && sourceCell !== cell) {
                removeCellContent(sourceCell);
            }

            fillCell(cell, payload);

            sourceCell = null;
            draggedData = null;
        });

        cell.addEventListener('mouseenter', (event) => {
            if (isTouchDevice()) return;

            if (cell.dataset.itemId) {
                showCellTooltip(cell, event.clientX, event.clientY);
            }
        });

        cell.addEventListener('mousemove', (event) => {
            if (isTouchDevice()) return;
            if (!cell.dataset.itemId) return;

            const tooltip = getOrCreateTooltip();

            if (!tooltip.classList.contains('hidden')) {
                positionTooltip(tooltip, event.clientX, event.clientY);
            }
        });

        cell.addEventListener('mouseleave', () => {
            if (isTouchDevice()) return;

            hideCellTooltip();
        });

        // ── Mobile: tap to show, tap same cell to hide ─────────────────────
        // The touch drag polyfill needs the original touch event to drag
        // filled cells out of the grid.
        cell.addEventListener('touchstart', (event) => {
            if (!cell.dataset.itemId || !event.touches[0]) return;

            const touch = event.touches[0];

            touchTapState = {
                cell,
                x: touch.clientX,
                y: touch.clientY,
                startedAt: Date.now(),
                moved: false,
            };

            hideCellTooltip();
        }, { passive: true });

        cell.addEventListener('touchmove', (event) => {
            if (!touchTapState || touchTapState.cell !== cell || !event.touches[0]) return;

            const touch = event.touches[0];
            const moved = Math.abs(touch.clientX - touchTapState.x) > 8
                || Math.abs(touch.clientY - touchTapState.y) > 8;

            if (moved) {
                touchTapState.moved = true;
            }
        }, { passive: true });

        cell.addEventListener('touchend', () => {
            if (!touchTapState || touchTapState.cell !== cell) return;

            const wasTap = !touchTapState.moved
                && Date.now() - touchTapState.startedAt < 350;
            const x = touchTapState.x;
            const y = touchTapState.y;

            touchTapState = null;

            if (!wasTap) return;

            if (activeTooltip === cell) {
                hideCellTooltip();
            } else {
                showCellTooltip(cell, x, y);
            }
        }, { passive: true });
    });
}

function bindDropOutsideGrid() {
    document.addEventListener('dragover', (event) => {
        if (!sourceCell) return;
        if (event.target.closest('.grid-cell')) return;

        event.preventDefault();
    });

    document.addEventListener('drop', (event) => {
        if (!sourceCell) return;
        if (event.target.closest('.grid-cell')) return;

        if (isApprovedCell(sourceCell)) {
            event.preventDefault();
            approvedMessage();
            sourceCell = null;
            draggedData = null;
            droppedOnGrid = false;
            hideCellTooltip();
            return;
        }

        event.preventDefault();

        const state = currentGridState();
        state.delete(Number(sourceCell.dataset.index));
        const violations = conditionViolations(state);

        if (violations.length > 0) {
            setConditionStatus(`Removal blocked: ${violations[0]}`, 'error');
            return;
        }

        removeCellContent(sourceCell);
        sourceCell = null;
        draggedData = null;
        droppedOnGrid = false;
        hideCellTooltip();
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
        let blockedApprovedCell = false;

        document.querySelectorAll('.grid-cell').forEach((cell) => {
            if (isApprovedCell(cell)) {
                blockedApprovedCell = true;
                return;
            }

            removeCellContent(cell);
        });

        if (blockedApprovedCell) {
            approvedMessage();
        }
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

function getEventStatus() {
    const names = activeEventNames();

    return names.length > 0
        ? names.join(', ')
        : 'No dragged event active at simulation time';
}

function exportToPDF() {
    if (!window.jspdf) {
        alert('PDF library not loaded. Please refresh the page and try again.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });

    const pageWidth = 210;
    const pageHeight = 297;
    const margin = 20;
    const usableWidth = pageWidth - margin * 2;

    function ensurePdfSpace(y, needed = 12) {
        if (y + needed <= pageHeight - margin) {
            return y;
        }

        doc.addPage();
        doc.setFontSize(8);
        doc.setTextColor(150, 150, 150);
        doc.text('Metropolis City Simulation', margin, 287);
        doc.setTextColor(0, 0, 0);

        return margin;
    }

    doc.setFontSize(22);
    doc.setFont(undefined, 'bold');
    doc.text('Metropolis Simulation Report', margin, 24);

    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(120, 120, 120);
    doc.text('Exported: ' + new Date().toLocaleString(), margin, 32);
    doc.setTextColor(0, 0, 0);

    doc.setDrawColor(220, 220, 220);
    doc.line(margin, 37, pageWidth - margin, 37);

    doc.setFontSize(13);
    doc.setFont(undefined, 'bold');
    doc.text('Simulation Info', margin, 46);

    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');

    const evaluationDateTime = selectedSimulationDateTime();
    const simText = evaluationDateTime ? evaluationDateTime.toLocaleString() : 'Not started';
    const modeText = evaluationDateTime ? (isNightTime(evaluationDateTime) ? 'Night Mode' : 'Day Mode') : 'N/A';
    const eventText = getEventStatus();

    doc.text('Date & Time:   ' + simText, margin, 55);
    doc.text('Mode:          ' + modeText, margin, 63);
    doc.text('Active Event:  ' + eventText, margin, 71);

    doc.setDrawColor(220, 220, 220);
    doc.line(margin, 78, pageWidth - margin, 78);

    doc.setFontSize(13);
    doc.setFont(undefined, 'bold');
    doc.text('City Grid', margin, 86);

    const cells = Array.from(document.querySelectorAll('.grid-cell'));
    const gridCols = 4;
    const gridRows = 3;
    const cellW = 38;
    const cellH = 26;
    const gridStartX = margin + (usableWidth - gridCols * cellW) / 2;
    const gridStartY = 91;

    cells.forEach((cell, i) => {
        const col = i % gridCols;
        const row = Math.floor(i / gridCols);
        const x = gridStartX + col * cellW;
        const y = gridStartY + row * cellH;
        const itemId = cell.dataset.itemId;
        const itemType = cell.dataset.itemType;

        if (isApprovedCell(cell)) {
            doc.setFillColor(220, 252, 231);
            doc.setDrawColor(22, 163, 74);
        } else if (itemId) {
            if (itemType === 'event') {
                doc.setFillColor(254, 243, 199);
                doc.setDrawColor(217, 119, 6);
            } else {
                doc.setFillColor(219, 234, 254);
                doc.setDrawColor(99, 102, 241);
            }
        } else {
            doc.setFillColor(249, 250, 251);
            doc.setDrawColor(209, 213, 219);
        }

        doc.roundedRect(x, y, cellW, cellH, 1.5, 1.5, 'FD');

        const itemName = cell.getAttribute('aria-label');

        if (itemName) {
            doc.setFontSize(7);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(30, 64, 175);

            const label = isApprovedCell(cell) ? `${itemName} (approved)` : itemName;
            const lines = doc.splitTextToSize(label, cellW - 6);
            const lineHeight = 3.5;
            const textBlockHeight = lines.length * lineHeight;
            const textY = y + (cellH - textBlockHeight) / 2 + lineHeight;

            doc.text(lines, x + cellW / 2, textY, { align: 'center' });
            doc.setFont(undefined, 'normal');
            doc.setTextColor(0, 0, 0);
        } else {
            doc.setFontSize(7);
            doc.setTextColor(180, 180, 180);
            doc.text(String(i + 1), x + cellW / 2, y + cellH / 2 + 1.5, { align: 'center' });
            doc.setTextColor(0, 0, 0);
        }
    });

    const effectsStartY = gridStartY + gridRows * cellH + 12;

    doc.setDrawColor(220, 220, 220);
    doc.line(margin, effectsStartY - 4, pageWidth - margin, effectsStartY - 4);

    doc.setFontSize(13);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(0, 0, 0);
    doc.text('Simulation Effects', margin, effectsStartY + 4);

    let scoreY = effectsStartY + 14;

    effectCategories.forEach((category) => {
        const scoreEl = document.getElementById(`effect-category-score-${category.id}`);
        const scoreText = scoreEl ? scoreEl.textContent.trim() : '0';
        const scoreNum = parseInt(scoreText, 10) || 0;

        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(50, 50, 50);
        doc.text(category.name, margin, scoreY);

        if (scoreNum > 0) doc.setTextColor(21, 128, 61);
        else if (scoreNum < 0) doc.setTextColor(185, 28, 28);
        else doc.setTextColor(107, 114, 128);

        doc.text(scoreText, margin + usableWidth, scoreY, { align: 'right' });
        doc.setTextColor(0, 0, 0);

        doc.setDrawColor(240, 240, 240);
        doc.line(margin, scoreY + 2.5, pageWidth - margin, scoreY + 2.5);

        scoreY += 9;
    });

    scoreY += 3;
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(0, 0, 0);
    doc.text('Total Score', margin, scoreY);

    const totalEl = document.getElementById('effect-total-score');
    const totalText = totalEl ? totalEl.textContent.trim() : '0';
    const totalNum = parseInt(totalText, 10) || 0;

    if (totalNum > 0) doc.setTextColor(21, 128, 61);
    else if (totalNum < 0) doc.setTextColor(185, 28, 28);
    else doc.setTextColor(107, 114, 128);

    doc.text(totalText, margin + usableWidth, scoreY, { align: 'right' });
    doc.setTextColor(0, 0, 0);

    scoreY += 14;
    scoreY = ensurePdfSpace(scoreY, 40);
    doc.setDrawColor(220, 220, 220);
    doc.line(margin, scoreY - 5, pageWidth - margin, scoreY - 5);

    doc.setFontSize(13);
    doc.setFont(undefined, 'bold');
    doc.text('Event Effects', margin, scoreY);
    scoreY += 9;

    const eventSelections = activeSelectedEvents();
    const eventTotals = eventCategoryTotals();

    if (eventSelections.length === 0) {
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(107, 114, 128);
        doc.text('No events placed on the grid.', margin, scoreY);
        doc.setTextColor(0, 0, 0);
    } else {
        doc.setFontSize(11);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(0, 0, 0);
        doc.text('Event Category Totals', margin, scoreY);
        scoreY += 8;

        effectCategories.forEach((category) => {
            scoreY = ensurePdfSpace(scoreY, 8);

            const score = Number(eventTotals[category.id] ?? 0);

            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(50, 50, 50);
            doc.text(category.name, margin + 4, scoreY);

            if (score > 0) doc.setTextColor(21, 128, 61);
            else if (score < 0) doc.setTextColor(185, 28, 28);
            else doc.setTextColor(107, 114, 128);

            doc.text(formatScore(score), margin + usableWidth, scoreY, { align: 'right' });
            scoreY += 6;
        });

        scoreY += 8;
        scoreY = ensurePdfSpace(scoreY, 14);
        doc.setFontSize(11);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(0, 0, 0);
        doc.text('Placed Events', margin, scoreY);
        scoreY += 8;

        eventSelections.forEach(({ event, cellIndex, active }) => {
            scoreY = ensurePdfSpace(scoreY, 14 + ((event.impacts || []).length * 5));

            const score = active
                ? (event.impacts || []).reduce((sum, impact) => sum + Number(impact.score ?? 0), 0)
                : 0;

            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text(`${event.name} (cell ${cellIndex})`, margin, scoreY);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(active ? 21 : 107, active ? 128 : 114, active ? 61 : 128);
            doc.text(active ? `total ${formatScore(score)}` : 'inactive', margin + usableWidth, scoreY, { align: 'right' });

            scoreY += 6;
            doc.setTextColor(80, 80, 80);

            (event.impacts || []).forEach((impact) => {
                scoreY = ensurePdfSpace(scoreY, 6);

                const impactScore = active ? Number(impact.score ?? 0) : 0;

                doc.setTextColor(80, 80, 80);
                doc.text(`- ${impact.category_name}`, margin + 4, scoreY);

                if (impactScore > 0) doc.setTextColor(21, 128, 61);
                else if (impactScore < 0) doc.setTextColor(185, 28, 28);
                else doc.setTextColor(107, 114, 128);

                doc.text(formatScore(impactScore), margin + usableWidth, scoreY, { align: 'right' });
                scoreY += 5;
            });

            scoreY += 3;
        });
    }

    doc.setFontSize(8);
    doc.setTextColor(150, 150, 150);
    doc.text('Metropolis City Simulation', margin, 287);
    doc.text('Page 1', pageWidth - margin, 287, { align: 'right' });

    const dateStr = evaluationDateTime
        ? evaluationDateTime.toISOString().slice(0, 10)
        : new Date().toISOString().slice(0, 10);

    doc.save(`simulation-report-${dateStr}.pdf`);
}

function bindExportButton() {
    document.getElementById('export-pdf')?.addEventListener('click', exportToPDF);
}


document.addEventListener('DOMContentLoaded', () => {
    hydrateSimulationMoment();
    restoreApprovedCellsFromStorage();
    bindLibraryItems();
    bindEventItems();
    bindGridCells();
    bindDropOutsideGrid();
    bindSearch();
    bindClearButton();
    bindOutsideTap();
    bindExportButton();
    bindSimulationSettings();
    bindSimulationSpeedControls();
    updateEffectView();
    updateEventEffectView();
    updateUpcomingEventList();
});

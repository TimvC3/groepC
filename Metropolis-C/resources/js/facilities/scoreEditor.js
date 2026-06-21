
export function initScoreEditor() {
    document.querySelectorAll('[data-score-id]').forEach((badge) => {
        badge.addEventListener('click', () => openEditor(badge));
    });
}

const approvedDestinationMessage = 'This destination has already been approved and its effects can no longer be changed.';

const scoreColorClasses = [
    'bg-green-100',
    'text-green-800',
    'dark:bg-green-900/40',
    'dark:text-green-300',
    'bg-red-100',
    'text-red-600',
    'dark:bg-red-900/40',
    'dark:text-red-300',
    'text-gray-500',
    'dark:bg-gray-700',
    'dark:text-gray-400',
];

function openEditor(badge) {
    if (badge.dataset.approvedDestination === 'true') {
        showToast(approvedDestinationMessage, 'error', badge);
        return;
    }

    // Avoid opening multiple editors on the same badge.
    if (badge.querySelector('input')) return;

    const currentScore = parseInt(badge.dataset.score, 10);
    const scoreId      = badge.dataset.scoreId;

    // Replace the badge content with an input.
    while (badge.firstChild) badge.removeChild(badge.firstChild);
    badge.classList.remove(...scoreColorClasses);
    badge.classList.add('bg-white', 'text-gray-900', 'ring-2', 'ring-indigo-400');

    const input = document.createElement('input');
    input.type      = 'number';
    input.min       = '-5';
    input.max       = '5';
    input.value     = currentScore;
    input.className = 'rounded border border-gray-300 text-center text-sm font-bold outline-none';
    input.style.cssText = [
        'width: 3rem',
        'height: 1.75rem',
        'background-color: #ffffff',
        'color: #111827',
        'caret-color: #111827',
        'border-color: #d1d5db',
        'padding: 0',
        'line-height: 1.25rem',
        'opacity: 1',
        '-webkit-text-fill-color: #111827',
    ].join(';');

    let handled = false;

    badge.appendChild(input);
    input.focus();
    input.select();

    // Save on Enter or blur.
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') input.blur();
        if (e.key === 'Escape') {
            e.stopPropagation();
            handled = true;
            e.preventDefault();
            input.blur();

            requestAnimationFrame(() => {
                restoreBadge(badge, currentScore);
            });
        }
    });

    input.addEventListener('blur', () => {
        if (handled) return;
            handled = true;

            const newScore = parseInt(input.value, 10);

            if (isNaN(newScore) || newScore < -5 || newScore > 5) {
                requestAnimationFrame(() => restoreBadge(badge, currentScore));
                showToast('The score must be between -5 and 5.', 'error', badge);
                return;
            }

            if (newScore === currentScore) {
                requestAnimationFrame(() => restoreBadge(badge, currentScore));
                return;
            }

            saveScore(badge, scoreId, newScore, currentScore);
        });
}

async function saveScore(badge, scoreId, newScore, oldScore) {
    // Loading state.
    badge.classList.add('opacity-50');

    try {
        const response = await fetch(`/facilities/scores/${scoreId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ score: newScore }),
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Saving failed, please try again.');
        }

        restoreBadge(badge, newScore);
        badge.dataset.score = newScore;

    } catch (error) {
        restoreBadge(badge, oldScore);
        showToast(error.message || 'Saving failed, please try again.', 'error', badge);
    } finally {
        badge.classList.remove('opacity-50');
    }
}

function restoreBadge(badge, score) {
    badge.classList.remove('bg-white', 'text-gray-900', 'ring-2', 'ring-indigo-400');

    badge.classList.remove(...scoreColorClasses);

    if (score > 0) {
        badge.classList.add('bg-green-100', 'text-green-800', 'dark:bg-green-900/40', 'dark:text-green-300');
    } else if (score < 0) {
        badge.classList.add('bg-red-100', 'text-red-600', 'dark:bg-red-900/40', 'dark:text-red-300');
    } else {
        badge.classList.add('text-gray-500', 'dark:bg-gray-700', 'dark:text-gray-400');
    }

    while (badge.firstChild) badge.removeChild(badge.firstChild);
    badge.appendChild(
        document.createTextNode(score > 0 ? `+${score}` : String(score))
    );
}

function showToast(message, type = 'info', anchor = null) {
    document.getElementById('score-editor-toast')?.remove();

    const toast = document.createElement('div');
    toast.id = 'score-editor-toast';
    toast.textContent = message;
    toast.setAttribute('role', 'alert');
    toast.className = 'px-4 py-2 rounded-lg text-sm font-medium shadow-lg';
    toast.style.cssText = [
        'position: fixed',
        'right: 1rem',
        'bottom: 1rem',
        'z-index: 9999',
        'max-width: calc(100vw - 2rem)',
        'background: ' + (type === 'error' ? '#fee2e2' : '#dcfce7'),
        'color: ' + (type === 'error' ? '#991b1b' : '#166534'),
        'border: 1px solid ' + (type === 'error' ? '#fecaca' : '#bbf7d0'),
    ].join(';');

    document.body.appendChild(toast);

    if (anchor) {
        const anchorRect = anchor.getBoundingClientRect();
        const toastRect = toast.getBoundingClientRect();
        const top = Math.max(8, anchorRect.top - toastRect.height - 8);
        const left = Math.min(
            window.innerWidth - toastRect.width - 8,
            Math.max(8, anchorRect.left + anchorRect.width / 2 - toastRect.width / 2)
        );

        toast.style.top = `${top}px`;
        toast.style.left = `${left}px`;
        toast.style.right = 'auto';
        toast.style.bottom = 'auto';
    }

    setTimeout(() => toast.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    initScoreEditor();
});

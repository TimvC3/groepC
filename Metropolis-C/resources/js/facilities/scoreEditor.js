export function initScoreEditor() {
    refreshScoreBadges();

    document.querySelectorAll('[data-score]').forEach((badge) => {
        if (!badge.dataset.scoreId) {
            return;
        }

        if (badge.dataset.scoreEditorBound === 'true') {
            return;
        }

        badge.dataset.scoreEditorBound = 'true';
        badge.addEventListener('click', () => openEditor(badge));
    });
}

const approvedDestinationMessage = 'This destination has already been approved and its effects can no longer be changed.';
function refreshScoreBadges() {
    document.querySelectorAll('[data-score]').forEach((badge) => {
        const score = parseInt(badge.dataset.score, 10);

        if (!Number.isNaN(score)) {
            restoreBadge(badge, score);
        }
    });
}

window.addEventListener('accessibility:colorblind-mode-changed', refreshScoreBadges);

const scoreColorClasses = [
    'inline-flex',
    'min-w-10',
    'items-center',
    'justify-center',
    'gap-1',
    'rounded-md',
    'rounded-full',
    'px-2',
    'px-2.5',
    'py-1',
    'py-0.5',
    'text-xs',
    'font-semibold',
    'font-bold',
    'font-extrabold',
    'cursor-pointer',
    'select-none',
    'shadow-sm',

    'bg-green-100',
    'text-green-800',
    'text-green-700',
    'dark:bg-green-900/40',
    'dark:bg-green-900/30',
    'dark:text-green-300',

    'bg-red-100',
    'text-red-600',
    'text-red-700',
    'dark:bg-red-900/40',
    'dark:bg-red-900/30',
    'dark:text-red-300',

    'bg-gray-100',
    'text-gray-500',
    'text-gray-600',
    'dark:bg-gray-700',
    'dark:text-gray-300',
    'dark:text-gray-400',

    'border',
    'border-2',
    'border-green-200',
    'border-red-200',
    'border-gray-200',
    'border-sky-950',
    'border-orange-950',
    'border-gray-900',

    'bg-white',
    'bg-sky-950',
    'bg-orange-900',
    'bg-gray-900',

    'text-white',
    'text-sky-950',
    'text-orange-950',
    'text-gray-950',
];

function openEditor(badge) {
    if (badge.dataset.approvedDestination === 'true') {
        showToast(approvedDestinationMessage, 'error', badge);
    }
    if (!badge.dataset.scoreId) {
        return;
    }

    // Avoid opening multiple editors on the same badge.
    if (badge.querySelector('input')) return;

    const currentScore = parseInt(badge.dataset.score, 10);
    const scoreId      = badge.dataset.scoreId;

    // Replace the badge content with an input.
   while (badge.firstChild) badge.removeChild(badge.firstChild);

    badge.className = [
        'inline-flex',
        'h-12',
        'w-12',
        'items-center',
        'justify-center',
        'rounded-full',
        'bg-white',
        'p-0',
        'text-xs',
        'font-bold',
        'text-gray-950',
        'ring-2',
        'ring-indigo-500',
    ].join(' ');

    const input = document.createElement('input');
    input.type      = 'number';
    input.min       = '-5';
    input.max       = '5';
    input.value     = currentScore;
    input.className = 'rounded border border-gray-300 text-center text-sm font-bold outline-none';
    input.style.cssText = [
        'width: 2.25rem',
        'height: 2rem',
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
        const response = await fetch(`/functions/scores/${scoreId}`, {
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

function isColorblindModeEnabled() {
    return document.documentElement.classList.contains('colorblind-mode');
}

function formatScore(score) {
    return score > 0 ? `+${score}` : String(score);
}

function scoreIcon(score) {
    if (score > 0) return '▲';
    if (score < 0) return '▼';

    return '•';
}

function scoreBadgeClasses(score) {
    const baseClasses = [
        'inline-flex',
        'h-12',
        'w-12',
        'items-center',
        'justify-center',
        'gap-0.5',
        'rounded-full',
        'p-0',
        'text-[11px]',
        'font-extrabold',
        'cursor-pointer',
        'select-none',
        'shadow-sm',
    ];

    if (isColorblindModeEnabled()) {
        if (score > 0) {
            return [
                ...baseClasses,
                'border-2',
                'border-sky-950',
                'bg-sky-950',
                'text-white',
            ];
        }

        if (score < 0) {
            return [
                ...baseClasses,
                'border-2',
                'border-orange-950',
                'bg-orange-900',
                'text-white',
            ];
        }

        return [
            ...baseClasses,
            'border-2',
            'border-gray-900',
            'bg-gray-900',
            'text-white',
        ];
    }

    if (score > 0) {
        return [
            ...baseClasses,
            'bg-green-100',
            'text-green-800',
            'dark:bg-green-900/40',
            'dark:text-green-300',
        ];
    }

    if (score < 0) {
        return [
            ...baseClasses,
            'bg-red-100',
            'text-red-600',
            'dark:bg-red-900/40',
            'dark:text-red-300',
        ];
    }

    return [
        ...baseClasses,
        'bg-gray-100',
        'text-gray-600',
        'dark:bg-gray-700',
        'dark:text-gray-300',
    ];
}

function restoreBadge(badge, score) {
    badge.className = scoreBadgeClasses(score).join(' ');

    while (badge.firstChild) {
        badge.removeChild(badge.firstChild);
    }

    badge.appendChild(
        document.createTextNode(`${scoreIcon(score)} ${formatScore(score)}`)
    );
}

function showToast(message, type = 'info', anchor = null) {
    document.getElementById('score-editor-toast')?.remove();

    const colorblindMode = document.documentElement.classList.contains('colorblind-mode');

    const baseClasses = [
        'fixed',
        'right-4',
        'bottom-4',
        'z-[9999]',
        'max-w-[calc(100vw-2rem)]',
        'rounded-lg',
        'border',
        'px-4',
        'py-2',
        'text-sm',
        'font-medium',
        'shadow-lg',
    ].join(' ');

    const colorClasses = type === 'error'
        ? colorblindMode
            ? 'border-2 border-orange-700 bg-white text-orange-950'
            : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300'
        : colorblindMode
            ? 'border-2 border-sky-700 bg-white text-sky-950'
            : 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300';

    const toast = document.createElement('div');
    toast.id = 'score-editor-toast';
    toast.textContent = `${type === 'error' ? '!' : '✓'} ${message}`;
    toast.setAttribute('role', 'alert');
    toast.className = `${baseClasses} ${colorClasses}`;

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

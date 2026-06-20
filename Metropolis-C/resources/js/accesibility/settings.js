document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('accessibilitySettingsButton');
    const menu = document.getElementById('accessibilitySettingsMenu');

    const colorblindMode = document.getElementById('colorblindMode');
    const largeTextMode = document.getElementById('largeTextMode');
    const highContrastMode = document.getElementById('highContrastMode');

    const colorblindStorageKey = 'accessibility.colorblindMode';
    const largeTextStorageKey = 'accessibility.largeTextMode';
    const highContrastStorageKey = 'accessibility.highContrastMode';

    function applyColorblindMode(isEnabled) {
        document.documentElement.classList.toggle('colorblind-mode', isEnabled);

        if (colorblindMode) {
            colorblindMode.checked = isEnabled;
        }

        localStorage.setItem(colorblindStorageKey, isEnabled ? 'true' : 'false');
    }

    function applyLargeTextMode(isEnabled) {
        document.documentElement.classList.toggle('text-lg', isEnabled);

        if (largeTextMode) {
            largeTextMode.checked = isEnabled;
        }

        localStorage.setItem(largeTextStorageKey, isEnabled ? 'true' : 'false');
    }

    function applyHighContrastMode(isEnabled) {
        document.documentElement.classList.toggle('bg-gray-950', isEnabled);
        document.documentElement.classList.toggle('text-white', isEnabled);

        if (highContrastMode) {
            highContrastMode.checked = isEnabled;
        }

        localStorage.setItem(highContrastStorageKey, isEnabled ? 'true' : 'false');
    }

    applyColorblindMode(localStorage.getItem(colorblindStorageKey) === 'true');
    applyLargeTextMode(localStorage.getItem(largeTextStorageKey) === 'true');
    applyHighContrastMode(localStorage.getItem(highContrastStorageKey) === 'true');

    if (!button || !menu) {
        return;
    }

    button.addEventListener('click', () => {
        const isHidden = menu.classList.toggle('hidden');

        button.setAttribute('aria-expanded', String(!isHidden));
    });

    document.addEventListener('click', (event) => {
        if (
            !menu.classList.contains('hidden')
            && !menu.contains(event.target)
            && !button.contains(event.target)
        ) {
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
            button.focus();
        }
    });

    colorblindMode?.addEventListener('change', () => {
        applyColorblindMode(colorblindMode.checked);
    });

    largeTextMode?.addEventListener('change', () => {
        applyLargeTextMode(largeTextMode.checked);
    });

    highContrastMode?.addEventListener('change', () => {
        applyHighContrastMode(highContrastMode.checked);
    });
});

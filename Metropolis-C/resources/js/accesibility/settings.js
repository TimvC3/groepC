document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('accessibilitySettingsButton');
    const menu = document.getElementById('accessibilitySettingsMenu');
    const colorblindMode = document.getElementById('colorblindMode');

    const userId = document
        .querySelector('meta[name="auth-user-id"]')
        ?.getAttribute('content') || 'guest';

    const colorblindStorageKey = `accessibility.colorblindMode.${userId}`;

    // Oude algemene keys opruimen, zodat eerdere testinstellingen niet blijven hangen.
    localStorage.removeItem('accessibility.colorblindMode');
    localStorage.removeItem('accessibility.largeTextMode');
    localStorage.removeItem('accessibility.highContrastMode');

    // Oude user-specific keys van verwijderde opties opruimen.
    localStorage.removeItem(`accessibility.largeTextMode.${userId}`);
    localStorage.removeItem(`accessibility.highContrastMode.${userId}`);

    function applyColorblindMode(isEnabled) {
        document.documentElement.classList.toggle('colorblind-mode', isEnabled);

        if (colorblindMode) {
            colorblindMode.checked = isEnabled;
        }

        localStorage.setItem(colorblindStorageKey, isEnabled ? 'true' : 'false');

        window.dispatchEvent(new CustomEvent('accessibility:colorblind-mode-changed', {
            detail: {
                enabled: isEnabled,
            },
        }));
    }

    applyColorblindMode(localStorage.getItem(colorblindStorageKey) === 'true');

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
});
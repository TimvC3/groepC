document.addEventListener('DOMContentLoaded', () => {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('iconPreview');
    const openIconPickerButton = document.getElementById('openIconPicker');
    const iconPicker = document.getElementById('iconPicker');
    const iconOptions = document.querySelectorAll('.icon-option');

    if (!iconInput || !iconPreview || !openIconPickerButton || !iconPicker) {
        return;
    }

    openIconPickerButton.addEventListener('click', () => {
        iconPicker.classList.toggle('hidden');
    });

    iconOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const selectedIcon = option.textContent.trim();

            iconInput.value = selectedIcon;
            iconPreview.textContent = selectedIcon;
            iconPicker.classList.add('hidden');
        });
    });

    iconInput.addEventListener('input', () => {
        iconPreview.textContent = iconInput.value;
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const categoryInput = document.getElementById('category');
    const iconInput = document.getElementById('icon');

    const previewName = document.getElementById('preview-name');
    const previewSlug = document.getElementById('preview-slug');
    const previewCategory = document.getElementById('preview-category');
    const previewIcon = document.getElementById('preview-icon');

    if (
        !nameInput ||
        !slugInput ||
        !categoryInput ||
        !iconInput ||
        !previewName ||
        !previewSlug ||
        !previewCategory ||
        !previewIcon
    ) {
        return;
    }

    function updatePreview() {
        previewName.textContent = nameInput.value.trim() || 'Function name';
        previewSlug.textContent = slugInput.value.trim() || 'function-slug';
        previewCategory.textContent = categoryInput.value.trim() || 'Category';
        previewIcon.textContent = iconInput.value.trim() || '🏙️';
    }

    nameInput.addEventListener('input', updatePreview);
    slugInput.addEventListener('input', updatePreview);
    categoryInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('input', updatePreview);

    updatePreview();
});
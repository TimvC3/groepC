document.addEventListener('DOMContentLoaded', () => {
    const cells = document.querySelectorAll('.cell');

    cells.forEach(cell => {
        cell.addEventListener('click', () => {
            const isSelected = cell.classList.toggle('selected');

            cell.classList.toggle('bg-blue-500', isSelected);
            cell.classList.toggle('text-white', isSelected);
            cell.classList.toggle('border-blue-500', isSelected);

            cell.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });
    });
});

    
document.addEventListener('DOMContentLoaded', () => {
    const cells = document.querySelectorAll('.cell');

    cells.forEach(cell => {
        cell.addEventListener('click', () => {
            cell.classList.toggle('bg-blue-500');
            cell.classList.toggle('text-white');
            cell.classList.toggle('border-blue-500');
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.table tbody tr');
    const dateGroups = new Map();
    const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
    let colorIndex = 0;

    rows.forEach(row => {
        // Trouvez la cellule contenant la date (ajustez le sélecteur selon votre structure)
        const dateCell = row.querySelector('td[data-column="dateDebut"]');

        if (dateCell) {
            const dateText = dateCell.textContent.trim();

            if (dateText && dateText !== '') {
                if (!dateGroups.has(dateText)) {
                    dateGroups.set(dateText, colors[colorIndex % colors.length]);
                    colorIndex++;
                }

                row.style.borderLeft = `4px solid ${dateGroups.get(dateText)}`;
            }
        }
    });
});

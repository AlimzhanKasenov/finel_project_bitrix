document.addEventListener("DOMContentLoaded", function () {
    setInterval(function () {
        fetch('/local/components/custom/carriage.schedule/ajax_update.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('carriage-table-body').innerHTML = data;
            })
            .catch(error => console.error('Error updating table:', error));
    }, 15000);
});
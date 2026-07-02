new Chart(document.getElementById('sectorChart'), {
    type: 'pie',
    data: {
        labels: sectorLabels,
        datasets: [{
            data: sectorDatos,
            backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796']
        }]
    },
    options: { plugins: { title: { display: true, text: 'Usuarios por Sector' } } }
});


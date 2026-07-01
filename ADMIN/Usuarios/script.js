const PagosChart = Document.getElementById('pagosChart');

new Chart(PagosChart,{
    type: 'bar',
    data:{
        labels: labels,
        datasets: [{
            label:'Pagos por Mes ',
            data: datos,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor:'rgba(54,162.235,1)',
            borderWidth:1
        }]
    },
    options:{
        responsive: true,
        scales:{
            y:{
                beginAtZero: true,
                title:{
                    display: true,
                    text:'monto en Lempiras'
                }
            },
            x: {
                title:{
                    display: true,
                    text:'Meses'
                }
            }
        }
    }
});
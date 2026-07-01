const ventas = document.getElementById('ventasChart');

new Chart(ventas, {

    type: 'bar',

    data: {

        labels: labels,

        datasets: [{

            label: 'Ventas por Producto',

            data: datos,

            borderWidth: 1

        }]

    }

});
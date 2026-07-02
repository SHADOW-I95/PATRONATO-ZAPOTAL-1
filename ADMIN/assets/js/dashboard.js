// Crea un nuevo gráfico utilizando la librería Chart.js.
// document.getElementById('sectorChart') obtiene el elemento <canvas>
// donde se dibujará el gráfico.
new Chart(document.getElementById('sectorChart'), {

    // Define el tipo de gráfico.
    // En este caso será un gráfico de pastel (Pie Chart).
    type: 'pie',

    // Contiene toda la información que mostrará el gráfico.
    data: {

        // Etiquetas que aparecerán junto a cada porción del gráfico.
        // Ejemplo: Norte, Sur, Centro, Este...
        labels: sectorLabels,

        // Conjunto de datos que utilizará el gráfico.
        datasets: [{

            // Valores numéricos correspondientes a cada sector.
            // Ejemplo: 20, 15, 35, 10...
            data: sectorDatos,

            // Colores que tendrán las diferentes porciones del gráfico.
            backgroundColor: [
                '#4e73df', // Azul
                '#1cc88a', // Verde
                '#36b9cc', // Celeste
                '#f6c23e', // Amarillo
                '#e74a3b', // Rojo
                '#858796'  // Gris
            ]

        }]

    },

    // Configuración adicional del gráfico.
    options: {

        // Configuración de los complementos (plugins).
        plugins: {

            // Configuración del título.
            title: {

                // Hace visible el título.
                display: true,

                // Texto que aparecerá encima del gráfico.
                text: 'Usuarios por Sector'

            }

        }

    }

});
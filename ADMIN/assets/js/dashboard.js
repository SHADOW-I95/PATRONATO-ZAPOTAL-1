const sectorLabels = <?= json_encode($sectorLabels) ?>;
const sectorDatos  = <?= json_encode($sectorDatos) ?>;
const mesLabels    = <?= json_encode($mesLabels) ?>;
const mesDatos     = <?= json_encode($mesDatos) ?>;

new Chart(document.getElementById("sectorChart"), {
    type: "pie",
    data: {
        labels: sectorLabels,
        datasets: [{
            data: sectorDatos,
            backgroundColor: ["#3700ff", "#33ff00", "#fbbf24", "#ff0000", "#3f00fd", "#f472b6"]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById("mesChart"), {
    type: "line",
    data: {
        labels: mesLabels,
        datasets: [{
            label: "Recaudado (L)",
            data: mesDatos,
            borderColor: "#34d399",
            backgroundColor: "rgba(52, 211, 153, 0.15)",
            fill: true,
            tension: 0.3,
            pointBackgroundColor: "#34d399",
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
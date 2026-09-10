var serviceLabels = @json($serviceLabels);
var serviceCounts = @json($serviceCounts);
var maireAdjointLabels = @json($maireAdjointLabels);
var maireAdjointCounts = @json($maireAdjointCounts);
var conseillerLabels = @json($conseillerLabels);
var conseillerCounts = @json($conseillerCounts);

function renderWorkloadChart(canvasId, labels, data) {
    var el = document.getElementById(canvasId);
    if (!el) { return; }
    new Chart(el, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Demandes',
                data: data,
                backgroundColor: '#2CA8FF',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });
}

renderWorkloadChart('serviceChart', serviceLabels, serviceCounts);
renderWorkloadChart('maireAdjointChart', maireAdjointLabels, maireAdjointCounts);
renderWorkloadChart('conseillerChart', conseillerLabels, conseillerCounts);

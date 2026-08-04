document.addEventListener('DOMContentLoaded', function () {
    const resourceForm = document.getElementById('resourceForm');
    const resourceList = document.getElementById('resourceList');
    const inventoryChart = document.getElementById('inventoryChart');

    const loadResources = function () {
        fetch('./../api/resources.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!Array.isArray(data)) return;
                if (resourceList) {
                    resourceList.innerHTML = '';
                    data.forEach(function (resource) {
                        const row = document.createElement('tr');
                        row.innerHTML = '<td>' + resource.name + '</td><td>' + resource.category + '</td><td>' + resource.quantity + '</td><td>' + (resource.remaining || 0) + '</td><td>' + resource.location + '</td><td>' + resource.status + '</td>';
                        resourceList.appendChild(row);
                    });
                }
                if (inventoryChart && window.Chart) {
                    const labels = data.map(function (resource) { return resource.name; });
                    const values = data.map(function (resource) { return resource.remaining || 0; });
                    if (window.resourceChartInstance) {
                        window.resourceChartInstance.data.labels = labels;
                        window.resourceChartInstance.data.datasets[0].data = values;
                        window.resourceChartInstance.update();
                        return;
                    }
                    window.resourceChartInstance = new Chart(inventoryChart, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{ label: 'Remaining Quantity', data: values, backgroundColor: '#ff6b35' }]
                        },
                        options: { responsive: true, scales: { y: { beginAtZero: true } } }
                    });
                }
            });
    };

    if (resourceForm) {
        resourceForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(resourceForm);
            const payload = {
                name: formData.get('name'),
                category: formData.get('category'),
                quantity: formData.get('quantity'),
                unit: formData.get('unit'),
                location: formData.get('location'),
                status: formData.get('status')
            };
            fetch('./../api/resources.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function () { loadResources(); resourceForm.reset(); });
        });
    }

    loadResources();
});

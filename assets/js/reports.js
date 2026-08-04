document.addEventListener('DOMContentLoaded', function () {
    const dailyChart = document.getElementById('dailyChart');
    const weeklyChart = document.getElementById('weeklyChart');
    const monthlyChart = document.getElementById('monthlyChart');
    const summaryCards = document.querySelectorAll('[data-summary]');
    const filterForm = document.getElementById('reportFilterForm');

    const renderSummary = function (data) {
        if (summaryCards.length) {
            summaryCards[0].textContent = data.affectedPopulation;
            summaryCards[1].textContent = data.volunteerActivities;
            summaryCards[2].textContent = data.resourceConsumption;
            summaryCards[3].textContent = data.responseTime + ' min';
        }
    };

    const renderCharts = function (data) {
        if (window.Chart) {
            const chartOptions = { responsive: true, maintainAspectRatio: false };
            new Chart(dailyChart, { type: 'line', data: { labels: data.dailyIncidents.map(function (item) { return item.label; }), datasets: [{ label: 'Daily Incidents', data: data.dailyIncidents.map(function (item) { return item.total; }), borderColor: '#ff6b35', tension: 0.3 }] }, options: chartOptions });
            new Chart(weeklyChart, { type: 'bar', data: { labels: data.weeklyIncidents.map(function (item) { return item.label; }), datasets: [{ label: 'Weekly Incidents', data: data.weeklyIncidents.map(function (item) { return item.total; }), backgroundColor: '#153d6d' }] }, options: chartOptions });
            new Chart(monthlyChart, { type: 'bar', data: { labels: data.monthlyIncidents.map(function (item) { return item.label; }), datasets: [{ label: 'Monthly Incidents', data: data.monthlyIncidents.map(function (item) { return item.total; }), backgroundColor: '#1b8f5a' }] }, options: chartOptions });
        }
    };

    const loadReports = function () {
        fetch('./../api/reports.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                renderSummary(data);
                renderCharts(data);
            });
    };

    if (filterForm) {
        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            loadReports();
        });
    }

    loadReports();
});

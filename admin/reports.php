<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);
$pageTitle = 'Operational Reports';
$pageSubtitle = 'Analyse incident trends and response performance.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card p-3"><h6 class="text-muted">Affected Population</h6><h3 class="fw-bold" data-summary="population">0</h3></div></div>
    <div class="col-md-3"><div class="card p-3"><h6 class="text-muted">Volunteer Activities</h6><h3 class="fw-bold" data-summary="volunteer">0</h3></div></div>
    <div class="col-md-3"><div class="card p-3"><h6 class="text-muted">Resource Consumption</h6><h3 class="fw-bold" data-summary="resources">0</h3></div></div>
    <div class="col-md-3"><div class="card p-3"><h6 class="text-muted">Response Time</h6><h3 class="fw-bold" data-summary="response">0</h3></div></div>
</div>
<div class="card p-4 mb-4">
    <form id="reportFilterForm" class="row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date"></div>
        <div class="col-md-3"><label class="form-label">End Date</label><input type="date" class="form-control" name="end_date"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Apply Filters</button></div>
        <div class="col-md-2"><button class="btn btn-outline-secondary w-100" type="button" onclick="window.print()">Print</button></div>
        <div class="col-md-2"><a class="btn btn-outline-primary w-100" href="../api/reports.php" download>Export</a></div>
    </form>
</div>
<div class="row g-4 mb-4">
    <div class="col-lg-6"><div class="card p-4"><h5 class="fw-bold mb-3">Daily Incidents</h5><div style="height: 280px;"><canvas id="dailyChart"></canvas></div></div></div>
    <div class="col-lg-6"><div class="card p-4"><h5 class="fw-bold mb-3">Weekly Incidents</h5><div style="height: 280px;"><canvas id="weeklyChart"></canvas></div></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-12"><div class="card p-4"><h5 class="fw-bold mb-3">Monthly Incidents</h5><div style="height: 300px;"><canvas id="monthlyChart"></canvas></div></div></div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/reports.js"></script>

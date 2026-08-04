<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['citizen']);
include_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4">
    <h3 class="fw-bold mb-3">Nearby Shelters</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-info-subtle p-3">
                <h5>Civic Center Shelter</h5>
                <p class="mb-1">123 Rizal Avenue, Manila</p>
                <p class="mb-0">Capacity: 200 | Occupancy: 86</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-info-subtle p-3">
                <h5>School Gym Shelter</h5>
                <p class="mb-1">45 Aurora Blvd, Quezon City</p>
                <p class="mb-0">Capacity: 150 | Occupancy: 52</p>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>

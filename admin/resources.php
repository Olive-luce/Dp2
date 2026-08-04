<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);
include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Register Resource</h4>
            <form id="resourceForm">
                <div class="mb-3"><label class="form-label">Resource Name</label><input class="form-control" name="name" required></div>
                <div class="mb-3"><label class="form-label">Category</label><select class="form-select" name="category"><option>Food</option><option>Water</option><option>Medicine</option><option>Fuel</option><option>Blankets</option><option>Vehicles</option><option>Boats</option><option>Helicopters</option><option>Medical Kits</option></select></div>
                <div class="mb-3"><label class="form-label">Stock</label><input type="number" class="form-control" name="quantity" required></div>
                <div class="mb-3"><label class="form-label">Unit</label><input class="form-control" name="unit" placeholder="boxes, liters, units"></div>
                <div class="mb-3"><label class="form-label">Location</label><input class="form-control" name="location" required></div>
                <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="available">Available</option><option value="low">Low</option><option value="depleted">Depleted</option></select></div>
                <button class="btn btn-primary w-100" type="submit">Save Resource</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <h4 class="fw-bold mb-3">Inventory Charts</h4>
            <canvas id="inventoryChart"></canvas>
        </div>
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Resource Inventory</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Resource</th><th>Category</th><th>Stock</th><th>Remaining</th><th>Location</th><th>Status</th></tr></thead>
                    <tbody id="resourceList"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/resource-management.js"></script>

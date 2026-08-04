<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);
$pageTitle = 'Volunteer Management';
$pageSubtitle = 'Register volunteers and track availability across missions.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Register Volunteer</h4>
            <form id="volunteerForm">
                <div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" required></div>
                <div class="mb-3"><label class="form-label">Full Name</label><input class="form-control" name="full_name" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                <div class="mb-3"><label class="form-label">Availability</label><select class="form-select" name="availability"><option value="available">Available</option><option value="busy">Busy</option><option value="deployed">Deployed</option></select></div>
                <div class="mb-3"><label class="form-label">Experience</label><select class="form-select" name="experience_level"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select></div>
                <div class="mb-3"><label class="form-label">Skills</label>
                    <div class="d-grid gap-2">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="skill_medical" name="skill_medical"><label class="form-check-label" for="skill_medical">Medical</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="skill_search_and_rescue" name="skill_search_and_rescue"><label class="form-check-label" for="skill_search_and_rescue">Search and Rescue</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="skill_logistics" name="skill_logistics"><label class="form-check-label" for="skill_logistics">Logistics</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="skill_engineering" name="skill_engineering"><label class="form-check-label" for="skill_engineering">Engineering</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="skill_communication" name="skill_communication"><label class="form-check-label" for="skill_communication">Communication</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="skill_food_distribution" name="skill_food_distribution"><label class="form-check-label" for="skill_food_distribution">Food Distribution</label></div>
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">Save Volunteer</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Volunteer Directory</h4>
            <div id="volunteerStats" class="row g-3 mb-3"></div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Name</th><th>Username</th><th>Availability</th><th>Experience</th><th>Skills</th><th>Assignments</th><th>Status</th></tr></thead>
                    <tbody id="volunteerList"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/volunteer-management.js"></script>

document.addEventListener('DOMContentLoaded', function () {
    const volunteerForm = document.getElementById('volunteerForm');
    const volunteerList = document.getElementById('volunteerList');
    const volunteerStats = document.getElementById('volunteerStats');

    const skillOptions = ['Medical', 'Search and Rescue', 'Logistics', 'Engineering', 'Communication', 'Food Distribution'];

    const renderVolunteerRows = function (volunteers) {
        if (!volunteerList) return;
        volunteerList.innerHTML = '';
        volunteers.forEach(function (volunteer) {
            const row = document.createElement('tr');
            const skills = (volunteer.skills || []).map(function (s) { return s.skill_name; }).join(', ');
            const assignments = (volunteer.assignments || []).map(function (a) { return a.title; }).join(', ');
            row.innerHTML = '<td>' + volunteer.full_name + '</td><td>' + volunteer.username + '</td><td>' + volunteer.availability + '</td><td>' + volunteer.experience_level + '</td><td>' + skills + '</td><td>' + assignments + '</td><td>' + volunteer.status + '</td>';
            volunteerList.appendChild(row);
        });
        if (volunteerStats) {
            volunteerStats.innerHTML = '<div class="col-md-4"><div class="card p-3"><h6 class="text-muted">Total Volunteers</h6><h3 class="fw-bold">' + volunteers.length + '</h3></div></div><div class="col-md-4"><div class="card p-3"><h6 class="text-muted">Available Now</h6><h3 class="fw-bold">' + volunteers.filter(function (v) { return v.availability === 'available'; }).length + '</h3></div></div><div class="col-md-4"><div class="card p-3"><h6 class="text-muted">Assigned Missions</h6><h3 class="fw-bold">' + volunteers.reduce(function (acc, v) { return acc + ((v.assignments || []).length); }, 0) + '</h3></div></div>';
        }
    };

    const loadVolunteers = function () {
        fetch('./../api/volunteers.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                renderVolunteerRows(data);
            });
    };

    if (volunteerForm) {
        volunteerForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(volunteerForm);
            const skills = skillOptions.filter(function (skill) { return formData.get('skill_' + skill.replace(/\s+/g, '_').toLowerCase()) === 'on'; });
            const payload = {
                username: formData.get('username'),
                full_name: formData.get('full_name'),
                email: formData.get('email'),
                availability: formData.get('availability'),
                experience_level: formData.get('experience_level'),
                skills: skills.map(function (skill) { return { skill_name: skill, proficiency: 'intermediate' }; })
            };
            fetch('./../api/volunteers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function () { loadVolunteers(); volunteerForm.reset(); });
        });
    }

    loadVolunteers();
});

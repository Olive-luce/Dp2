document.addEventListener('DOMContentLoaded', function () {
    // Initialize the global loader and shared UI state.
    var loader = document.getElementById('page-loader');
    var body = document.body;

    function showLoader() {
        if (loader) {
            loader.classList.add('show');
        }
    }

    function hideLoader() {
        if (loader) {
            loader.classList.remove('show');
        }
    }

    function showToast(message, type) {
        type = type || 'info';
        var stack = document.getElementById('toastStack');
        if (!stack) return;

        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML = '<div class="fw-semibold">' + message + '</div>';
        stack.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('show');
        });

        window.setTimeout(function () {
            toast.classList.remove('show');
            window.setTimeout(function () {
                toast.remove();
            }, 220);
        }, 3200);
    }

    window.showToast = showToast;

    function applyTheme(theme) {
        body.setAttribute('data-theme', theme);
        var toggle = document.getElementById('themeToggle');
        if (toggle) {
            var icon = toggle.querySelector('i');
            var label = toggle.querySelector('span');
            if (theme === 'dark') {
                if (icon) { icon.className = 'fa-solid fa-sun me-2'; }
                if (label) { label.textContent = 'Light mode'; }
                toggle.setAttribute('aria-pressed', 'true');
            } else {
                if (icon) { icon.className = 'fa-solid fa-moon me-2'; }
                if (label) { label.textContent = 'Dark mode'; }
                toggle.setAttribute('aria-pressed', 'false');
            }
        }
        localStorage.setItem('dashboard-theme', theme);
    }

    function initThemeToggle() {
        var toggle = document.getElementById('themeToggle');
        if (!toggle) return;
        var savedTheme = localStorage.getItem('dashboard-theme') || 'light';
        applyTheme(savedTheme);
        toggle.addEventListener('click', function () {
            var nextTheme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            showToast(nextTheme === 'dark' ? 'Dark mode enabled.' : 'Light mode enabled.', 'info');
        });
    }

    function initSidebar() {
        var toggle = document.getElementById('sidebarToggle');
        var close = document.getElementById('sidebarClose');
        var backdrop = document.getElementById('sidebarBackdrop');
        function toggleSidebar(force) {
            var shouldOpen = typeof force === 'boolean' ? force : !body.classList.contains('sidebar-open');
            body.classList.toggle('sidebar-open', shouldOpen);
            if (toggle) {
                toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            }
        }
        if (toggle) {
            toggle.addEventListener('click', function () {
                toggleSidebar();
            });
        }
        if (close) {
            close.addEventListener('click', function () { toggleSidebar(false); });
        }
        if (backdrop) {
            backdrop.addEventListener('click', function () { toggleSidebar(false); });
        }
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                toggleSidebar(false);
            }
        });
    }

    function initScrollReveal() {
        document.querySelectorAll('.animate-on-scroll').forEach(function (item, index) {
            item.classList.add('is-visible');
        });
    }

    function initForms() {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.classList.add('is-loading');
                    submitButton.disabled = true;
                    window.setTimeout(function () {
                        submitButton.classList.remove('is-loading');
                        submitButton.disabled = false;
                    }, 1400);
                }
            });
        });
    }

    // Initialize the interactive campus map when present.
    var mapElement = document.getElementById('map');
    if (mapElement && window.L) {
        var map = L.map('map').setView([14.5995, 120.9842], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([14.5995, 120.9842]).addTo(map).bindPopup('Emergency Coordination HQ').openPopup();
        L.marker([14.6200, 121.0000]).addTo(map).bindPopup('Incident Zone 2');
    }

    // Populate the incident feed on the landing page when available.
    var feed = document.getElementById('incident-feed');
    if (feed) {
        feed.innerHTML = '<li class="list-group-item text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></li>';
        fetch('./api/incidents.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!Array.isArray(data) || !data.length) {
                    feed.innerHTML = '<li class="list-group-item text-muted">No incidents reported yet.</li>';
                    return;
                }
                feed.innerHTML = data.map(function (item) {
                    return '<li class="list-group-item"><strong>' + item.title + '</strong><br><small class="text-muted">' + item.status + ' • ' + item.severity + '</small></li>';
                }).join('');
            })
            .catch(function () {
                feed.innerHTML = '<li class="list-group-item text-muted">Unable to load incidents right now.</li>';
            });
    }

    // Render dashboard charts when Chart.js is available.
    var charts = document.querySelectorAll('[data-chart]');
    charts.forEach(function (canvas) {
        if (!canvas || !window.Chart) return;
        var ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Resolved', 'In Progress'],
                datasets: [{
                    data: [24, 16, 8],
                    backgroundColor: ['#0f2a4a', '#1b8f5a', '#ff6b35']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    });

    initThemeToggle();
    initSidebar();
    initScrollReveal();
    initForms();

    window.setTimeout(function () {
        hideLoader();
        showToast('Operations dashboard ready.', 'success');
    }, 500);
});

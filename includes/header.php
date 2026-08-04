<?php
// Start the session early so shared UI state can be reused across pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Professional emergency operations dashboard for disaster coordination.">
    <meta name="theme-color" content="#0f172a">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body data-role="<?php echo htmlspecialchars($_SESSION['role'] ?? 'citizen'); ?>">
<a class="skip-link" href="#mainContent">Skip to content</a>
<div class="page-loader" id="page-loader">
    <div class="spinner-border text-light" role="status" aria-label="Loading dashboard"></div>
</div>
<div class="app-shell">
    <?php include_once __DIR__ . '/navbar.php'; ?>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-main">
        <?php include_once __DIR__ . '/sidebar.php'; ?>
        <div class="content-area">
            <div class="content-scroll">
                <main class="main-content" id="mainContent">

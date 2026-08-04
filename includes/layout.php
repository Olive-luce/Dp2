<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}
if (!isset($pageSubtitle)) {
    $pageSubtitle = '';
}
if (!isset($pageActions)) {
    $pageActions = '';
}
if (!isset($pageContent)) {
    $pageContent = '';
}

include_once __DIR__ . '/header.php';
include_once __DIR__ . '/navbar.php';
?>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="app-main">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    <div class="content-area">
        <div class="content-scroll">
            <main class="main-content" id="mainContent">
                <?php if (!empty($pageTitle)): ?>
                    <div class="page-header">
                        <div class="page-title-group">
                            <div class="eyebrow"><i class="fa-solid fa-shield-halved"></i> Emergency Operations Center</div>
                            <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
                            <?php if (!empty($pageSubtitle)): ?>
                                <p class="page-subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($pageActions)): ?>
                            <div class="page-actions"><?php echo $pageActions; ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php echo $pageContent; ?>
            </main>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/footer.php'; ?>

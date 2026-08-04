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
echo $pageContent;
include_once __DIR__ . '/footer.php';

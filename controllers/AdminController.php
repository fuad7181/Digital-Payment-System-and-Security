<?php
require_once __DIR__ . '/../models/bootstrap.php';

function requireAdminRedirect(): void {
    requireLoginRedirect();
    if (currentUserRole() !== 'admin') {
        $_SESSION['errors'] = ["Access denied."];
        header('Location: index.php?url=Auth/login');
        exit;
    }
}

function adminDashboard(): void {
    requireAdminRedirect();
    require __DIR__ . '/../views/admin/admindashboard.php';
}

function manageRoles(): void {
    requireAdminRedirect();
    require __DIR__ . '/../views/admin/manage_roles.php';
}

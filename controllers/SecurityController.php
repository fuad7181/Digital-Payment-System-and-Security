<?php
require_once __DIR__ . '/../models/bootstrap.php';

function signupPage(): void {
    require __DIR__ . '/../views/security/signup.php';
}

function forgotPage(): void {
    require __DIR__ . '/../views/security/forgot.php';
}

function resetPasswordPage(): void {
    require __DIR__ . '/../views/security/reset_password.php';
}

function changePasswordPage(): void {
    requireLoginRedirect();
    require __DIR__ . '/../views/security/change_password.php';
}

function termsViewPage(): void {
    requireLoginRedirect();
    require __DIR__ . '/../views/security/terms_view.php';
}

function termsConditionsPage(): void {
    requireLoginRedirect();
    require __DIR__ . '/../views/security/Terms_conditions.php';
}

function loanStatusPage(): void {
    requireLoginRedirect();
    require __DIR__ . '/../views/security/loan_status.php';
}

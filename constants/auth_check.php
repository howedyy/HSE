<?php
/**
 * Authentication Check - Include this file in all protected pages
 * This file ensures user is logged in and has appropriate access
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/auth.php';

/**
 * Check if user is authenticated
 */
function isUserAuthenticated() {
    return isset($_SESSION['username']) && 
           !empty($_SESSION['username']) && 
           isset($_SESSION['user_id']) && 
           !empty($_SESSION['user_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireAuthentication() {
    if (!isUserAuthenticated()) {
        // Clear any partial session data
        session_destroy();
        
        // Redirect to login page
        header('Location: index.php');
        exit();
    }
}

/**
 * Check authentication and page access permissions
 * @param string $page - The page name (e.g., 'ptw.php')
 * @param string $action - The action (e.g., 'view', 'submit', 'edit')
 */
function requireAuthenticationAndAccess($page, $action = 'view') {
    // First check if user is logged in
    requireAuthentication();
    
    // Then check if user has access to this specific page/action
    if (!hasAccess($page, $action)) {
        header('Location: unauthorized.php');
        exit();
    }
}

/**
 * Get current user information
 */
function getCurrentUser() {
    if (!isUserAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'user_type' => $_SESSION['user_type'] ?? null,
        'user_name' => $_SESSION['user_name'] ?? $_SESSION['username']
    ];
}

/**
 * Simple authentication check - call this at the top of protected pages
 */
requireAuthentication();
?> 
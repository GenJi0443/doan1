<?php
session_start();
require_once '../config/database.php';

// Check if user is already logged in
function isLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

// Redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Login function
function login($email, $password)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            return true;
        }

        return false;
    } catch (Exception $e) {
        throw $e;
    }
}

// Logout function
function logout()
{
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    session_destroy();
}

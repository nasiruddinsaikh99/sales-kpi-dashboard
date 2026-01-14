<?php

require_once __DIR__ . '/../Models/User.php';

class AuthController {
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $userModel = new User();
            $user = $userModel->findByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                
                if ($user['role'] === 'admin') {
                    header('Location: /sales-kpi-dashboard/admin/dashboard');
                } else {
                    header('Location: /sales-kpi-dashboard/agent/dashboard');
                }
                exit;
            } else {
                $error = "Invalid credentials.";
                require __DIR__ . '/../Views/auth/login.php';
            }
        } else {
            require __DIR__ . '/../Views/auth/login.php';
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /sales-kpi-dashboard/login');
        exit;
    }
}

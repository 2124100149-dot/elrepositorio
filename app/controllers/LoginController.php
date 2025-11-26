<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';

class LoginController {
    public function index() {
        // Si ya está logueado, redirigir
        if (isset($_SESSION['correo']) && isset($_SESSION['rol'])) {
            $this->redirectByRole($_SESSION['rol']);
            exit();
        }
        
        $this->loadView('login.php');
    }

    public function login() {
        // Si ya está logueado, redirigir
        if (isset($_SESSION['correo']) && isset($_SESSION['rol'])) {
            $this->redirectByRole($_SESSION['rol']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            if (empty($correo) || empty($password)) {
                $error = "Por favor completa todos los campos";
            } else {
                $userModel = new UserModel();
                $user = $userModel->login($correo, $password);
                
                if ($user) {
                    // Establecer variables de sesión
                    $_SESSION['correo'] = $user['correo'];
                    $_SESSION['rol'] = $user['rol'];
                    
                    // Redirigir según el rol
                    $this->redirectByRole($user['rol']);
                    exit();
                } else {
                    $error = "Usuario o contraseña incorrectos";
                }
            }
        } else {
            $error = "Método de solicitud incorrecto";
        }
        
        // Mostrar vista con error
        $this->loadView('login.php', ['error' => $error]);
    }

    private function redirectByRole($rol) {
        $redirect_url = "";
        switch ($rol) {
            case 'AdminGeneral':
            case 'AdminRegional':
                $redirect_url = BASE_URL . "/public/admin.php";
                break;
            case 'medico':
                $redirect_url = BASE_URL . "/public/medico.php";
                break;
            case 'Usuario':
                $redirect_url = BASE_URL . "/public/paciente.php";
                break;
            default:
                $redirect_url = BASE_URL . "/public/index.php";
        }
        
        header("Location: " . $redirect_url);
        exit();
    }

    private function loadView($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $view;
    }
}
?>
<?php
session_start();
require_once '../models/UserModel.php';

class RegisterController {
    public function index() {
        require_once '../views/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = $_POST['correo'] ?? '';
            $rol = $_POST['rol'] ?? '';
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['reviewPassword'] ?? '';

            if (empty($correo) || empty($rol) || empty($password) || empty($password2)) {
                $error = "Por favor completa todos los campos";
            } else if ($password !== $password2) {
                $error = "Las contraseñas no coinciden";
            } else {
                $userModel = new UserModel();
                $result = $userModel->register($correo, $rol, $password);
                
                if ($result) {
                    header("Location: ../../public/P_Entrar.html");
                    exit();
                } else {
                    $error = "Este usuario ya está registrado";
                }
            }
        }
        
        require_once '../views/register.php';
    }
}
?>
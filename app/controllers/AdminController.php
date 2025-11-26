<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

class AdminController {
    public function index() {
        // Verificar sesión
        if (!isset($_SESSION['correo'])) {
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        }

        // Verificar rol
        if ($_SESSION['rol'] !== 'AdminGeneral' && $_SESSION['rol'] !== 'AdminRegional') {
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        }

        // Datos de ejemplo para la vista
        $estadisticas = [
            'total_usuarios' => 150,
            'total_medicos' => 25,
            'total_pacientes' => 120,
            'total_citas' => 45
        ];

        $usuarios = [
            ['id' => 1, 'email' => 'usuario1@test.com', 'tipo' => 'Paciente', 'fecha_registro' => '2024-01-15'],
            ['id' => 2, 'email' => 'medico1@test.com', 'tipo' => 'Médico', 'fecha_registro' => '2024-01-14']
        ];

        // Cargar vista
        require_once __DIR__ . '/../views/admin.php';
    }
}
?>
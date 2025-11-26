<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

class MedicoController {
    public function index() {
        // Verificar sesión
        if (!isset($_SESSION['correo'])) {
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        }

        // Verificar rol
        if ($_SESSION['rol'] !== 'medico') {
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        }

        // Datos de ejemplo para la vista
        $estadisticas = [
            'total_citas' => 45,
            'citas_hoy' => 3,
            'proximas_citas' => 8
        ];

        $citas = [
            [
                'paciente_nombre' => 'Juan Pérez',
                'apellido' => 'Gómez',
                'fecha_cita' => '2024-01-20',
                'hora_cita' => '10:00:00',
                'telefono' => '555-1234',
                'motivo' => 'Consulta general',
                'estado' => 'confirmada'
            ]
        ];

        $mensajes = [];

        // Cargar vista
        require_once __DIR__ . '/../views/medico.php';
    }
}
?>
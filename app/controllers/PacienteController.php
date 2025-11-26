<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

class PacienteController {
    public function index() {
        // Verificar si está logueado y es paciente
        if (!isset($_SESSION['correo'])) {
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        }

        if ($_SESSION['rol'] !== 'Usuario') {
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        }

        require_once __DIR__ . '/../models/PacienteModel.php';
        $pacienteModel = new PacienteModel();

        // Obtener datos para la vista
        $servicios_hospitalarios = $pacienteModel->obtenerServicios();
        $especialidades_medicas = $pacienteModel->obtenerEspecialidades();
        $hospitales = $pacienteModel->obtenerHospitales();

        // Procesar búsqueda si existe
        $resultadosBusqueda = [];
        $terminoBusqueda = '';
        $tipoBusqueda = 'general';

        if (isset($_GET['buscar']) && !empty(trim($_GET['busqueda']))) {
            $terminoBusqueda = trim($_GET['busqueda']);
            $tipoBusqueda = $_GET['tipo_busqueda'] ?? 'general';
            $resultadosBusqueda = $pacienteModel->buscarHospitales($terminoBusqueda, $tipoBusqueda);
        } else {
            $resultadosBusqueda = $hospitales;
        }

        // Cargar la vista con todos los datos
        require_once __DIR__ . '/../views/paciente.php';
    }
}
?>
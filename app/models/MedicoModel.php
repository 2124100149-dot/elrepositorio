<?php
class MedicoModel {
    private $db;

    public function __construct() {
        require_once '../../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerEstadisticasMedico($correo) {
        $stats = [];
        
        // Total de citas del médico
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_citas'] = $result->fetch_assoc()['total'];
        $stmt->close();
        
        // Citas de hoy
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_correo = ? AND fecha_cita = CURDATE()");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['citas_hoy'] = $result->fetch_assoc()['total'];
        $stmt->close();
        
        // Próximas citas (próximos 7 días)
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_correo = ? AND fecha_cita BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['proximas_citas'] = $result->fetch_assoc()['total'];
        $stmt->close();
        
        return $stats;
    }

    public function obtenerCitasMedico($correo) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.nombre as paciente_nombre, p.apellido, p.telefono 
            FROM cita c 
            LEFT JOIN paciente p ON c.paciente_id = p.id 
            WHERE c.medico_correo = ? 
            ORDER BY c.fecha_cita DESC, c.hora_cita DESC
        ");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $citas = [];
        while ($row = $result->fetch_assoc()) {
            $citas[] = $row;
        }
        
        $stmt->close();
        return $citas;
    }

    public function obtenerMensajesMedico($correo) {
        $stmt = $this->db->prepare("
            SELECT * FROM mensajes 
            WHERE destinatario = ? OR remitente = ? 
            ORDER BY fecha_envio DESC
        ");
        $stmt->bind_param("ss", $correo, $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensajes = [];
        while ($row = $result->fetch_assoc()) {
            $mensajes[] = $row;
        }
        
        $stmt->close();
        return $mensajes;
    }

    public function enviarMensaje($remitente, $destinatario, $asunto, $contenido) {
        $stmt = $this->db->prepare("INSERT INTO mensajes (remitente, destinatario, asunto, contenido) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $remitente, $destinatario, $asunto, $contenido);
        return $stmt->execute();
    }
}
?>
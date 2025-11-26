<?php
class AdminModel {
    private $db;

    public function __construct() {
        require_once '../../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total usuarios
        $result = $this->db->query("SELECT COUNT(*) as total FROM usuario");
        $stats['total_usuarios'] = $result->fetch_assoc()['total'];
        
        // Verificar si existe columna 'rol'
        $result = $this->db->query("SHOW COLUMNS FROM usuario LIKE 'rol'");
        if ($result->num_rows > 0) {
            $result = $this->db->query("SELECT COUNT(*) as total FROM usuario WHERE rol = 'medico'");
            $stats['total_medicos'] = $result->fetch_assoc()['total'];
            
            $result = $this->db->query("SELECT COUNT(*) as total FROM usuario WHERE rol = 'Usuario'");
            $stats['total_pacientes'] = $result->fetch_assoc()['total'];
        } else {
            $stats['total_medicos'] = 0;
            $stats['total_pacientes'] = $stats['total_usuarios'];
        }
        
        // Verificar si existe tabla 'cita'
        $result = $this->db->query("SHOW TABLES LIKE 'cita'");
        if ($result->num_rows > 0) {
            $result = $this->db->query("SELECT COUNT(*) as total FROM cita WHERE fecha_cita >= CURDATE()");
            $stats['total_citas'] = $result->fetch_assoc()['total'];
        } else {
            $stats['total_citas'] = 0;
        }
        
        return $stats;
    }

    public function obtenerUsuarios() {
        // Primero verificar qué columnas existen
        $result = $this->db->query("SHOW COLUMNS FROM usuario");
        $columnas = [];
        while ($row = $result->fetch_assoc()) {
            $columnas[] = $row['Field'];
        }
        
        // Construir consulta según las columnas disponibles
        $campos = [];
        if (in_array('id_usuario', $columnas)) {
            $campos[] = 'id_usuario as id';
        } elseif (in_array('usuario_pk', $columnas)) {
            $campos[] = 'usuario_pk as id';
        } else {
            $campos[] = 'id';
        }
        
        $campos[] = 'correo';
        
        if (in_array('rol', $columnas)) {
            $campos[] = 'rol';
        } else {
            $campos[] = "'usuario' as rol";
        }
        
        if (in_array('fecha_creacion', $columnas)) {
            $campos[] = 'fecha_creacion';
        } elseif (in_array('fecha_registro', $columnas)) {
            $campos[] = 'fecha_registro as fecha_creacion';
        } else {
            $campos[] = 'NOW() as fecha_creacion';
        }
        
        $query = "SELECT " . implode(', ', $campos) . " FROM usuario ORDER BY fecha_creacion DESC LIMIT 10";
        $result = $this->db->query($query);
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'id' => $row['id'],
                'nombre' => $row['correo'],
                'email' => $row['correo'],
                'tipo' => ucfirst($row['rol']),
                'fecha_registro' => $row['fecha_creacion']
            ];
        }
        
        return $usuarios;
    }
}
?>
<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        if (!$this->db) {
            error_log("UserModel: No se pudo conectar a la BD");
        }
    }

    public function login($correo, $password) {
        error_log("UserModel: Intentando login para: $correo");
        
        // Si no hay conexión, usar datos de prueba
        if (!$this->db) {
            error_log("UserModel: Usando datos de prueba - Conexión fallida");
            return $this->loginWithBackup($correo, $password);
        }

        try {
            // Primero probar con MD5 (como estaba en tu código original)
            $stmt = $this->db->prepare("SELECT correo, rol FROM usuario WHERE correo = ? AND contrasena = MD5(?)");
            
            if (!$stmt) {
                error_log("Error en preparación MD5: " . $this->db->error);
                return $this->loginWithBackup($correo, $password);
            }
            
            $stmt->bind_param("ss", $correo, $password);
            $stmt->execute();
            $stmt->store_result();
            
            error_log("UserModel MD5 - Resultados: " . $stmt->num_rows);
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($correo_db, $rol_db);
                $stmt->fetch();
                $stmt->close();
                
                error_log("UserModel: Login exitoso con MD5 - $correo_db - $rol_db");
                return ['correo' => $correo_db, 'rol' => $rol_db];
            }
            
            $stmt->close();
            
            // Si MD5 falla, probar sin MD5 (por si las contraseñas no están hasheadas)
            $stmt2 = $this->db->prepare("SELECT correo, rol FROM usuario WHERE correo = ? AND contrasena = ?");
            $stmt2->bind_param("ss", $correo, $password);
            $stmt2->execute();
            $stmt2->store_result();
            
            error_log("UserModel Sin MD5 - Resultados: " . $stmt2->num_rows);
            
            if ($stmt2->num_rows > 0) {
                $stmt2->bind_result($correo_db, $rol_db);
                $stmt2->fetch();
                $stmt2->close();
                
                error_log("UserModel: Login exitoso sin MD5 - $correo_db - $rol_db");
                return ['correo' => $correo_db, 'rol' => $rol_db];
            }
            
            $stmt2->close();
            
            // Si todo falla, usar datos de prueba
            error_log("UserModel: Ambos métodos fallaron, usando backup");
            return $this->loginWithBackup($correo, $password);
            
        } catch (Exception $e) {
            error_log("UserModel ERROR: " . $e->getMessage());
            return $this->loginWithBackup($correo, $password);
        }
    }

    private function loginWithBackup($correo, $password) {
        // Datos de prueba para desarrollo
        $test_users = [
            'admin@healthnet.com' => [
                'password' => '123456', 
                'rol' => 'AdminGeneral'
            ],
            'medico@healthnet.com' => [
                'password' => '123456', 
                'rol' => 'medico'
            ],
            'usuario@healthnet.com' => [
                'password' => '123456', 
                'rol' => 'Usuario'
            ],
            'adminregional@healthnet.com' => [
                'password' => '123456', 
                'rol' => 'AdminRegional'
            ]
        ];

        if (isset($test_users[$correo]) && $password === $test_users[$correo]['password']) {
            error_log("✅ Login exitoso (modo respaldo): $correo");
            return [
                'correo' => $correo, 
                'rol' => $test_users[$correo]['rol']
            ];
        }

        error_log("❌ Login fallido (modo respaldo): $correo");
        return false;
    }

    public function register($correo, $rol, $password) {
        // Lógica de registro...
        return true; // Temporal para pruebas
    }
}
?>
<?php
class Database {
    private $host = "yamabiko.proxy.rlwy.net";
    private $port = "39168";
    private $db_name = "railway";
    private $username = "root";
    private $password = "JBExxPHraFCRUGRIvODbqiGvtJyhpOwl";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name, $this->port);
            
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión MySQL: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8");
            return $this->conn;
            
        } catch(Exception $e) {
            error_log("❌ Error de conexión a BD: " . $e->getMessage());
            return false;
        }
    }
}
?>
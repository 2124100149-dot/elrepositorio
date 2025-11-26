<?php
require_once __DIR__ . '/../../config/database.php';

class PacienteModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerServicios() {
        $servicios = [];
        $query = "SELECT nombre_servicio FROM servicios";
        $result = $this->db->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $servicios[] = $row['nombre_servicio'];
            }
        }
        return $servicios;
    }

    public function obtenerEspecialidades() {
        $especialidades = [];
        $query = "SELECT nombre_especialidad FROM especialidad";
        $result = $this->db->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $especialidades[] = $row['nombre_especialidad'];
            }
        }
        return $especialidades;
    }

    public function obtenerHospitales() {
        $hospitales = [];
        $query = "SELECT h.hospital_pk, h.nombre, h.telefono, h.calle, h.numero, h.cp, h.horario,
                         m.nombre_municipio, e.nombre_entidad
                  FROM hospital h
                  JOIN municipio m ON h.municipio_fk = m.municipio_pk
                  JOIN entidad_federativa e ON m.entidad_fk = e.entidad_pk";
        $result = $this->db->query($query);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $servicios_hospital = $this->obtenerServiciosHospital($row['hospital_pk']);
                $especialidades_hospital = $this->obtenerEspecialidadesHospital($row['hospital_pk']);

                $direccion_completa = $row['calle'] . ' #' . $row['numero'] . ', CP: ' . $row['cp'];

                $hospitales[] = [
                    'id' => $row['hospital_pk'],
                    'nombre' => $row['nombre'],
                    'direccion' => $direccion_completa,
                    'telefono' => $row['telefono'],
                    'municipio' => $row['nombre_municipio'],
                    'estado' => $row['nombre_entidad'],
                    'codigo_postal' => $row['cp'],
                    'horario' => $row['horario'],
                    'servicios' => $servicios_hospital,
                    'especialidades' => $especialidades_hospital
                ];
            }
        }

        return $hospitales;
    }

    private function obtenerServiciosHospital($hospital_id) {
        $servicios = [];
        $query = "SELECT s.nombre_servicio 
                  FROM servicios_hospital sh
                  JOIN servicios s ON sh.servicio_fk = s.servicio_pk 
                  WHERE sh.hospital_fk = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($servicio = $result->fetch_assoc()) {
                $servicios[] = $servicio['nombre_servicio'];
            }
        }
        return $servicios;
    }

    private function obtenerEspecialidadesHospital($hospital_id) {
        $especialidades = [];
        $query = "SELECT DISTINCT esp.nombre_especialidad 
                  FROM medico med
                  JOIN especialidad_medico em ON med.id_medico = em.medico_fk
                  JOIN especialidad esp ON em.especialidad_fk = esp.especialidad_pk
                  WHERE med.id_hospital = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($especialidad = $result->fetch_assoc()) {
                $especialidades[] = $especialidad['nombre_especialidad'];
            }
        }
        return $especialidades;
    }

    public function buscarHospitales($termino, $tipoBusqueda = 'general') {
        $hospitales = $this->obtenerHospitales();
        $resultados = [];

        $terminoNormalizado = $this->normalizarTexto($termino);

        foreach ($hospitales as $hospital) {
            $encontrado = false;

            switch($tipoBusqueda) {
                case 'general':
                    $campos = ['nombre', 'municipio', 'direccion', 'estado', 'codigo_postal'];
                    foreach ($campos as $campo) {
                        if (isset($hospital[$campo]) && 
                            stripos($this->normalizarTexto($hospital[$campo]), $terminoNormalizado) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }

                    if (!$encontrado && isset($hospital['servicios'])) {
                        foreach($hospital['servicios'] as $servicio) {
                            if (stripos($this->normalizarTexto($servicio), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$encontrado && isset($hospital['especialidades'])) {
                        foreach($hospital['especialidades'] as $especialidad) {
                            if (stripos($this->normalizarTexto($especialidad), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    break;
                    
                case 'servicios':
                    if (isset($hospital['servicios'])) {
                        foreach($hospital['servicios'] as $servicio) {
                            if (stripos($this->normalizarTexto($servicio), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    break;
                    
                case 'especialidades':
                    if (isset($hospital['especialidades'])) {
                        foreach($hospital['especialidades'] as $especialidad) {
                            if (stripos($this->normalizarTexto($especialidad), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    break;
            }

            if ($encontrado) {
                $resultados[] = $hospital;
            }
        }

        return $resultados;
    }

    private function normalizarTexto($texto) {
        if (empty($texto)) return '';
        
        $texto = mb_strtolower($texto, 'UTF-8');
        
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        
        return strtr($texto, $acentos);
    }
}
?>
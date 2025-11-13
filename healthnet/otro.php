<?php
session_start();
/**
 * ARREGLOS PARA DATOS FIJOS
 */
$precios_especialidades = [
    'cardiologia' => 900,
    'pediatria' => 800,
    'ginecologia' => 940,
    'traumatologia' => 1000,
    'neurologia' => 850,
    'oncologia' => 700,
    'oftamologo' => 600,
    'cirugia' => 650,
    'dermatologia' => 800,
    'medicina_general' => 820
];

$usuarios = [
    'admin' => [
        'password' => '123456',
        'nombre' => 'Dr. Carlos Rodríguez',
        'email' => 'carlos@hospital.com',
        'telefono' => '(555) 123-4567',
        'tipo' => 'doctor'
    ],
    'paciente' => [
        'password' => '123456',
        'nombre' => 'Ana Martínez López',
        'email' => 'ana.martinez@email.com',
        'telefono' => '(555) 987-6543',
        'tipo' => 'paciente'
    ]
];

// LISTA DE SERVICIOS HOSPITALARIOS 
$servicios_hospitalarios = [
    'Urgencias 24/7',
    'Consultas Externas',
    'Cirugías Programadas',
    'Cirugías de Emergencia',
    'Laboratorio Clínico',
    'Rayos X',
    'Tomografía',
    'Resonancia Magnética',
    'Ultrasonido',
    'Farmacia',
    'Hospitalización',
    'Terapia Intensiva',
    'Cuidados Intensivos Neonatales',
    'Banco de Sangre',
    'Ambulancia',
    'Hemodiálisis',
    'Quimioterapia',
    'Radioterapia',
    'Endoscopía',
    'Colonoscopía'
];

// LISTA DE ESPECIALIDADES MÉDICAS (Arreglo fijo)
$especialidades_medicas = [
    'Cardiología',
    'Pediatría',
    'Ginecología',
    'Traumatología',
    'Neurología',
    'Oncología',
    'Oftalmología',
    'Cirugía General',
    'Cirugía Plástica',
    'Dermatología',
    'Medicina General',
    'Odontología',
    'Ortodoncia',
    'Endodoncia',
    'Periodoncia',
    'Gastroenterología',
    'Psiquiatría',
    'Neumología',
    'Endocrinología',
    'Nefrología',
    'Urología',
    'Otorrinolaringología',
    'Reumatología',
    'Alergología',
    'Infectología'
];


/**
 * LISTA DOBLEMENTE CIRCULAR PARA HOSPITALES
 */
class NodoHospital {
    public $hospital;
    public $siguiente;
    public $anterior;
    
    public function __construct($hospital) {
        $this->hospital = $hospital;
        $this->siguiente = null;
        $this->anterior = null;
    }
}

class ListaDoblementeCircularHospitales {
    private $cabeza;
    private $tamaño;
    
    public function __construct() {
        $this->cabeza = null;
        $this->tamaño = 0;
    }
    
    /**
     * MÉTODO PARA INSERTAR HOSPITALES EN LA LISTA
     */
    public function insertar($hospital) {
        $nuevo = new NodoHospital($hospital);
        
        if ($this->cabeza === null) {
            $this->cabeza = $nuevo;
            $nuevo->siguiente = $nuevo;
            $nuevo->anterior = $nuevo;
        } else {
            $ultimo = $this->cabeza->anterior;
            
            $ultimo->siguiente = $nuevo;
            $nuevo->anterior = $ultimo;
            $nuevo->siguiente = $this->cabeza;
            $this->cabeza->anterior = $nuevo;
        }
        $this->tamaño++;
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
    
    /**
     * BÚSQUEDA SECUENCIAL 
     */
    public function busquedaSecuencial($termino, $tipoBusqueda = 'general') {
        if ($this->cabeza === null) return [];
        
        $resultados = [];
        $actual = $this->cabeza;
        $contador = 0;
        
        $terminoNormalizado = $this->normalizarTexto($termino);
        
        do {
            $hospital = $actual->hospital;
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
                    // Búsqueda específica en servicios
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
                    // Búsqueda específica en especialidades
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
            
            $actual = $actual->siguiente;
            $contador++;
        } while ($actual !== $this->cabeza && $contador < $this->tamaño);
        
        return $resultados;
    }
    
    /**
     * MÉTODO PARA OBTENER TODOS LOS HOSPITALES
     */
    public function obtenerTodos() {
        if ($this->cabeza === null) return [];
        
        $datos = [];
        $actual = $this->cabeza;
        $contador = 0;
        
        do {
            $datos[] = $actual->hospital;
            $actual = $actual->siguiente;
            $contador++;
        } while ($actual !== $this->cabeza && $contador < $this->tamaño);
        
        return $datos;
    }
}


$hospitalesPorEstado = [
    // Ciudad de México
    [
        'id' => 1, 'nombre' => 'Hospital General de México', 'direccion' => 'Dr. Balmis 148', 
        'telefono' => '(55) 2789-9000', 'municipio' => 'Cuauhtémoc', 'estado' => 'Ciudad de México', 
        'codigo_postal' => '06726', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Cirugías Programadas', 'Laboratorio Clínico', 'Rayos X', 'Hospitalización'],
        'especialidades' => ['Cardiología', 'Pediatría', 'Ginecología', 'Traumatología', 'Odontología', 'Medicina General']
    ],
    // Estado de México
    [
        'id' => 2, 'nombre' => 'Hospital General de Toluca', 'direccion' => 'Paseo Tollocan 101', 
        'telefono' => '(722) 214-8500', 'municipio' => 'Toluca', 'estado' => 'Estado de México', 
        'codigo_postal' => '50120', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X', 'Farmacia'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Jalisco
    [
        'id' => 3, 'nombre' => 'Hospital Civil de Guadalajara', 'direccion' => 'Salvador Quevedo y Zubieta 750', 
        'telefono' => '(33) 3614-7788', 'municipio' => 'Guadalajara', 'estado' => 'Jalisco', 
        'codigo_postal' => '44280', 'region' => 'occidente',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Cirugías de Emergencia', 'Terapia Intensiva', 'Banco de Sangre'],
        'especialidades' => ['Neurología', 'Oncología', 'Oftalmología', 'Cirugía General', 'Odontología', 'Ortodoncia']
    ],
    // Nuevo León
    [
        'id' => 4, 'nombre' => 'Hospital Universitario de Monterrey', 'direccion' => 'Madero y Aguirre Pequeño', 
        'telefono' => '(81) 8347-1010', 'municipio' => 'Monterrey', 'estado' => 'Nuevo León', 
        'codigo_postal' => '64460', 'region' => 'noreste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Tomografía', 'Resonancia Magnética', 'Farmacia'],
        'especialidades' => ['Cardiología', 'Pediatría', 'Ginecología', 'Odontología', 'Endodoncia', 'Periodoncia']
    ],
    // Puebla
    [
        'id' => 5, 'nombre' => 'Hospital General de Puebla', 'direccion' => '13 Sur 2702', 
        'telefono' => '(222) 213-1500', 'municipio' => 'Puebla', 'estado' => 'Puebla', 
        'codigo_postal' => '72000', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Ultrasonido'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ],
    // Veracruz
    [
        'id' => 6, 'nombre' => 'Hospital Regional de Veracruz', 'direccion' => '20 de Noviembre 1254', 
        'telefono' => '(229) 932-1144', 'municipio' => 'Veracruz', 'estado' => 'Veracruz', 
        'codigo_postal' => '91700', 'region' => 'golfo',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Quimioterapia', 'Radioterapia', 'Endoscopía'],
        'especialidades' => ['Oncología', 'Gastroenterología', 'Endocrinología', 'Odontología', 'Ortodoncia']
    ],
    // Guanajuato
    [
        'id' => 7, 'nombre' => 'Hospital General de León', 'direccion' => 'Blvd. Adolfo López Mateos 1810', 
        'telefono' => '(477) 714-1515', 'municipio' => 'León', 'estado' => 'Guanajuato', 
        'codigo_postal' => '37360', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Rayos X', 'Farmacia', 'Hospitalización'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Baja California
    [
        'id' => 8, 'nombre' => 'Hospital General de Tijuana', 'direccion' => 'Blvd. Salinas 1500', 
        'telefono' => '(664) 684-1212', 'municipio' => 'Tijuana', 'estado' => 'Baja California', 
        'codigo_postal' => '22010', 'region' => 'noroeste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Hemodiálisis', 'Banco de Sangre', 'Farmacia'],
        'especialidades' => ['Nefrología', 'Urología', 'Medicina General', 'Odontología', 'Endodoncia']
    ],
    // Chihuahua
    [
        'id' => 9, 'nombre' => 'Hospital Regional de Chihuahua', 'direccion' => 'Calle 24a 2201', 
        'telefono' => '(614) 429-3300', 'municipio' => 'Chihuahua', 'estado' => 'Chihuahua', 
        'codigo_postal' => '31000', 'region' => 'norte',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Quimioterapia', 'Radioterapia', 'Hospitalización'],
        'especialidades' => ['Oncología', 'Psiquiatría', 'Infectología', 'Odontología', 'Periodoncia']
    ],
    // Coahuila
    [
        'id' => 10, 'nombre' => 'Hospital General de Saltillo', 'direccion' => 'Blvd. Venustiano Carranza 2400', 
        'telefono' => '(844) 416-1200', 'municipio' => 'Saltillo', 'estado' => 'Coahuila', 
        'codigo_postal' => '25204', 'region' => 'norte',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ],
    // Sinaloa
    [
        'id' => 11, 'nombre' => 'Hospital General de Culiacán', 'direccion' => 'Boulevard Miguel Hidalgo 2456', 
        'telefono' => '(667) 714-2424', 'municipio' => 'Culiacán', 'estado' => 'Sinaloa', 
        'codigo_postal' => '80230', 'region' => 'noroeste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Ultrasonido', 'Ambulancia', 'Hemodiálisis'],
        'especialidades' => ['Gastroenterología', 'Psiquiatría', 'Neumología', 'Odontología', 'Medicina General']
    ],
    // Michoacán
    [
        'id' => 12, 'nombre' => 'Hospital General de Morelia', 'direccion' => 'Av. Francisco I. Madero 1125', 
        'telefono' => '(443) 312-0404', 'municipio' => 'Morelia', 'estado' => 'Michoacán', 
        'codigo_postal' => '58000', 'region' => 'occidente',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Farmacia'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Oaxaca
    [
        'id' => 13, 'nombre' => 'Hospital General de Oaxaca', 'direccion' => 'Calzada San Felipe 1212', 
        'telefono' => '(951) 516-2020', 'municipio' => 'Oaxaca', 'estado' => 'Oaxaca', 
        'codigo_postal' => '68020', 'region' => 'sur',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Endoscopía', 'Colonoscopía', 'Farmacia'],
        'especialidades' => ['Gastroenterología', 'Cirugía General', 'Medicina General', 'Odontología', 'Ortodoncia']
    ],
    // Guerrero
    [
        'id' => 14, 'nombre' => 'Hospital General de Acapulco', 'direccion' => 'Av. Ruiz Cortines 128', 
        'telefono' => '(744) 486-1200', 'municipio' => 'Acapulco', 'estado' => 'Guerrero', 
        'codigo_postal' => '39670', 'region' => 'sur',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Rayos X', 'Laboratorio Clínico'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ],
    // Chiapas
    [
        'id' => 15, 'nombre' => 'Hospital General de Tuxtla Gutiérrez', 'direccion' => 'Blvd. Belisario Domínguez 1080', 
        'telefono' => '(961) 614-5050', 'municipio' => 'Tuxtla Gutiérrez', 'estado' => 'Chiapas', 
        'codigo_postal' => '29000', 'region' => 'sur',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Ultrasonido', 'Farmacia'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Sonora
    [
        'id' => 16, 'nombre' => 'Hospital General de Hermosillo', 'direccion' => 'Av. Reforma 219', 
        'telefono' => '(662) 259-0900', 'municipio' => 'Hermosillo', 'estado' => 'Sonora', 
        'codigo_postal' => '83000', 'region' => 'noroeste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ],
    // Tabasco
    [
        'id' => 17, 'nombre' => 'Hospital Regional de Villahermosa', 'direccion' => 'Av. Universidad 703', 
        'telefono' => '(993) 312-1212', 'municipio' => 'Villahermosa', 'estado' => 'Tabasco', 
        'codigo_postal' => '86000', 'region' => 'sureste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Cuidados Intensivos Neonatales', 'Ambulancia', 'Hospitalización'],
        'especialidades' => ['Pediatría', 'Neonatología', 'Ginecología', 'Odontología', 'Endodoncia']
    ],
    // Yucatán
    [
        'id' => 18, 'nombre' => 'Hospital General de Mérida', 'direccion' => 'Av. Itzáes 242', 
        'telefono' => '(999) 924-5800', 'municipio' => 'Mérida', 'estado' => 'Yucatán', 
        'codigo_postal' => '97000', 'region' => 'sureste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Quintana Roo
    [
        'id' => 19, 'nombre' => 'Hospital General de Cancún', 'direccion' => 'Av. Bonampak Mz. 1 Lt. 1', 
        'telefono' => '(998) 881-3400', 'municipio' => 'Cancún', 'estado' => 'Quintana Roo', 
        'codigo_postal' => '77500', 'region' => 'sureste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X', 'Farmacia'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Odontología', 'Periodoncia']
    ],
    // Tlaxcala
    [
        'id' => 20, 'nombre' => 'Hospital General de Tlaxcala', 'direccion' => 'Blvd. Revolución 100', 
        'telefono' => '(246) 462-1200', 'municipio' => 'Tlaxcala', 'estado' => 'Tlaxcala', 
        'codigo_postal' => '90000', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología']
    ],
    // Querétaro
    [
        'id' => 21, 'nombre' => 'Hospital General de Querétaro', 'direccion' => 'Av. 5 de Febrero 132', 
        'telefono' => '(442) 216-5000', 'municipio' => 'Querétaro', 'estado' => 'Querétaro', 
        'codigo_postal' => '76000', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Tomografía', 'Resonancia Magnética', 'Hospitalización'],
        'especialidades' => ['Cardiología', 'Neurología', 'Oftalmología', 'Odontología', 'Medicina General']
    ],
    // Hidalgo
    [
        'id' => 22, 'nombre' => 'Hospital General de Pachuca', 'direccion' => 'Blvd. Felipe Ángeles 101', 
        'telefono' => '(771) 717-3500', 'municipio' => 'Pachuca', 'estado' => 'Hidalgo', 
        'codigo_postal' => '42080', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Rayos X', 'Laboratorio Clínico'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ],
    // Morelos
    [
        'id' => 23, 'nombre' => 'Hospital General de Cuernavaca', 'direccion' => 'Av. Plan de Ayala 263', 
        'telefono' => '(777) 314-2424', 'municipio' => 'Cuernavaca', 'estado' => 'Morelos', 
        'codigo_postal' => '62050', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Ultrasonido', 'Terapia Intensiva', 'Ambulancia'],
        'especialidades' => ['Pediatría', 'Neumología', 'Alergología', 'Odontología', 'Ortodoncia']
    ],
    // Durango
    [
        'id' => 24, 'nombre' => 'Hospital General de Durango', 'direccion' => 'Blvd. Felipe Pescador 1820', 
        'telefono' => '(618) 812-3400', 'municipio' => 'Durango', 'estado' => 'Durango', 
        'codigo_postal' => '34000', 'region' => 'norte',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // San Luis Potosí
    [
        'id' => 25, 'nombre' => 'Hospital General de San Luis Potosí', 'direccion' => 'Av. Carranza 2305', 
        'telefono' => '(444) 813-3030', 'municipio' => 'San Luis Potosí', 'estado' => 'San Luis Potosí', 
        'codigo_postal' => '78250', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X', 'Ultrasonido'],
        'especialidades' => ['Ginecología', 'Obstetricia', 'Pediatría', 'Odontología', 'Medicina General']
    ],
    // Zacatecas
    [
        'id' => 26, 'nombre' => 'Hospital General de Zacatecas', 'direccion' => 'Calzada de la Paz 434', 
        'telefono' => '(492) 925-1000', 'municipio' => 'Zacatecas', 'estado' => 'Zacatecas', 
        'codigo_postal' => '98000', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología']
    ],
    // Aguascalientes
    [
        'id' => 27, 'nombre' => 'Hospital General de Aguascalientes', 'direccion' => 'Av. Universidad 1001', 
        'telefono' => '(449) 910-2500', 'municipio' => 'Aguascalientes', 'estado' => 'Aguascalientes', 
        'codigo_postal' => '20100', 'region' => 'centro',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Rayos X', 'Laboratorio Clínico'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ],
    // Nayarit
    [
        'id' => 28, 'nombre' => 'Hospital General de Tepic', 'direccion' => 'Av. México 280', 
        'telefono' => '(311) 214-6000', 'municipio' => 'Tepic', 'estado' => 'Nayarit', 
        'codigo_postal' => '63000', 'region' => 'occidente',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Farmacia'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Campeche
    [
        'id' => 29, 'nombre' => 'Hospital General de Campeche', 'direccion' => 'Av. Central 235', 
        'telefono' => '(981) 816-2500', 'municipio' => 'Campeche', 'estado' => 'Campeche', 
        'codigo_postal' => '24000', 'region' => 'sureste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Cirugía General']
    ],
    // Colima
    [
        'id' => 30, 'nombre' => 'Hospital General de Colima', 'direccion' => 'Av. Calz. Galván 305', 
        'telefono' => '(312) 316-1000', 'municipio' => 'Colima', 'estado' => 'Colima', 
        'codigo_postal' => '28000', 'region' => 'occidente',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología']
    ],
    // Baja California Sur
    [
        'id' => 31, 'nombre' => 'Hospital General de La Paz', 'direccion' => 'Av. 5 de Febrero 520', 
        'telefono' => '(612) 122-4800', 'municipio' => 'La Paz', 'estado' => 'Baja California Sur', 
        'codigo_postal' => '23000', 'region' => 'noroeste',
        'servicios' => ['Urgencias 24/7', 'Consultas Externas', 'Laboratorio Clínico', 'Rayos X'],
        'especialidades' => ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología']
    ]
];

// =============================================================================
// INICIALIZACIÓN DEL SISTEMA
// =============================================================================

// Crear lista doblemente circular con todos los hospitales
$listaHospitales = new ListaDoblementeCircularHospitales();
foreach ($hospitalesPorEstado as $hospital) {
    $listaHospitales->insertar($hospital);
}

// =============================================================================
// PROCESAMIENTO DE BÚSQUEDAS - BÚSQUEDA SECUENCIAL
// =============================================================================

$resultadosBusqueda = [];
$terminoBusqueda = '';
$tipoBusqueda = 'general';
$mostrarResultados = false;

if (isset($_GET['buscar']) && !empty(trim($_GET['busqueda']))) {
    $terminoBusqueda = trim($_GET['busqueda']);
    $tipoBusqueda = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : 'general';
    $resultadosBusqueda = $listaHospitales->busquedaSecuencial($terminoBusqueda, $tipoBusqueda);
    $mostrarResultados = true;
}

// =============================================================================
// PROCESAMIENTO DE LOGIN
// =============================================================================

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (isset($usuarios[$username]) && $usuarios[$username]['password'] === $password) {
        $_SESSION['usuario'] = $usuarios[$username];
        $_SESSION['usuario']['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error_login = "Usuario o contraseña incorrectos";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// =============================================================================
// INTERFAZ DE USUARIO - HTML
// =============================================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Healthnet - Cuidando tu salud</title>
    <link rel="stylesheet" href="cagaderoAlex.css">    
</head>
<body>
    <!-- PANEL DE USUARIO -->
    <div class="user-panel">
        <div class="container">
            <div class="user-info">
                <div class="user-welcome">
                    <?php if (isset($_SESSION['usuario'])): ?>
                        Bienvenido, <span><?php echo $_SESSION['usuario']['nombre']; ?></span> 
                        (<?php echo $_SESSION['usuario']['tipo']; ?>)
                    <?php else: ?>
                        Bienvenido
                    <?php endif; ?>
                </div>
                <div class="user-actions">
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <a href="?logout=1">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="#" onclick="abrirLogin()">Iniciar Sesión</a>
                        <a href="#" onclick="abrirLogin()">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE LOGIN -->
    <div id="modalLogin" class="modal">
        <div class="modal-content">
            <h2>Iniciar Sesión</h2>
            <?php if (isset($error_login)): ?>
                <div class="mensaje error"><?php echo $error_login; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="cerrarLogin()">Cancelar</button>
                    <button type="submit" class="btn-login" name="login">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- HEADER PRINCIPAL -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon"><img src="imagen/logoH.png" width="50px" height="50px" ></div>
                    <h1>Hospital Healthnet</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li>
                            <!-- FORMULARIO DE BÚSQUEDA -->
                            <form method="GET" class="buscador-header" id="formBuscador">
                                <input type="text" name="busqueda" placeholder="Buscar hospitales, servicios, especialidades..." 
                                value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                                <input type="hidden" name="tipo_busqueda" id="tipoBusqueda" value="<?php echo $tipoBusqueda; ?>">
                                <button type="submit" name="buscar">🔍</button>
                            </form>
                        </li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#especialidades">Especialidades</a></li>
                        <li><a href="#contacto" class="cta-button">Pedir Cita</a></li>
                    </ul>
                </nav> 
            </div>
        </div>
    </header>

    <!-- SECCIÓN DE RESULTADOS DE BÚSQUEDA -->
    <?php if ($mostrarResultados): ?>
    <section id="resultados-busqueda" class="resultados-section">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">Resultados de Búsqueda</h2>
                <p style="color: rgba(255,255,255,0.8);">
                    Búsqueda: "<?php echo htmlspecialchars($terminoBusqueda); ?>"
                    <span class="metodo-busqueda">
                        
                        <?php 
                            switch($tipoBusqueda) {
                                case 'servicios': echo 'Búsqueda por Servicios'; break;
                                case 'especialidades': echo 'Búsqueda por Especialidades'; break;
                                default: echo 'Búsqueda General'; break;
                            }
                        ?>
                    </span>
                </p>
            </div>

            <!-- FILTROS DE BÚSQUEDA -->
            <div class="filtros-busqueda">
                <button type="button" class="filtro-btn <?php echo $tipoBusqueda == 'general' ? 'active' : ''; ?>" 
                        onclick="cambiarFiltro('general')">
                    🔍 Búsqueda General
                </button>
                <button type="button" class="filtro-btn <?php echo $tipoBusqueda == 'servicios' ? 'active' : ''; ?>" 
                        onclick="cambiarFiltro('servicios')">
                    🏥 Servicios Hospitalarios
                </button>
                <button type="button" class="filtro-btn <?php echo $tipoBusqueda == 'especialidades' ? 'active' : ''; ?>" 
                        onclick="cambiarFiltro('especialidades')">
                    👨‍⚕️ Especialidades Médicas
                </button>
            </div>
            
            <!-- RESULTADOS DE BÚSQUEDA -->
            <div class="resultados-container">
                <?php if (!empty($resultadosBusqueda)): ?>
                    <div class="contador-resultados">
                        <?php echo count($resultadosBusqueda); ?> resultado(s) encontrado(s)
                    </div>
                    
                    <div class="hospitales-grid">
                        <?php foreach ($resultadosBusqueda as $hospital): ?>
                            <div class="hospital-card">
                                <h3>🏥 <?php echo $hospital['nombre']; ?></h3>
                                <p><strong>📍 Dirección:</strong> <?php echo $hospital['direccion']; ?></p>
                                <p><strong>📞 Teléfono:</strong> <?php echo $hospital['telefono']; ?></p>
                                <p><strong>🏙️ Municipio:</strong> <?php echo $hospital['municipio']; ?></p>
                                <p><strong>🏛️ Estado:</strong> <?php echo $hospital['estado']; ?></p>
                                
                                <?php if (isset($hospital['servicios'])): ?>
                                <div class="servicios-hospital">
                                    <strong>🛠️ Servicios:</strong>
                                    <div class="etiquetas">
                                        <?php foreach (array_slice($hospital['servicios'], 0, 3) as $servicio): ?>
                                            <span class="etiqueta"><?php echo $servicio; ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($hospital['servicios']) > 3): ?>
                                            <span class="etiqueta">+<?php echo count($hospital['servicios']) - 3; ?> más</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($hospital['especialidades'])): ?>
                                <div class="especialidades-hospital">
                                    <strong>🎯 Especialidades:</strong>
                                    <div class="etiquetas">
                                        <?php foreach (array_slice($hospital['especialidades'], 0, 3) as $especialidad): ?>
                                            <span class="etiqueta especialidad"><?php echo $especialidad; ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($hospital['especialidades']) > 3): ?>
                                            <span class="etiqueta">+<?php echo count($hospital['especialidades']) - 3; ?> más</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <button onclick="seleccionarHospital('<?php echo $hospital['nombre']; ?>')" 
                                        class="btn-seleccionar">
                                    Seleccionar este Hospital
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="sin-resultados">
                        <h3>😔 No se encontraron resultados para "<?php echo htmlspecialchars($terminoBusqueda); ?>"</h3>
                        <p>Intenta con otros términos de búsqueda como: "Odontología", "Urgencias", "Cardiología", etc.</p>
                        
                        <div class="sugerencias-busqueda">
                            <h4>💡 Sugerencias de búsqueda:</h4>
                            <div class="sugerencias-lista">
                                <span class="sugerencia" onclick="buscarSugerencia('Odontología')">Odontología</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Urgencias')">Urgencias</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Cardiología')">Cardiología</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Pediatría')">Pediatría</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Cirugía')">Cirugía</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Ciudad de México')">Ciudad de México</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Jalisco')">Jalisco</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Nuevo León')">Nuevo León</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SECCIÓN HERO/INICIO -->
    <section id="inicio" class="hero">
        <div class="container">
            <h2>Cuidamos de tu salud con excelencia</h2>
            <p>Hospital Healthnet - Siempre brindando atención médica de calidad con equipo humano comprometido con tu bienestar.</p>
            <div class="hero-buttons">
                <a href="#contacto" class="btn btn-primary">Solicitar Cita</a>
                <a href="#servicios" class="btn btn-secondary">Conocer Servicios</a>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE SERVICIOS -->
    <section id="servicios" class="services">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Servicios</h2>
                <p>Ofrecemos una amplia gama de servicios médicos con los más altos estándares de calidad</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🚑</div>
                    <div class="service-content">
                        <h3>Urgencias 24/7</h3>
                        <p>Atención médica inmediata las 24 horas del día, los 365 días del año con personal altamente capacitado.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🩺</div>
                    <div class="service-content">
                        <h3>Consultas Externas</h3>
                        <p>Consulta con especialistas en todas las áreas médicas con citas programadas y atención personalizada.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔪</div>
                    <div class="service-content">
                        <h3>Cirugías</h3>
                        <p>Quirófanos equipados con tecnología de última generación para procedimientos de alta complejidad.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🧪</div>
                    <div class="service-content">
                        <h3>Laboratorio Clínico</h3>
                        <p>Análisis clínicos y estudios de diagnóstico completos con resultados precisos y oportunos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE ESPECIALIDADES -->
    <section id="especialidades" class="specialties">
        <div class="container">
            <div class="section-title">
                <h2>Nuestras especialidades</h2>
                <p>Contamos con especialistas en todas las áreas de la medicina para brindarte la mejor atención</p>
            </div>
            <div class="specialties-grid">
                <div class="specialty">
                    <i>❤️</i>
                    <h3>Cardiología</h3>
                    <h4>$900</h4>
                </div>
                <div class="specialty">
                    <i>👶</i>
                    <h3>Pediatría</h3>
                    <h4>$800</h4>
                </div>
                <div class="specialty">
                    <i>👩</i>
                    <h3>Ginecología</h3>
                    <h4>$940</h4>
                </div>
                <div class="specialty">
                    <i>🦴</i>
                    <h3>Traumatología</h3>
                    <h4>$1000</h4>
                </div>
                <div class="specialty">
                    <i>🧠</i>
                    <h3>Neurología</h3>
                    <h4>$850</h4>
                </div>
                <div class="specialty">
                    <i>🎗️</i>
                    <h3>Oncología</h3>
                    <h4>$700</h4>
                </div>
                <div class="specialty">
                    <i>👁️</i>
                    <h3>Oftalmología</h3>
                    <h4>$600</h4>
                </div>
                <div class="specialty">
                    <i>✃</i>
                    <h3>Cirugía</h3>
                    <h4>$650</h4>
                </div>
                <div class="specialty">
                    <i>💆🏻‍♀️</i>
                    <h3>Dermatología</h3>
                    <h4>$800</h4>
                </div>
                <div class="specialty">
                    <i>🩹</i>
                    <h3>Medicina general</h3>
                    <h4>$820</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE CONTACTO Y CITAS -->
    <section id="contacto" class="contact">
        <div class="container">
            <div class="section-title">
                <h2>Solicitar Cita Médica</h2>
                <p>Completa el formulario para agendar tu cita.</p>
            </div>

            <?php if (isset($_GET['exito']) && $_GET['exito'] == '1'): ?>
                <div class="mensaje exito">¡Gracias! Tu cita ha sido solicitada correctamente. Te contactaremos pronto.</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                <div class="mensaje error">Error: Por favor completa todos los campos requeridos.</div>
            <?php endif; ?>

            <div class="contact-container">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h3>Teléfono</h3>
                            <p>(555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div>
                            <h3>Email</h3>
                            <p>info@hospitalHealthnet.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div>
                            <h3>Horario de Atención</h3>
                            <p>Lunes a Viernes: 7:00 am - 9:00 pm</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Formulario de Cita</h3>
                    
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <button type="button" class="auto-fill-btn" onclick="autoCompletarDatos()">
                            Auto-completar con mis datos
                        </button>
                    <?php else: ?>
                        <div style="background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                            <small>💡 <a href="#" onclick="abrirLogin()" style="color: #0d47a1;">Inicia sesión</a> para auto-completar tus datos</small>
                        </div>
                    <?php endif; ?>

                    <form action="procesar_cita.php" method="POST" id="formCita">
                        <div class="form-group">
                            <label for="hospital">Hospital Seleccionado *</label>
                            <input type="text" id="hospital" name="hospital" required readonly 
                                   placeholder="Selecciona un hospital de la sección anterior">
                        </div>
                        <div class="form-group">
                            <label for="nombre">Nombre completo *</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo electrónico *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>
                        <div class="form-group">
                            <label for="especialidad">Especialidad requerida *</label>
                            <select id="especialidad" name="especialidad" required onchange="calcularTotal()">
                                <option value="">Selecciona una especialidad</option>
                                <option value="cardiologia" data-precio="900">Cardiología - $900</option>
                                <option value="pediatria" data-precio="800">Pediatría - $800</option>
                                <option value="ginecologia" data-precio="940">Ginecología - $940</option>
                                <option value="traumatologia" data-precio="1000">Traumatología - $1,000</option>
                                <option value="neurologia" data-precio="850">Neurología - $850</option>
                                <option value="oncologia" data-precio="700">Oncología - $700</option>
                                <option value="oftamologo" data-precio="600">Oftalmología - $600</option>
                                <option value="cirugia" data-precio="650">Cirugía - $650</option>
                                <option value="dermatologia" data-precio="800">Dermatología - $800</option>
                                <option value="medicina_general" data-precio="820">Medicina General - $820</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="total">Total a Pagar:</label>
                            <input type="text" id="total" name="total" readonly 
                                   style="background-color: #e8f5e8; font-weight: bold; color: #2e7d32; font-size: 1.1rem;"
                                   placeholder="Selecciona una especialidad">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_preferida">Fecha preferida *</label>
                            <input type="date" id="fecha_preferida" name="fecha_preferida" required>
                        </div>
                        <div class="form-group">
                            <label for="mensaje">Mensaje adicional</label>
                            <textarea id="mensaje" name="mensaje" placeholder="Describe brevemente tu consulta o síntomas"></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Solicitar Cita</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Hospital healthnet</h3>
                    <p>Siempre brindando atención médica de calidad con equipo humano comprometido con tu bienestar.</p>
                </div>
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#especialidades">Especialidades</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Servicios</h3>
                    <ul>
                        <li><a href="#">Urgencias</a></li>
                        <li><a href="#">Consultas</a></li>
                        <li><a href="#">Cirugías</a></li>
                        <li><a href="#">Laboratorio</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contáctanos</h3>
                    <ul>
                        <li>📞 (555) 123-4567</li>
                        <li>✉️ info@hospitalHealthnet.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Hospital Healthnet.</p>
            </div>
        </div>
    </footer>

    <!-- ============================================================================= -->
    <!-- JAVASCRIPT - FUNCIONALIDADES DEL SISTEMA -->
    <!-- ============================================================================= -->
    <script>
        // FUNCIONES DE LOGIN
        function abrirLogin() {
            document.getElementById('modalLogin').style.display = 'block';
        }

        function cerrarLogin() {
            document.getElementById('modalLogin').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modalLogin');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // FUNCIÓN PARA AUTO-COMPLETAR DATOS DEL USUARIO
        function autoCompletarDatos() {
            <?php if (isset($_SESSION['usuario'])): ?>
                document.getElementById('nombre').value = '<?php echo $_SESSION['usuario']['nombre']; ?>';
                document.getElementById('email').value = '<?php echo $_SESSION['usuario']['email']; ?>';
                document.getElementById('telefono').value = '<?php echo $_SESSION['usuario']['telefono']; ?>';
                
                alert('Datos auto-completados correctamente.');
            <?php else: ?>
                alert('Debes iniciar sesión para usar esta función.');
                abrirLogin();
            <?php endif; ?>
        }

        // FUNCIÓN PARA SELECCIONAR HOSPITAL
        function seleccionarHospital(nombreHospital) {
            document.getElementById('hospital').value = nombreHospital;
            document.getElementById('contacto').scrollIntoView({ behavior: 'smooth' });
            
            alert('Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
        }

        // FUNCIÓN PARA CALCULAR TOTAL DE CITA
        function calcularTotal() {
            const especialidadSelect = document.getElementById('especialidad');
            const totalInput = document.getElementById('total');
            const opcionSeleccionada = especialidadSelect.options[especialidadSelect.selectedIndex];
            
            if (opcionSeleccionada.value !== '') {
                const precio = opcionSeleccionada.getAttribute('data-precio');
                totalInput.value = '$' + precio + ' MXN';
            } else {
                totalInput.value = '';
            }
        }

        // FUNCIONES DE BÚSQUEDA
        function cambiarFiltro(tipo) {
            document.getElementById('tipoBusqueda').value = tipo;
            
            // Actualizar clases activas
            document.querySelectorAll('.filtro-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Si hay término de búsqueda, enviar formulario automáticamente
            const busquedaInput = document.querySelector('input[name="busqueda"]');
            if (busquedaInput.value.trim() !== '') {
                document.getElementById('formBuscador').submit();
            }
        }

        function buscarSugerencia(termino) {
            document.querySelector('input[name="busqueda"]').value = termino;
            document.getElementById('formBuscador').submit();
        }

        // SCROLL SUAVE PARA NAVEGACIÓN
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // EFECTO DE SCROLL EN HEADER
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        });

        // SCROLL AUTOMÁTICO A RESULTADOS CUANDO HAY BÚSQUEDA
        <?php if ($mostrarResultados): ?>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('resultados-busqueda').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);
        });
        <?php endif; ?>

        // INICIALIZAR CÁLCULO DE TOTAL AL CARGAR LA PÁGINA
        document.addEventListener('DOMContentLoaded', function() {
            calcularTotal();
        });
    </script>
</body>
</html>
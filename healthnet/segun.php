<?php
session_start();

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

// LISTA DE ESPECIALIDADES MÉDICAS
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

// CLASE LISTA DOBLEMENTE CIRCULAR
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
    
    // FUNCIÓN PARA ELIMINAR ACENTOS Y NORMALIZAR TEXTO
    private function normalizarTexto($texto) {
        if (empty($texto)) return '';
        
        // Convertir a minúsculas
        $texto = mb_strtolower($texto, 'UTF-8');
        
        // Reemplazar caracteres con acentos
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        
        return strtr($texto, $acentos);
    }
    
    // BÚSQUEDA SECUENCIAL MEJORADA Y SIMPLIFICADA
    public function busquedaSecuencial($termino, $tipoBusqueda = 'general') {
        if ($this->cabeza === null) return [];
        
        $resultados = [];
        $actual = $this->cabeza;
        $contador = 0;
        
        // Normalizar el término de búsqueda
        $terminoNormalizado = $this->normalizarTexto($termino);
        
        do {
            $hospital = $actual->hospital;
            $encontrado = false;
            
            switch($tipoBusqueda) {
                case 'general':
                    // Búsqueda en todos los campos (case insensitive y sin acentos)
                    $campos = ['nombre', 'municipio', 'direccion', 'estado', 'codigo_postal'];
                    foreach ($campos as $campo) {
                        if (isset($hospital[$campo]) && 
                            stripos($this->normalizarTexto($hospital[$campo]), $terminoNormalizado) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }
                    
                    // Buscar en servicios
                    if (!$encontrado && isset($hospital['servicios'])) {
                        foreach($hospital['servicios'] as $servicio) {
                            if (stripos($this->normalizarTexto($servicio), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    
                    // Buscar en especialidades
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

// HOSPITALES POR ESTADO - UN HOSPITAL MÍNIMO POR CADA ESTADO
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

// CREAR LISTA DOBLEMENTE CIRCULAR CON TODOS LOS HOSPITALES POR ESTADO
$listaHospitales = new ListaDoblementeCircularHospitales();
foreach ($hospitalesPorEstado as $hospital) {
    $listaHospitales->insertar($hospital);
}

// PROCESAR BÚSQUEDA MEJORADA
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

// Hospitales por región (para mantener compatibilidad con la sección existente)
$hospitales = [
    'norte' => [
        ['nombre' => 'Hospital General de Chihuahua', 'direccion' => 'Calle 24a 2201', 'telefono' => '(614) 429-3300'],
        ['nombre' => 'Hospital General de Durango', 'direccion' => 'Blvd. Felipe Pescador 1820', 'telefono' => '(618) 812-3400']
    ],
    'sur' => [
        ['nombre' => 'Hospital General de Oaxaca', 'direccion' => 'Calzada San Felipe 1212', 'telefono' => '(951) 516-2020'],
        ['nombre' => 'Hospital General de Tuxtla Gutiérrez', 'direccion' => 'Blvd. Belisario Domínguez 1080', 'telefono' => '(961) 614-5050']
    ],
    'este' => [
        ['nombre' => 'Hospital Universitario de Monterrey', 'direccion' => 'Madero y Aguirre Pequeño', 'telefono' => '(81) 8347-1010'],
        ['nombre' => 'Hospital General de Saltillo', 'direccion' => 'Blvd. Venustiano Carranza 2400', 'telefono' => '(844) 416-1200']
    ],
    'oeste' => [
        ['nombre' => 'Hospital Civil de Guadalajara', 'direccion' => 'Salvador Quevedo y Zubieta 750', 'telefono' => '(33) 3614-7788'],
        ['nombre' => 'Hospital General de Tepic', 'direccion' => 'Av. México 280', 'telefono' => '(311) 214-6000']
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Healthnet - Cuidando tu salud</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .mensaje {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .user-panel {
            background: linear-gradient(135deg, #7AB2D3 0%, #7AB2D3 100%);
            color: white;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-welcome span {
            font-weight: bold;
        }

        .user-actions a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .user-actions a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Modal de Login */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }

        .modal h2 {
            color: #7AB2D3;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
        }

        .modal-buttons button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-login {
            background-color: #7AB2D3;
            color: white;
        }

        .btn-login:hover {
            background-color: #1a6fc4;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        header {
            background: linear-gradient(135deg, #1a6fc4 50%, #7AB2D3 100%);
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h1 {
            font-size: 1.8rem;
            margin-left: 10px;
        }

        .logo-icon {
            font-size: 2rem;
        }

        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }

        nav ul li {
            margin-left: 25px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* ESTILOS PARA EL BUSCADOR EN HEADER */
        .buscador-header {
            display: flex;
            align-items: center;
        }

        .buscador-header input {
            padding: 8px 15px;
            border: none;
            border-radius: 20px 0 0 20px;
            width: 250px;
            font-size: 0.9rem;
        }

        .buscador-header button {
            padding: 8px 15px;
            border: none;
            border-radius: 0 20px 20px 0;
            background: #4A628A;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .buscador-header button:hover {
            background: #3c5280;
        }

        .cta-button {
            background-color: #4A628A;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .search-hospitales {
            background-color: #e3f2fd;
            padding: 60px 0;
        }

        .regiones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .region-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .region-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .region-card h3 {
            color: #0d47a1;
            margin-bottom: 15px;
        }

        .hospitales-list {
            display: none;
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }

        .hospital-card {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #0d47a1;
        }

        .hospital-card h4 {
            color: #0d47a1;
            margin-bottom: 8px;
        }

        .hospital-card button {
            background-color: #0d47a1;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .hospital-card button:hover {
            background-color: #1a6fc4;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(13, 71, 161, 0.8), rgba(13, 71, 161, 0.9)), url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
        }

        .hero h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background-color: #141756ff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background-color: white;
            color: #1a6fc4;
            transform: translateY(-3px);
        }

        section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            color: #0d47a1;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
        }

        .service-icon {
            background-color: #e3f2fd;
            padding: 25px;
            text-align: center;
            font-size: 2.5rem;
            color: #0d47a1;
        }

        .service-content {
            padding: 25px;
        }

        .service-content h3 {
            margin-bottom: 15px;
            color: #0d47a1;
        }

        .specialties {
            background-color: #e3f2fd;
        }

        .specialties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .specialty {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .specialty:hover {
            background-color: #0d47a1;
            color: white;
            transform: scale(1.05);
        }

        .specialty i {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #0d47a1;
        }

        .specialty:hover i {
            color: white;
        }

        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
        }

        .contact-icon {
            background-color: #e3f2fd;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #0d47a1;
            font-size: 1.2rem;
        }

        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .contact-form h3 {
            margin-bottom: 20px;
            color: #0d47a1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .submit-btn {
            background-color: #4969a0ff;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #3178bfff;
        }

        .auto-fill-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .auto-fill-btn:hover {
            background-color: #218838;
        }

        footer {
            background-color: #3c69b7ff;
            color: white;
            padding: 50px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 10px;
        }

        .footer-column ul li a {
            color: #e3f2fd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-column ul li a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ESTILOS PARA RESULTADOS DE BÚSQUEDA */
        .resultados-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            color: white;
        }

        .resultados-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .resultado-item {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .resultado-item:hover {
            transform: translateX(5px);
            background: #e9ecef;
        }

        .resultado-titulo {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .resultado-info {
            color: #666;
            margin-bottom: 5px;
        }

        .sin-resultados {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .contador-resultados {
            background: #4ecdc4;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .metodo-busqueda {
            background: #ff6b6b;
            color: white;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: 10px;
        }

        /* NUEVOS ESTILOS PARA FILTROS DE BÚSQUEDA */
        .filtros-busqueda {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filtro-btn {
            padding: 10px 20px;
            border: 2px solid #4A628A;
            background: white;
            color: #4A628A;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filtro-btn.active {
            background: #4A628A;
            color: white;
        }

        .filtro-btn:hover {
            background: #4A628A;
            color: white;
            transform: translateY(-2px);
        }

        .etiquetas-hospital {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }

        .etiqueta {
            background: #e3f2fd;
            color: #0d47a1;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            border: 1px solid #bbdefb;
        }

        .etiqueta-servicio {
            background: #e8f5e8;
            color: #2e7d32;
            border-color: #c8e6c9;
        }

        .etiqueta-especialidad {
            background: #fff3e0;
            color: #ef6c00;
            border-color: #ffe0b2;
        }

        .info-extra {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            nav ul {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav ul li {
                margin: 5px 10px;
            }

            .buscador-header {
                margin: 10px 0;
            }

            .buscador-header input {
                width: 200px;
            }

            .hero h2 {
                font-size: 2.2rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .btn {
                width: 80%;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
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

<?php if ($mostrarResultados): ?>
<section id="resultados-busqueda" class="resultados-section">
    <div class="container">
        <div class="section-title">
            <h2 style="color: white;">Resultados de Búsqueda</h2>
            <p style="color: rgba(255,255,255,0.8);">
                Búsqueda: 
                <span class="metodo-busqueda">
                    Lista Doblemente Circular - 
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
        
        <div class="resultados-container">
            <?php if (!empty($resultadosBusqueda)): ?>
                <div class="contador-resultados">
                    <?php echo count($resultadosBusqueda); ?> resultado(s) encontrado(s) para: "<?php echo htmlspecialchars($terminoBusqueda); ?>"
                </div>
                
                <?php foreach ($resultadosBusqueda as $hospital): ?>
                    <div class="resultado-item">
                        <h3 class="resultado-titulo">🏥 <?php echo $hospital['nombre']; ?></h3>
                        <p class="resultado-info"><strong>📍 Dirección:</strong> <?php echo $hospital['direccion']; ?></p>
                        <p class="resultado-info"><strong>📞 Teléfono:</strong> <?php echo $hospital['telefono']; ?></p>
                        <p class="resultado-info"><strong>🏙️ Municipio:</strong> <?php echo $hospital['municipio']; ?></p>
                        <p class="resultado-info"><strong>🏛️ Estado:</strong> <?php echo $hospital['estado']; ?></p>
                        
                        <?php if (isset($hospital['servicios'])): ?>
                        <div class="info-extra">
                            <strong>🛠️ Servicios:</strong>
                            <div class="etiquetas-hospital">
                                <?php foreach (array_slice($hospital['servicios'], 0, 5) as $servicio): ?>
                                    <span class="etiqueta etiqueta-servicio"><?php echo $servicio; ?></span>
                                <?php endforeach; ?>
                                <?php if (count($hospital['servicios']) > 5): ?>
                                    <span class="etiqueta">+<?php echo count($hospital['servicios']) - 5; ?> más</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($hospital['especialidades'])): ?>
                        <div class="info-extra">
                            <strong>🎯 Especialidades:</strong>
                            <div class="etiquetas-hospital">
                                <?php foreach (array_slice($hospital['especialidades'], 0, 5) as $especialidad): ?>
                                    <span class="etiqueta etiqueta-especialidad"><?php echo $especialidad; ?></span>
                                <?php endforeach; ?>
                                <?php if (count($hospital['especialidades']) > 5): ?>
                                    <span class="etiqueta">+<?php echo count($hospital['especialidades']) - 5; ?> más</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <button onclick="seleccionarHospital('<?php echo $hospital['nombre']; ?>')" 
                                style="background: #667eea; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px;">
                            Seleccionar este Hospital
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sin-resultados">
                    <h3>😔 No se encontraron resultados para "<?php echo htmlspecialchars($terminoBusqueda); ?>"</h3>
                    <p>Intenta con otros términos de búsqueda como: "Odontología", "Urgencias", "Cardiología", etc.</p>
                    
                    <div style="margin-top: 20px;">
                        <h4>💡 Sugerencias de búsqueda:</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                            <span class="etiqueta" onclick="buscarSugerencia('Odontología')" style="cursor: pointer;">Odontología</span>
                            <span class="etiqueta" onclick="buscarSugerencia('Urgencias')" style="cursor: pointer;">Urgencias</span>
                            <span class="etiqueta" onclick="buscarSugerencia('Cardiología')" style="cursor: pointer;">Cardiología</span>
                            <span class="etiqueta" onclick="buscarSugerencia('Pediatría')" style="cursor: pointer;">Pediatría</span>
                            <span class="etiqueta" onclick="buscarSugerencia('Cirugía')" style="cursor: pointer;">Cirugía</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($mostrarResultados): ?>
            setTimeout(function() {
                document.getElementById('resultados-busqueda').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        <?php endif; ?>
    });
</script>
<?php endif; ?>

    <section id="inicio" class="hero">
        <div class="container">
            <h2>Cuidamos de tu salud con excelencia</h2>
            <p>Hospital Healthnet - Siempre brindando atención médica de calidad con equipo humano comprometido con tu bienestar.</p>
            <div class="hero-buttons">
                <a href="#contacto" class="btn btn-primary">Solicitar Cita</a>
                <a href="#buscar-hospitales" class="btn btn-secondary">Buscar Hospitales</a>
            </div>
        </div>
    </section>

    <!--
    <section id="buscar-hospitales" class="search-hospitales">
        <div class="container">
            <div class="section-title">
                <h2>Encuentra tu hospital por región</h2>
            </div>
            
            <div class="regiones-grid">
                <?php foreach ($hospitales as $region => $hospitales_region): ?>
                    <div class="region-card" onclick="mostrarHospitales('<?php echo $region; ?>')">
                        <h3>Región <?php echo ucfirst($region); ?></h3>
                        <p><?php echo count($hospitales_region); ?> hospitales disponibles</p>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php foreach ($hospitales as $region => $hospitales_region): ?>
                <div id="hospitales-<?php echo $region; ?>" class="hospitales-list">
                    <h3>Hospitales en la Región <?php echo ucfirst($region); ?></h3>
                    <?php foreach ($hospitales_region as $hospital): ?>
                        <div class="hospital-card">
                            <h4><?php echo $hospital['nombre']; ?></h4>
                            <p><strong>Dirección:</strong> <?php echo $hospital['direccion']; ?></p>
                            <p><strong>Teléfono:</strong> <?php echo $hospital['telefono']; ?></p>
                            <button onclick="seleccionarHospital('<?php echo $hospital['nombre']; ?>')">
                                Seleccionar este Hospital
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section> -->

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
                        <li><a href="#buscar-hospitales">Buscar Hospitales</a></li>
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

    <script>
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

        function mostrarHospitales(region) {
            const listas = document.querySelectorAll('.hospitales-list');
            listas.forEach(lista => {
                lista.style.display = 'none';
            });
            
            const listaSeleccionada = document.getElementById('hospitales-' + region);
            if (listaSeleccionada) {
                listaSeleccionada.style.display = 'block';
                
                listaSeleccionada.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function seleccionarHospital(nombreHospital) {
            document.getElementById('hospital').value = nombreHospital;
            document.getElementById('contacto').scrollIntoView({ behavior: 'smooth' });
            
            alert('Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
        }

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

        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        });

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

        document.addEventListener('DOMContentLoaded', function() {
            calcularTotal();
        });

        // FUNCIÓN PARA CAMBIAR FILTRO DE BÚSQUEDA
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

        // FUNCIÓN PARA BÚSQUEDA POR SUGERENCIA
        function buscarSugerencia(termino) {
            document.querySelector('input[name="busqueda"]').value = termino;
            document.getElementById('tipoBusqueda').value = 'especialidades';
            document.getElementById('formBuscador').submit();
        }

        // Scroll automático mejorado
        function scrollToResults() {
            const resultadosSection = document.getElementById('resultados-busqueda');
            if (resultadosSection) {
                setTimeout(() => {
                    resultadosSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start',
                        inline: 'nearest'
                    });
                }, 300);
            }
        }

        // Ejecutar scroll cuando hay resultados
        <?php if ($mostrarResultados): ?>
        document.addEventListener('DOMContentLoaded', scrollToResults);
        <?php endif; ?>
    </script>
</body>
</html>
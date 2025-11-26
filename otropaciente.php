<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthNet - Sistema de Pacientes</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --success: #27ae60;
            --warning: #f39c12;
            --dark: #34495e;
        }
        
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
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        nav a:hover, nav a.active {
            background: rgba(255,255,255,0.2);
        }
        
        /* User Panel */
        .user-panel {
            background: var(--dark);
            color: white;
            padding: 8px 0;
            font-size: 0.9rem;
        }
        
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-actions a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            transition: color 0.3s;
        }
        
        .user-actions a:hover {
            color: var(--secondary);
        }
        
        /* Main Content */
        .main-content {
            display: flex;
            min-height: calc(100vh - 140px);
        }
        
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: var(--dark);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--secondary);
            color: white;
        }
        
        .content-area {
            flex: 1;
            padding: 20px;
            background: white;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Page Headers */
        .page-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .page-header h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary);
        }
        
        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin: 0;
        }
        
        /* Search Section */
        .search-section {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .search-box {
            display: flex;
            margin-bottom: 20px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 4px 0 0 4px;
            font-size: 1rem;
        }
        
        .search-box button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .search-box button:hover {
            background: #c0392b;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active, .filter-btn:hover {
            background: white;
            color: var(--primary);
        }
        
        /* Hospital Cards */
        .hospital-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .hospital-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .hospital-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .hospital-header {
            background: var(--secondary);
            color: white;
            padding: 15px;
        }
        
        .hospital-body {
            padding: 15px;
        }
        
        .hospital-info {
            margin-bottom: 15px;
        }
        
        .hospital-info p {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
        }
        
        .hospital-info strong {
            min-width: 100px;
            display: inline-block;
        }
        
        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        
        .tag {
            background: #e1f0fa;
            color: var(--secondary);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .tag.specialty {
            background: #ffeaa7;
            color: #e17055;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background 0.3s;
            font-weight: 500;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-accent {
            background: var(--accent);
        }
        
        .btn-accent:hover {
            background: #c0392b;
        }
        
        /* Profile Section */
        .profile-container {
            display: flex;
            gap: 30px;
        }
        
        .profile-sidebar {
            width: 250px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 15px;
        }
        
        .profile-details {
            flex: 1;
        }
        
        .info-group {
            margin-bottom: 20px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .info-value {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid var(--secondary);
        }
        
        /* Policy Section */
        .policy-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .policy-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--success);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .policy-features {
            margin: 15px 0;
        }
        
        .policy-features ul {
            list-style: none;
            padding-left: 0;
        }
        
        .policy-features li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .policy-features li:before {
            content: "✓";
            color: var(--success);
            margin-right: 10px;
            font-weight: bold;
        }
        
        .policy-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Appointment Form */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            outline: none;
        }
        
        .auto-fill-btn {
            background: #e1f0fa;
            color: var(--secondary);
            border: 1px dashed var(--secondary);
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .auto-fill-btn:hover {
            background: #d1e8ff;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin: 0;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
        
        /* Messages */
        .message {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
            }
            
            .hospital-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- User Panel -->
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">🏥</div>
                    <h1>HealthNet</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="#busqueda" class="active">Búsqueda</a></li>
                        <li><a href="#perfil">Mi Perfil</a></li>
                        <li><a href="#poliza">Mi Póliza</a></li>
                        <li><a href="#cita">Agendar Cita</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#busqueda" class="active">🔍 Búsqueda de Hospitales</a></li>
                <li><a href="#perfil">👤 Mi Perfil</a></li>
                <li><a href="#poliza">📄 Mi Póliza</a></li>
                <li><a href="#cita">📅 Agendar Cita</a></li>
                <li><a href="#historial">📋 Historial Médico</a></li>
                <li><a href="#facturacion">💰 Facturación</a></li>
                <li><a href="#ayuda">❓ Ayuda</a></li>
            </ul>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Search Section -->
            <section id="busqueda">
                <div class="page-header">
                    <h2>Búsqueda de Hospitales</h2>
                    <p>Encuentra el hospital que necesitas según tu póliza y ubicación</p>
                </div>

                <div class="search-section">
                    <div class="search-container">
                        <form method="GET" id="formBuscador">
                            <div class="search-box">
                                <input type="text" name="busqueda" placeholder="Buscar hospitales, servicios, especialidades..." 
                                       value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                <button type="submit" name="buscar">🔍 Buscar</button>
                            </div>
                            <input type="hidden" name="tipo_busqueda" id="tipoBusqueda" value="<?php echo $tipoBusqueda; ?>">
                            
                            <div class="filter-buttons">
                                <button type="button" class="filter-btn <?php echo $tipoBusqueda == 'general' ? 'active' : ''; ?>" 
                                        onclick="cambiarFiltro('general')">
                                    🔍 Búsqueda General
                                </button>
                                <button type="button" class="filter-btn <?php echo $tipoBusqueda == 'servicios' ? 'active' : ''; ?>" 
                                        onclick="cambiarFiltro('servicios')">
                                    🏥 Servicios Hospitalarios
                                </button>
                                <button type="button" class="filter-btn <?php echo $tipoBusqueda == 'especialidades' ? 'active' : ''; ?>" 
                                        onclick="cambiarFiltro('especialidades')">
                                    👨‍⚕️ Especialidades Médicas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php echo empty($terminoBusqueda) ? 'Todos los Hospitales Disponibles' : 'Resultados de Búsqueda'; ?>
                        </h3>
                        <div class="result-count">
                            <?php echo count($resultadosBusqueda); ?> hospital(es) encontrado(s)
                        </div>
                    </div>

                    <?php if (!empty($resultadosBusqueda)): ?>
                        <div class="hospital-grid">
                            <?php foreach ($resultadosBusqueda as $hospital): ?>
                                <div class="hospital-card">
                                    <div class="hospital-header">
                                        <h3>🏥 <?php echo $hospital['nombre']; ?></h3>
                                    </div>
                                    <div class="hospital-body">
                                        <div class="hospital-info">
                                            <p><strong>📍</strong> <?php echo $hospital['direccion']; ?></p>
                                            <p><strong>📞</strong> <?php echo $hospital['telefono']; ?></p>
                                            <p><strong>🏙️</strong> <?php echo $hospital['municipio']; ?>, <?php echo $hospital['estado']; ?></p>
                                            <p><strong>🕒</strong> <?php echo $hospital['horario']; ?></p>
                                        </div>
                                        
                                        <?php if (isset($hospital['servicios'])): ?>
                                        <div class="servicios-hospital">
                                            <strong>🛠️ Servicios:</strong>
                                            <div class="tags">
                                                <?php foreach (array_slice($hospital['servicios'], 0, 3) as $servicio): ?>
                                                    <span class="tag"><?php echo $servicio; ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hospital['servicios']) > 3): ?>
                                                    <span class="tag">+<?php echo count($hospital['servicios']) - 3; ?> más</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (isset($hospital['especialidades'])): ?>
                                        <div class="especialidades-hospital">
                                            <strong>🎯 Especialidades:</strong>
                                            <div class="tags">
                                                <?php foreach (array_slice($hospital['especialidades'], 0, 3) as $especialidad): ?>
                                                    <span class="tag specialty"><?php echo $especialidad; ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hospital['especialidades']) > 3): ?>
                                                    <span class="tag">+<?php echo count($hospital['especialidades']) - 3; ?> más</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <button onclick="seleccionarHospital('<?php echo $hospital['nombre']; ?>')" 
                                                class="btn" style="width: 100%; margin-top: 15px;">
                                            Seleccionar este Hospital
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="message info">
                            <h3>No se encontraron resultados para "<?php echo htmlspecialchars($terminoBusqueda); ?>"</h3>
                            <p>Intenta con otros términos de búsqueda o ajusta los filtros.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Profile Section -->
            <section id="perfil" style="display: none;">
                <div class="page-header">
                    <h2>Mi Perfil</h2>
                    <p>Información personal y datos de contacto</p>
                </div>

                <div class="profile-container">
                    <div class="profile-sidebar">
                        <div class="profile-avatar">
                            <?php 
                                if (isset($_SESSION['usuario'])) {
                                    echo strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1));
                                } else {
                                    echo "U";
                                }
                            ?>
                        </div>
                        <h3>
                            <?php 
                                if (isset($_SESSION['usuario'])) {
                                    echo $_SESSION['usuario']['nombre'];
                                } else {
                                    echo "Usuario";
                                }
                            ?>
                        </h3>
                        <p>
                            <?php 
                                if (isset($_SESSION['usuario'])) {
                                    echo $_SESSION['usuario']['tipo'];
                                } else {
                                    echo "Paciente";
                                }
                            ?>
                        </p>
                    </div>

                    <div class="profile-details">
                        <div class="card">
                            <h3 class="card-title">Información Personal</h3>
                            
                            <div class="info-group">
                                <div class="info-label">Nombre completo</div>
                                <div class="info-value">
                                    <?php 
                                        if (isset($_SESSION['usuario'])) {
                                            echo $_SESSION['usuario']['nombre'];
                                        } else {
                                            echo "No disponible";
                                        }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Correo electrónico</div>
                                <div class="info-value">
                                    <?php 
                                        if (isset($_SESSION['usuario'])) {
                                            echo $_SESSION['usuario']['email'];
                                        } else {
                                            echo "No disponible";
                                        }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Teléfono</div>
                                <div class="info-value">
                                    <?php 
                                        if (isset($_SESSION['usuario'])) {
                                            echo $_SESSION['usuario']['telefono'];
                                        } else {
                                            echo "No disponible";
                                        }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Tipo de usuario</div>
                                <div class="info-value">
                                    <?php 
                                        if (isset($_SESSION['usuario'])) {
                                            echo $_SESSION['usuario']['tipo'];
                                        } else {
                                            echo "Paciente";
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Policy Section -->
            <section id="poliza" style="display: none;">
                <div class="page-header">
                    <h2>Mi Póliza</h2>
                    <p>Gestiona tu tipo de póliza y cobertura médica</p>
                </div>

                <div class="policy-card">
                    <div class="policy-badge">ACTIVA</div>
                    <h3>Póliza <?php echo obtenerTipoPoliza(); ?></h3>
                    <p>Tu póliza actual te ofrece cobertura según el plan seleccionado.</p>
                    
                    <div class="policy-features">
                        <h4>Cobertura:</h4>
                        <ul>
                            <?php if (obtenerTipoPoliza() == 'Normal'): ?>
                                <li>Acceso a hospitales en zonas metropolitanas (Jalisco, CDMX, Monterrey)</li>
                                <li>2-3 hospitales disponibles por zona</li>
                                <li>Cobertura básica de servicios médicos</li>
                                <li>Atención de urgencias 24/7</li>
                            <?php else: ?>
                                <li>Acceso completo a todos los hospitales de la red</li>
                                <li>Sin restricciones geográficas</li>
                                <li>Cobertura ampliada de servicios médicos</li>
                                <li>Atención prioritaria y servicios premium</li>
                                <li>Consultas con especialistas sin costo adicional</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="policy-actions">
                        <button class="btn" onclick="mostrarDetallesPoliza()">Ver detalles completos</button>
                        <button class="btn btn-accent" onclick="solicitarCambioPoliza()">Solicitar cambio de póliza</button>
                    </div>
                </div>
            </section>

            <!-- Appointment Section -->
            <section id="cita" style="display: none;">
                <div class="page-header">
                    <h2>Agendar Cita Médica</h2>
                    <p>Completa el formulario para solicitar tu cita</p>
                </div>

                <div class="card">
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <button type="button" class="auto-fill-btn" onclick="autoCompletarDatos()">
                            📋 Auto-completar con mis datos
                        </button>
                    <?php else: ?>
                        <div class="message info">
                            <small>💡 <a href="#" onclick="abrirLogin()">Inicia sesión</a> para auto-completar tus datos</small>
                        </div>
                    <?php endif; ?>

                    <form action="procesar_cita.php" method="POST" id="formCita">
                        <div class="form-group">
                            <label for="hospital">Hospital Seleccionado *</label>
                            <input type="text" id="hospital" name="hospital" class="form-control" required readonly 
                                   placeholder="Selecciona un hospital de la sección de búsqueda">
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre">Nombre completo *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo electrónico *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="especialidad">Especialidad requerida *</label>
                            <select id="especialidad" name="especialidad" class="form-control" required>
                                <option value="">Selecciona una especialidad</option>
                                <option value="cardiologia">Cardiología</option>
                                <option value="pediatria">Pediatría</option>
                                <option value="ginecologia">Ginecología</option>
                                <option value="traumatologia">Traumatología</option>
                                <option value="neurologia">Neurología</option>
                                <option value="oncologia">Oncología</option>
                                <option value="oftamologo">Oftalmología</option>
                                <option value="cirugia">Cirugía</option>
                                <option value="dermatologia">Dermatología</option>
                                <option value="medicina_general">Medicina General</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_preferida">Fecha preferida *</label>
                            <input type="date" id="fecha_preferida" name="fecha_preferida" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensaje">Mensaje adicional</label>
                            <textarea id="mensaje" name="mensaje" class="form-control" placeholder="Describe brevemente tu consulta o síntomas" rows="4"></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%;">Solicitar Cita</button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <!-- Change Policy Modal -->
    <div id="modalCambioPoliza" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Solicitar Cambio de Póliza</h3>
            </div>
            
            <div class="message info">
                <p>Al cambiar de póliza, tu cobertura médica se actualizará según el nuevo plan seleccionado.</p>
            </div>
            
            <form id="formCambioPoliza">
                <div class="form-group">
                    <label for="nueva_poliza">Nuevo tipo de póliza *</label>
                    <select id="nueva_poliza" name="nueva_poliza" class="form-control" required>
                        <option value="">Selecciona una opción</option>
                        <option value="normal">Póliza Normal</option>
                        <option value="premium">Póliza Premium</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="motivo">Motivo del cambio *</label>
                    <textarea id="motivo" name="motivo" class="form-control" required placeholder="Explica por qué deseas cambiar de póliza" rows="3"></textarea>
                </div>
                
                <div id="costo_adicional" style="display: none;" class="message warning">
                    <p>⚠️ El cambio a Póliza Premium tiene un costo adicional de $500 MXN mensuales.</p>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="cerrarModal('modalCambioPoliza')">Cancelar</button>
                    <button type="submit" class="btn btn-accent">Solicitar Cambio</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="modalLogin" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Iniciar Sesión</h3>
            </div>
            
            <?php if (isset($error_login)): ?>
                <div class="message error"><?php echo $error_login; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="cerrarModal('modalLogin')">Cancelar</button>
                    <button type="submit" class="btn" name="login">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Navigation functions
        function mostrarSeccion(seccionId) {
            // Hide all sections
            document.querySelectorAll('.content-area > section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            document.getElementById(seccionId).style.display = 'block';
            
            // Update active nav links
            document.querySelectorAll('nav a, .sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            
            document.querySelector(`nav a[href="#${seccionId}"]`).classList.add('active');
            document.querySelector(`.sidebar-menu a[href="#${seccionId}"]`).classList.add('active');
        }
        
        // Initialize with search section
        document.addEventListener('DOMContentLoaded', function() {
            mostrarSeccion('busqueda');
            
            // Set up navigation
            document.querySelectorAll('nav a, .sidebar-menu a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('href').substring(1);
                    mostrarSeccion(target);
                });
            });
        });
        
        // Search functions
        function cambiarFiltro(tipo) {
            document.getElementById('tipoBusqueda').value = tipo;
            
            // Update active filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Submit form automatically
            document.getElementById('formBuscador').submit();
        }
        
        function seleccionarHospital(nombreHospital) {
            document.getElementById('hospital').value = nombreHospital;
            mostrarSeccion('cita');
            
            // Show success message
            alert('Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
        }
        
        // Auto-fill functions
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
        
        // Policy functions
        function mostrarDetallesPoliza() {
            alert('Mostrando detalles completos de la póliza...');
            // Aquí iría la lógica para mostrar los detalles completos
        }
        
        function solicitarCambioPoliza() {
            document.getElementById('modalCambioPoliza').style.display = 'flex';
        }
        
        // Modal functions
        function abrirLogin() {
            document.getElementById('modalLogin').style.display = 'flex';
        }
        
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        // Policy change cost display
        document.getElementById('nueva_poliza').addEventListener('change', function() {
            const costoAdicional = document.getElementById('costo_adicional');
            if (this.value === 'premium') {
                costoAdicional.style.display = 'block';
            } else {
                costoAdicional.style.display = 'none';
            }
        });
        
        // Form submission for policy change
        document.getElementById('formCambioPoliza').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Solicitud de cambio de póliza enviada. Te contactaremos para confirmar los detalles.');
            cerrarModal('modalCambioPoliza');
        });
        
        // Helper function to get policy type (this would come from your backend)
        function obtenerTipoPoliza() {
            // Esta función debería obtener el tipo de póliza real del usuario
            // Por ahora, devolvemos un valor de ejemplo
            return 'Normal'; // o 'Premium'
        }
    </script>
</body>
</html>
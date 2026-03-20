<?php

session_start();

//Si no existe usuario_id en sesión, se redirige al login 
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require("conexion.php"); // Conexión base de datos, necesaria

$usuario_id = $_SESSION['usuario_id'];

//Obtener puntos y nivel de usuario

//Consulta preparada para obtener puntos y nivel actuales
$sql_user = "SELECT puntos, nivel FROM usuarios WHERE id = ?";
$stmt_user = $conexion->prepare($sql_user);
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();

$puntos = $usuario['puntos'];
$nivel  = $usuario['nivel'];

/* Cálculo de progreso dentro del nivel actual.
   Cada 50 puntos se aumenta un nivel.
   Se usa el operador módulo (%) para calcular el progreso parcial */
$puntos_nivel_actual = $puntos % 50;
$porcentaje = ($puntos_nivel_actual / 50) * 100;

//Estadísticas del usuario

/* Se utilizan consultas COUNT optimizadas para evitar
   cargar registros completos innecesarios */

// Tareas pendientes
$sql_pendientes = "SELECT COUNT(*) as total 
                   FROM tareas 
                   WHERE usuario_id = ? AND completada = 0";

$stmt_pendientes = $conexion->prepare($sql_pendientes);
$stmt_pendientes->bind_param("i", $usuario_id);
$stmt_pendientes->execute();
$pendientes = $stmt_pendientes->get_result()->fetch_assoc()['total'];

// Tareas completadas
$sql_completadas = "SELECT COUNT(*) as total 
                    FROM tareas 
                    WHERE usuario_id = ? AND completada = 1";

$stmt_completadas = $conexion->prepare($sql_completadas);
$stmt_completadas->bind_param("i", $usuario_id);
$stmt_completadas->execute();
$completadas = $stmt_completadas->get_result()->fetch_assoc()['total'];

//Filtro de visualización hoy/todas 
$filtro = ($_GET['filtro'] ?? 'hoy') === 'todas' ? 'todas' : 'hoy';

if ($filtro === 'todas') {

    /* Se seleccionan únicamente los campos necesarios
       para optimizar la consulta */
    $sql_tareas = "SELECT id, titulo, fecha 
                   FROM tareas 
                   WHERE usuario_id = ? 
                   AND completada = 0
                   ORDER BY fecha ASC";

    $stmt_tareas = $conexion->prepare($sql_tareas);
    $stmt_tareas->bind_param("i", $usuario_id);

} else {

    $fecha_hoy = date("Y-m-d");

    $sql_tareas = "SELECT id, titulo, fecha 
                   FROM tareas 
                   WHERE usuario_id = ? 
                   AND fecha = ? 
                   AND completada = 0";

    $stmt_tareas = $conexion->prepare($sql_tareas);
    $stmt_tareas->bind_param("is", $usuario_id, $fecha_hoy);
}

$stmt_tareas->execute();
$result_tareas = $stmt_tareas->get_result();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DoFocus - Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header class="main-header">
    <div class="header-top">
        <h1 class="logo">
    <span class="logo-icon"></span>
    DoFocus
</h1>

        <div class="user-info">
    <span class="nav-link">
    <!--Icono nivel-->
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
         viewBox="0 0 24 24" fill="none" stroke="currentColor" 
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 13a1 1 0 0 0-1-1H5.061a1 1 0 0 1-.75-1.811l6.836-6.835a1.207 1.207 0 0 1 1.707 0l6.835 6.835a1 1 0 0 1-.75 1.811H16a1 1 0 0 0-1 1v6a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1z"/>
    </svg>
    Nivel: <?php echo $nivel; ?>
</span>
    <span>Puntos: <?php echo $puntos; ?></span>
    <a href="historial.php" class="nav-link">
    <!--Icono historial-->
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
         viewBox="0 0 24 24" fill="none" stroke="currentColor" 
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
        <path d="M3 3v5h5"/>
        <path d="M12 7v5l4 2"/>
    </svg>
    Historial
</a>
    <a href="logout.php" class="nav-link">
        <!--Icono cerrar sesión-->
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
         viewBox="0 0 24 24" fill="none" stroke="currentColor" 
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m16 17 5-5-5-5"/>
        <path d="M21 12H9"/>
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
    </svg>
    Cerrar sesión
    </a>
    <button type="button" onclick="toggleDarkMode()" class="icon-btn theme-toggle">
    <!-- Icono modo oscuro -->
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3a9 9 0 1 0 9 9 7 7 0 0 1-9-9z"/>
    </svg>
</button>
</div>
    </div>

    <div class="progreso-wrapper">
        <div class="barra-progreso-container">
            <div class="barra-progreso" style="width: <?php echo $porcentaje; ?>%;"></div>
        </div>
        <small><?php echo round($porcentaje); ?>% hacia el siguiente nivel</small>
    </div>
</header>


<main>
<!--Resumen del día-->
<section class="resumen">
    <h2>Resumen del día</h2>

    <p>
    <?php 
    if ($filtro === 'todas') {

        if ($pendientes > 0) {
            echo "Tienes $pendientes tareas pendientes en total.";
        } else {
            echo "No tienes tareas pendientes. ¡Buen trabajo!";
        }

    } else {

        if ($pendientes > 0) {
            echo "Tienes $pendientes tareas pendientes para hoy.";
        } else {
            echo "No tienes tareas pendientes para hoy. ¡Buen trabajo!";
        }

    }
    ?>
    </p>
</section>
<!--Estadísticas-->
<section class="estadisticas">
    <h2>Estadísticas</h2>
    <div class="stats-grid">
        <div class="stat-card">
           <strong class="icon-title">
        <!--Icono pendientes-->
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" x2="12" y1="8" y2="12"/>
        <line x1="12" x2="12.01" y1="16" y2="16"/>
    </svg>
    Pendientes
</strong>
            <p><?php echo $pendientes; ?></p>
        </div>
        <div class="stat-card">
            <strong class="icon-title">
        <!--Icono completadas-->
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6 9 17l-5-5"/>
    </svg>
    Completadas
</strong>
            <p><?php echo $completadas; ?></p>
        </div>
        <div class="stat-card">
            <strong class="icon-title">
        <!--Icono puntos totales-->
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9 12 2"/>
    </svg>
    Puntos totales
</strong>
            <p><?php echo $puntos; ?></p>
        </div>
    </div>
</section>
<!--Tareas-->
<section class="tareas">
    <h2>Tareas</h2>

    <!--Filtro -->
   <div class="tareas-top">

    <form method="GET" class="filtro-form">
        <select name="filtro" onchange="this.form.submit()" class="filtro-select">
            <option value="hoy" <?php if($filtro=='hoy') echo 'selected'; ?>>Hoy</option>
            <option value="todas" <?php if($filtro=='todas') echo 'selected'; ?>>Todas</option>
        </select>
    </form>

    <form action="crear_tarea.php" method="POST" class="crear-form">

        <input type="text" name="titulo" placeholder="Nueva tarea..." required>

        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>

        <button type="submit" class="btn-add">
            + Añadir
        </button>

    </form>

</div>

    <div class="lista-tareas">
        <?php while($tarea = $result_tareas->fetch_assoc()): 

    $hoy = date("Y-m-d");
    $vencida = ($tarea['fecha'] < $hoy);

?>
    <div class="tarea <?php echo $vencida ? 'vencida' : ''; ?>">

<form action="completar_tarea.php" method="POST" style="display:inline;" class="form-completar">
    <input type="hidden" name="tarea_id" value="<?php echo $tarea['id']; ?>">
    <button type="submit" class="btn-completar icon-btn">
    <!-- Icono completar -->
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
         viewBox="0 0 24 24" fill="none" stroke="currentColor" 
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6 9 17l-5-5"/>
    </svg>
</button>
</form>
                <?php echo htmlspecialchars($tarea['titulo']); ?>
<small>(<?php echo $tarea['fecha']; ?>)</small>

<?php if($vencida): ?>
    <span class="aviso-vencida icon-title">
        <!-- Icono alerta vencida -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" x2="12" y1="8" y2="12"/>
        <line x1="12" x2="12.01" y1="16" y2="16"/>
    </svg>
    Vencida
</span>
<?php endif; ?>

                <a href="editar_tarea.php?id=<?php echo $tarea['id']; ?>">
                    <button type="button" class="icon-btn">
                    <!-- Icono editar -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                     <path d="m15 5 4 4"/>
                     </svg>
                    </button>
                </a>

                <form action="eliminar_tarea.php" method="POST" class="form-eliminar">
                    <input type="hidden" name="tarea_id" value="<?php echo $tarea['id']; ?>">
                    <button type="submit" class="icon-btn danger">
                    <!-- Icono eliminar -->
                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                    <path d="M3 6h18"/>
                     <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                     </svg>
                    </button>
                </form>

            </div>
        <?php endwhile; ?>
    </div>
</section>

<section class="temporizador">
    <h2 class="icon-title">
        <!--Icono temporizador-->
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
         viewBox="0 0 24 24" fill="none" stroke="currentColor" 
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="10" x2="14" y1="2" y2="2"/>
        <line x1="12" x2="15" y1="14" y2="11"/>
        <circle cx="12" cy="14" r="8"/>
    </svg>
    Temporizador
    </h2>
    <label for="minutos">Duración (minutos):</label>
    <input type="number" id="minutos" value="25" min="1" max="120">

    <div id="timer">25:00</div>

    <button onclick="iniciar()">Iniciar</button>
    <button onclick="reiniciar()">Reiniciar</button>

    <a href="temporizador.php">
        <button type="button">Modo concentración</button>
    </a>

</section>

</main>

<script src="temporizador.js"></script>
<script>

// Animación entrada
window.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("page-in");
});

// Animación salida suave
document.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", function(e) {

        const href = this.getAttribute("href");

        if (href && !href.startsWith("#")) {
            e.preventDefault();
            document.body.classList.add("page-out");

            setTimeout(() => {
                window.location.href = href;
            }, 250);
        }
    });
});

// Dark mode persistente
function toggleDarkMode() {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("modoOscuro", "activo");
    } else {
        localStorage.removeItem("modoOscuro");
    }
}

window.addEventListener("load", () => {
    if (localStorage.getItem("modoOscuro") === "activo") {
        document.body.classList.add("dark-mode");
    }
});

</script>
</body>
</html>

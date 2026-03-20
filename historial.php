<?php

session_start();

//Si no hay sesión activa, vuelve al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require("conexion.php");//Archivo de conexión de base de datos necesario

$usuario_id = $_SESSION['usuario_id'];

//Consulta de tareas completadas

//Se seleccionan únicamente los campos necesarios (titulo y fecha) para optimizar la consulta
$sql = "SELECT titulo, fecha 
        FROM tareas 
        WHERE usuario_id = ? 
        AND completada = 1
        ORDER BY fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();


$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - DoFocus</title>
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
            <a href="dashboard.php">← Dashboard</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
</header>

<main>

<section>
    <h2>Historial de tareas completadas</h2>

    <?php if($result->num_rows > 0): ?>
        <?php while($tarea = $result->fetch_assoc()): ?>
            <div class="tarea">
                <strong><?php echo htmlspecialchars($tarea['titulo']); ?></strong>
                <small>(<?php echo $tarea['fecha']; ?>)</small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No tienes tareas completadas todavía.</p>
    <?php endif; ?>

</section>

</main>

</body>
</html>
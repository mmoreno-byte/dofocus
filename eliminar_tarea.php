<?php
session_start();

require("conexion.php");//Conexión de base de datos, necesario para continuar

//Verificación sesión activa, si no, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id = $_SESSION['usuario_id'];

    // Validamos que exista el identificador de la tarea
    if (empty($_POST['tarea_id'])) {
        die("Tarea no válida.");
    }

    $tarea_id = $_POST['tarea_id'];

    /* Eliminación de tarea.
       Se añade la condición usuario_id para garantizar
       que solo el propietario pueda eliminarla. */
    $sql = "DELETE FROM tareas 
            WHERE id = ? AND usuario_id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $tarea_id, $usuario_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit();
}

$conexion->close();
?>
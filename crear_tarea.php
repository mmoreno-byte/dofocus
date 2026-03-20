<?php
session_start();


require("conexion.php");//Se incluye el archivo de la conexión de la base de datos antes de seguir

//Se verifica si el usuario está autenticado, si no, se redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    //Se obtiene el id del usuario desde la sesión activa, ya que es información sensible
    $usuario_id = $_SESSION['usuario_id'];

    
    if (empty($_POST['titulo']) || empty($_POST['fecha'])) {
        die("El título y la fecha son obligatorios.");
    }

    //Limpieza de espacios innecesarios
    $titulo = trim($_POST['titulo']);
    $descripcion = $_POST['descripcion'] ?? "";
    $fecha = $_POST['fecha'];

    //Consulta preparada para insertar nueva tarea. Se utilizan parámetros vinculados para evitar inyección SQL.
    $sql = "INSERT INTO tareas (usuario_id, titulo, descripcion, fecha)
            VALUES (?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    /*Vinculación de parámetros:
       i para $usuario_id y se trata como un integer
       s para $titulo y se trata como un string
       s para $descripcion y se trata como un string
       s para $fecha y se trata como un string*/
    $stmt->bind_param("isss", $usuario_id, $titulo, $descripcion, $fecha);

    $stmt->execute();

    // Cierre de la sentencia preparada
    $stmt->close();

    // Redirección para evitar reenvío del formulario. P.ej. Si refresca la página solo recarga el dashboard, no vuelve a enviar formulario
    header("Location: dashboard.php");
    exit();//Necesario para detener el script
}

// Cierre de conexión
$conexion->close();
?>
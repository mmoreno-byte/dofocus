<?php
session_start();

require("conexion.php");//Contiene la base de datos, necesario para seguir ejecutando

//Verificar si la sesión está activa, si no, vuelve al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$tarea_id = $_GET['id'] ?? null; //Si existe $_GET['id'], se utiliza, si no, se asigna null

/* Si no se recibe identificador válido, se redirige */
if (!$tarea_id) {
    header("Location: dashboard.php");
    exit();
}

//Obtener tarea cercionándose que sea del usuario
$sql = "SELECT * FROM tareas WHERE id = ? AND usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $tarea_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$tarea = $result->fetch_assoc();
/*Si la tarea no se encuentra (no existe o no pertenece al usuario)
se redirige al panel principaL, evitando así accesos no autorizados
y errores en la ejecución. Esencial el uso del exit() para que no
se siga ejecutando el script*/
if (!$tarea) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty($_POST['titulo'])) {
        die("El título no puede estar vacío.");
    }

    $nuevo_titulo = trim($_POST['titulo']);

    //Cambio si el id de la tarea coincide y pertece al usuario autenticado
    $sql_update = "UPDATE tareas 
                   SET titulo = ? 
                   WHERE id = ? AND usuario_id = ?";
    //Preparado para evitar inyección SQL
    $stmt_update = $conexion->prepare($sql_update);
    /*s para $nuevo_titulo y se considera string
      i para $tarea_id y se considera integer
      i para $usuario_id y se considera integer
      Trata estos valores como datos, no como código SQL*/
    $stmt_update->bind_param("sii", $nuevo_titulo, $tarea_id, $usuario_id);
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar tarea</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<main style="max-width:500px; margin:auto; padding:50px;">

    <h2>Editar tarea</h2>

    <form method="POST">
        <input type="text" name="titulo" 
               value="<?php echo htmlspecialchars($tarea['titulo']); ?>" required>
        <br><br>
        <button type="submit">Guardar cambios</button>
        <a href="dashboard.php">Cancelar</a>
    </form>

</main>

</body>
</html>

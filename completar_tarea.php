<?php
session_start();

require("conexion.php");//Necesario ya que incluye la conexión de la base de datos

//Verificación de sesión activa si no, se redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    //Se coge desde el id de la sesión activa por información sensible
    $usuario_id = $_SESSION['usuario_id'];

    //Validación de existencia de tarea_id
    if (empty($_POST['tarea_id'])) {
        die("Tarea no válida.");
    }

    $tarea_id = $_POST['tarea_id'];

    /*Marcar tarea como completada:
      Se añade la condición usuario_id con la intención de evitar
      que los usuarios modifiquen tareas de otros usuarios*/
    $sql = "UPDATE tareas 
            SET completada = 1 
            WHERE id = ? AND usuario_id = ? AND completada = 0";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $tarea_id, $usuario_id);
    $stmt->execute();

    //Solo se suma puntos si realmente se modificó una fila
    if ($stmt->affected_rows > 0) {

        //Sumar puntos al usuario
        $sql2 = "UPDATE usuarios 
                 SET puntos = puntos + 10 
                 WHERE id = ?";

        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param("i", $usuario_id);
        $stmt2->execute();
        $stmt2->close();

        //Recalcular el nivel en función de puntos
        $sql3 = "SELECT puntos FROM usuarios WHERE id = ?";
        $stmt3 = $conexion->prepare($sql3);
        $stmt3->bind_param("i", $usuario_id);
        $stmt3->execute();
        $result = $stmt3->get_result();
        $usuario = $result->fetch_assoc();
        $puntos = $usuario['puntos'];
        $stmt3->close();

        /*Cada 50 puntos sube 1 nivel
          +1 es porque se empieza en el nivel 1*/
        $nivel = floor($puntos / 50) + 1;

        $sql4 = "UPDATE usuarios SET nivel = ? WHERE id = ?";
        $stmt4 = $conexion->prepare($sql4);
        $stmt4->bind_param("ii", $nivel, $usuario_id);
        $stmt4->execute();
        $stmt4->close();
    }

    $stmt->close();

    header("Location: dashboard.php");
    exit();
}

$conexion->close();
?>
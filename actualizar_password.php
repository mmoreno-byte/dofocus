<?php
require("conexion.php");//Necesario ya que incluye la conexión de la base de datos

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}
//Si se quiere acceder directamente, se redirige al login 
if (empty($_POST['token']) || empty($_POST['password'])) {
    header("Location: login.php?reset=error");
    exit();
}

$token = $_POST['token'];
$nueva_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

//Verificar Token
//Consulta preparada para buscar el Token en la BD. Evita inyección SQL
$stmt = $conexion->prepare(
    "SELECT token_expira FROM usuarios WHERE reset_token = ?"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: login.php?reset=error");
    exit();
}

if (strtotime($usuario['token_expira']) < time()) {
    header("Location: login.php?reset=expired");
    exit();
}

$stmt->close();

//Actualizar contraseña
$stmt_update = $conexion->prepare(
    "UPDATE usuarios 
     SET password = ?, reset_token = NULL, token_expira = NULL 
     WHERE reset_token = ?"
);
$stmt_update->bind_param("ss", $nueva_password, $token);
$stmt_update->execute();

if ($stmt_update->affected_rows > 0) {
    header("Location: login.php?reset=ok");
} else {
    header("Location: login.php?reset=error");
}

$stmt_update->close();
$conexion->close();
exit();
?>
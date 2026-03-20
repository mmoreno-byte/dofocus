<?php
require("conexion.php");//Base de datos del proyecto, necesaria

//Obtener Token desde la URL
$token = $_GET['token'] ?? null;

if (!$token) {
    die("Token inválido.");
}

//Buscar Token en la base de datos
$stmt = $conexion->prepare(
    "SELECT token_expira FROM usuarios WHERE reset_token = ?"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    die("Token no válido.");
}

//Verificar que el Token no haya expirado
if (strtotime($usuario['token_expira']) < time()) {
    die("El token ha expirado.");
}

$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="olvide.css">
<title>Nueva contraseña</title>
</head>
<body>

<div class="olvide-container">

    <h2>Nueva contraseña</h2>

    <form action="actualizar_password.php" method="post">

        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="input-group">
            <input type="password" name="password" placeholder="Nueva contraseña" required>
        </div>

        <button type="submit" class="btn-primary">
            Actualizar contraseña
        </button>

    </form>

    <div class="olvide-links">
        <a href="login.php">← Volver</a>
    </div>

</div>

</body>
</html>


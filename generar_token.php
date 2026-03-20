<?php
require("conexion.php"); //Necesario, contiene la Base de Datos

$username = $_POST['username'] ?? "";

$mensaje = "";
$link = "";

if (!empty($username)) {

    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $stmt = $conexion->prepare("UPDATE usuarios SET reset_token=?, token_expira=? WHERE username=?");
    $stmt->bind_param("sss", $token, $expira, $username);
    $stmt->execute();

    $mensaje = "Si el usuario existe, se ha generado un enlace válido durante 15 minutos.";

    if ($stmt->affected_rows > 0) {
        $link = "reset.php?token=" . $token;
    }

    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="olvide.css">
<title>Enlace generado</title>
</head>
<body>

<div class="olvide-container">

    <h2>Recuperación de contraseña</h2>

    <p class="olvide-text"><?php echo $mensaje; ?></p>

    <?php if (!empty($link)): ?>
        <a href="<?php echo $link; ?>" class="btn-primary">
            Restablecer contraseña
        </a>
    <?php endif; ?>

    <div class="olvide-links">
        <a href="login.php">← Volver al inicio de sesión</a>
    </div>

</div>

</body>
</html>
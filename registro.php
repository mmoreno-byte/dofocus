<?php
require("conexion.php");

$error = "";

/* Procesar formulario */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Todos los campos son obligatorios.";
    } else {

        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conexion->prepare(
            "INSERT INTO usuarios (username, password) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            header("Location: login.php?registro=ok");
            exit();
        } else {
            $error = "El usuario ya existe.";
        }

        $stmt->close();
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="login-container">

    <h2>Registrarse</h2>

    <?php if (!empty($error)): ?>
        <div class="error-box">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="input-g">
            <input type="text" name="username" placeholder="Usuario" required>
        </div>

        <div class="input-g">
            <input type="password" name="password" placeholder="Contraseña" required>
        </div>

        <button type="submit" class="btn-login">
            Registrarse
        </button>

    </form>

    <div class="options">
        <a href="login.php">Volver al login</a>
    </div>

</div>

</body>
</html>
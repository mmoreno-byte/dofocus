<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="olvide.css">
    <title>Recuperar contraseña</title>
</head>
<body>

<div class="olvide-container">

    <h2>Recuperar contraseña</h2>

    <p class="olvide-text">
        Introduce tu usuario para generar un enlace de recuperación.
    </p>

    <form action="generar_token.php" method="post">

        <div class="input-group">
            <input type="text" name="username" placeholder="Usuario" required>
        </div>

        <button type="submit" class="btn-primary">Enviar</button>

    </form>

    <div class="olvide-links">
        <a href="login.php">← Volver al inicio de sesión</a>
    </div>

</div>

</body>
</html>
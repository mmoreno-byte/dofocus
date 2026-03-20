<?php
session_start();
require("conexion.php");//Necesario para la ejecución, contiene la BD

$error = "";
$resetMessage = "";

/* Procesamiento del formulario */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Todos los campos son obligatorios.";
    } else {

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $conexion->prepare("SELECT id, password FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['username'] = $username;

                header("Location: dashboard.php");
                exit();
            }
        }

        $error = "Usuario o contraseña incorrectos.";
    }
}

/* Mensajes del reset */
if (isset($_GET['reset'])) {
    if ($_GET['reset'] === "ok") {
        $resetMessage = "<div class='success-box'>Contraseña actualizada correctamente. Ahora puedes iniciar sesión.</div>";
    } elseif ($_GET['reset'] === "error") {
        $resetMessage = "<div class='error-box'>Se produjo un error al actualizar la contraseña.</div>";
    } elseif ($_GET['reset'] === "expired") {
        $resetMessage = "<div class='error-box'>El enlace ha expirado. Solicita uno nuevo.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="login.css">
<link rel="stylesheet" href="olvide.css">
</head>
<body>

<div class="login-container">

<h2>Iniciar sesión</h2>

<?php echo $resetMessage; ?>

<?php if (!empty($error)): ?>
    <div class="error-box">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="input-g">
        <input type="text" name="username" placeholder="Usuario">
    </div>

    <div class="input-g">
        <input type="password" name="password" placeholder="Contraseña">
    </div>

    <button class="btn-login">Entrar</button>
</form>

<div class="options">
    <p>¿No tienes cuenta?</p>
    <a href="registro.php">Regístrate</a>
    <br>
    <a href="olvide.php">¿Olvidaste tu contraseña?</a>
</div>

</div>

</body>
</html>
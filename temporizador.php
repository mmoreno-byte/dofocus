<?php
session_start();

if(!isset($_SESSION['usuario_id'])){
    header("Location: ");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modo Concentración - DoFocus</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="modo-concentracion">

<main style="text-align:center; margin-top:100px;">

    <h1>Modo Concentración</h1>

    <label for="minutos">Duración (minutos):</label>
    <input type="number" id="minutos" value="25" min="1" max="120">

    <div id="timer" style="font-size:4rem; margin:30px 0;">25:00</div>

    <button onclick="iniciar()">Iniciar</button>
    <button onclick="reiniciar()">Reiniciar</button>
    <br><br>
    <a href="dashboard.php">← Volver al Dashboard</a>

</main>

<script src="temporizador.js"></script>
</body>
</html>

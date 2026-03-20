let intervalo = null;
let tiempoRestante = 0;
let duracionSeleccionada = 0;

function iniciar() {

    clearInterval(intervalo);

    const inputMinutos = document.getElementById("minutos");
    const minutos = parseInt(inputMinutos.value);

    if (isNaN(minutos) || minutos < 1) {
        alert("Introduce un tiempo válido.");
        return;
    }

    duracionSeleccionada = minutos * 60;
    tiempoRestante = duracionSeleccionada;

    actualizarPantalla();

    intervalo = setInterval(() => {

        tiempoRestante--;

        actualizarPantalla();

        if (tiempoRestante <= 0) {
            clearInterval(intervalo);

            alert("¡Tiempo terminado! 🎉");
        }

    }, 1000);
}


//Reiniciar temporizador
function reiniciar() {

    clearInterval(intervalo);

    //Si no se inició, no se hace nada
    if (duracionSeleccionada === 0) return;

    tiempoRestante = duracionSeleccionada;
    actualizarPantalla();
}

//Actualizar pantalla
function actualizarPantalla() {

    const minutos = Math.floor(tiempoRestante / 60);
    const segundos = tiempoRestante % 60;

    document.getElementById("timer").textContent =
        `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
}
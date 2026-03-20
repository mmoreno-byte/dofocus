# 🎯 DoFocus

Aplicación web de gestión de tareas con sistema de gamificación, desarrollada como proyecto final del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (DAW).

🌐 **Demo en vivo:** [https://dofocus.infinityfreeapp.com](https://dofocus.infinityfreeapp.com) *(actualiza con tu URL real)*

---

## 📋 Descripción

DoFocus es una aplicación de productividad que permite a los usuarios organizar sus tareas diarias de forma sencilla. Incluye un sistema de puntos y niveles para motivar el cumplimiento de objetivos, un temporizador Pomodoro integrado y modo oscuro persistente.

---

## ✨ Funcionalidades

- **Registro e inicio de sesión** con contraseñas cifradas (`password_hash`)
- **Recuperación de contraseña** mediante token con expiración de 15 minutos  
  ⚠️ *Funcionalidad diseñada e implementada a nivel de lógica y base de datos, pero el envío de email no está activo en la demo por requerir un servicio externo (ver sección de limitaciones).*
- **Gestión de tareas**: crear, editar, completar y eliminar
- **Filtro de tareas**: ver las de hoy o todas las pendientes
- **Indicador de tareas vencidas**
- **Sistema de gamificación**: puntos y niveles por tareas completadas
- **Temporizador Pomodoro** con modo concentración de pantalla completa
- **Historial** de tareas completadas
- **Modo oscuro** persistente con `localStorage`
- **Animaciones** de entrada y salida de página
- Diseño **responsive** adaptado a móvil y escritorio

---

## 🛠️ Tecnologías utilizadas

| Capa          | Tecnología              |
|---------------|-------------------------|
| Frontend      | HTML5, CSS3, JavaScript |
| Backend       | PHP 8                   |
| Base de datos | MySQL                   |
| Hosting       | InfinityFree            |
| Entorno local | XAMPP                   |

---

## 🗄️ Estructura de la base de datos

### Tabla `usuarios`

| Campo          | Tipo      | Descripción                         |
|----------------|-----------|-------------------------------------|
| `id`           | INT (PK)  | Identificador único                 |
| `username`     | VARCHAR   | Nombre de usuario                   |
| `password`     | VARCHAR   | Contraseña cifrada con bcrypt       |
| `puntos`       | INT       | Puntos acumulados                   |
| `nivel`        | INT       | Nivel actual del usuario            |
| `reset_token`  | VARCHAR   | Token de recuperación de contraseña |
| `token_expira` | DATETIME  | Fecha de expiración del token       |

### Tabla `tareas`

| Campo         | Tipo     | Descripción                       |
|---------------|----------|-----------------------------------|
| `id`          | INT (PK) | Identificador único               |
| `usuario_id`  | INT (FK) | Referencia al usuario propietario |
| `titulo`      | VARCHAR  | Título de la tarea                |
| `descripcion` | TEXT     | Descripción opcional              |
| `fecha`       | DATE     | Fecha asignada                    |
| `completada`  | TINYINT  | 0 = pendiente, 1 = completada     |

---

## 🚀 Instalación en local

### Requisitos previos
- XAMPP (o cualquier servidor con PHP 8+ y MySQL)
- Git

### Pasos

1. **Clona el repositorio:**
   ```bash
   git clone https://github.com/tu-usuario/dofocus.git
   cd dofocus
   ```

2. **Configura la base de datos:**
   - Inicia XAMPP y activa Apache y MySQL
   - Crea una base de datos llamada `login_sistema` en phpMyAdmin
   - Importa el esquema SQL (ver sección siguiente)

3. **Configura la conexión:**
   - Copia `.env.example`, renómbralo a `.env` y rellena tus datos
   - Abre `conexion.php` y cambia `$produccion = true;` a `$produccion = false;`
   - Las credenciales de local ya vienen configuradas (`root` sin contraseña)

4. **Accede a la aplicación:**
   - Abre tu navegador en `http://localhost/dofocus`

### Esquema SQL

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    puntos INT DEFAULT 0,
    nivel INT DEFAULT 1,
    reset_token VARCHAR(100) DEFAULT NULL,
    token_expira DATETIME DEFAULT NULL
);

CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    completada TINYINT(1) DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

---

## 🔒 Seguridad implementada

- Contraseñas cifradas con `password_hash()` y `password_verify()`
- Consultas preparadas con `mysqli` para prevenir inyección SQL
- Tokens de recuperación seguros con `bin2hex(random_bytes(32))` y expiración de 15 minutos
- Verificación de propiedad en cada operación sobre tareas (el usuario solo puede modificar las suyas)
- En producción, los errores de conexión no exponen información técnica
- Regeneración de ID de sesión al iniciar sesión (`session_regenerate_id`)

---

## ⚠️ Limitaciones de la demo

La demo alojada en InfinityFree es una versión funcional del proyecto con algunas limitaciones propias del entorno de hosting gratuito:

| Funcionalidad | Estado en la demo | En un entorno real |
|---|---|---|
| Recuperación de contraseña | El formulario existe pero el email no se envía | Se integraría **PHPMailer** o **SendGrid** para el envío real |
| Variables de entorno | Configuradas manualmente en el panel de InfinityFree | Se gestionarían con una librería como `vlucas/phpdotenv` |

### Sobre la recuperación de contraseña

La lógica está completamente implementada a nivel de backend y base de datos: se genera un token seguro, se almacena con fecha de expiración y existe el flujo completo de validación y actualización de contraseña. Lo único que falta para que funcione en producción real es conectar un servicio de envío de email, algo que InfinityFree no permite en su plan gratuito.

---

## 📁 Estructura del proyecto

```
dofocus/
├── index.php               # Redirección al login
├── login.php               # Inicio de sesión
├── registro.php            # Registro de usuario
├── logout.php              # Cierre de sesión
├── dashboard.php           # Panel principal
├── crear_tarea.php         # Crear nueva tarea
├── editar_tarea.php        # Editar tarea existente
├── completar_tarea.php     # Marcar tarea como completada
├── eliminar_tarea.php      # Eliminar tarea
├── historial.php           # Historial de completadas
├── temporizador.php        # Modo concentración
├── temporizador.js         # Lógica del temporizador
├── olvide.php              # Formulario recuperar contraseña
├── generar_token.php       # Generación de token de reset
├── reset.php               # Formulario nueva contraseña
├── actualizar_password.php # Guardar nueva contraseña
├── conexion.php            # ⚠️ No incluido en el repo (ver .env.example)
├── .env.example            # Plantilla de configuración
├── login.css               # Estilos login y registro
├── dashboard.css           # Estilos dashboard e historial
└── olvide.css              # Estilos recuperación de contraseña
```

---

## 👨‍💻 Autor

**mmorenodev**  
Proyecto de fin de ciclo — DAW  
Granada, 2026

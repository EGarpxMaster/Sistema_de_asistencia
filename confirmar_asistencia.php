<?php
// Incluir el archivo de conexión
include 'conexion.php';

// Iniciar sesión para manejar CSRF (si lo implementaste previamente)
session_start();

// Conectar a la base de datos
$connection = serverCon();

// Verificar si la conexión se realizó correctamente
if ($connection != null) {
    // Verificar que se recibieron los datos del formulario
    if (isset($_POST['participante'], $_POST['correo_electronico'], $_POST['brazalete'], $_POST['telefono'], $_POST['programa_educativo'])) {
        
        // Validación del token CSRF (si lo implementaste)
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            mostrarMensajeError("Token CSRF inválido.");
            exit;
        }

        // Obtener los datos del formulario
        $nombre = strtoupper(trim($_POST['participante']));
        $correo = strtoupper(trim($_POST['correo_electronico'])); // Obtener el correo
        $telefono = strtoupper(trim($_POST['telefono'])); // Obtener el teléfono
        $prog_educativo = strtoupper(trim($_POST['programa_educativo'])); // Obtener el programa educativo
        $brazalete = intval($_POST['brazalete']); // Asegurarse de que sea un número entero
        
        // Validar formato de correo electrónico
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            mostrarMensajeError("Correo electrónico no válido.");
            exit;
        }

        // Verificar si el número de brazalete es válido (ejemplo: entre 1 y 500)
        if ($brazalete < 1 || $brazalete > 500) {
            mostrarMensajeError("Número de brazalete fuera de rango.");
            exit;
        }

        // Verificar si el participante ya está registrado
        $sql = "SELECT Id_Brazalete FROM asistencia WHERE Nombre_completo = ? AND Correo_electronico = ?";
        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            mostrarMensajeError("Error en la preparación de la consulta: " . $connection->error);
            exit;
        }
        $stmt->bind_param("ss", $nombre, $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Si el participante ya está registrado, redirigir a confirmar_asistencia2.php
            header("Location: confirmar_asistencia2.php");
            exit();
        } else {
            // Verificar si el número de brazalete ya está registrado
            $sql = "SELECT Id_Brazalete FROM asistencia WHERE Id_Brazalete = ?";
            $stmt = $connection->prepare($sql);
            if ($stmt === false) {
                mostrarMensajeError("Error en la preparación de la consulta: " . $connection->error);
                exit;
            }
            $stmt->bind_param("i", $brazalete);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Si ya está registrado
                mostrarMensajeError("Error: El número de brazalete ya está asignado.");
            } else {
                // Insertar el registro de asistencia con todos los datos
                $sql = "INSERT INTO asistencia (Nombre_completo, Correo_electronico, Telefono, Programa_educativo, Id_brazalete, fecha_hora_registro) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $connection->prepare($sql);
                if ($stmt === false) {
                    mostrarMensajeError("Error en la preparación de la consulta: " . $connection->error);
                    exit;
                }
                $stmt->bind_param("ssisi", $nombre, $correo, $telefono, $prog_educativo, $brazalete); // Asegurarse de incluir todos los datos
                if ($stmt->execute()) {
                    // Confirmación de asistencia
                    mostrarMensajeExito("Asistencia confirmada correctamente.");

                    // También insertar en la tabla lista
                    insertarEnLista($connection, $nombre, $correo, $telefono, $prog_educativo, $brazalete);
                } else {
                    mostrarMensajeError("Error al confirmar asistencia: " . $stmt->error);
                }
            }
        }

        // Cerrar el statement
        $stmt->close();
    } else {
        mostrarMensajeError("Error: No se recibieron los datos necesarios.");
    }

    // Cerrar la conexión
    $connection->close();
} else {
    mostrarMensajeError("No se pudo establecer la conexión a la base de datos.");
}

// Función para insertar en la tabla lista
function insertarEnLista($connection, $nombre, $correo, $telefono, $prog_educativo, $brazalete) {
    // Verificar si el participante ya está en la lista
    $sql = "SELECT Id_brazalete FROM lista WHERE Nombre_completo = ?";
    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        mostrarMensajeError("Error en la preparación de la consulta para verificar duplicados: " . $connection->error);
        return;
    }
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Si el participante ya está en la lista
        mostrarMensajeError("Error: El participante ya está en la lista.");
    } else {
        // Si no está, proceder con la inserción
        $sql = "INSERT INTO lista (Nombre_completo, Correo_electronico, Telefono, Programa_educativo, Id_brazalete) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            mostrarMensajeError("Error en la preparación de la consulta para la tabla lista: " . $connection->error);
            return;
        }
        $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $prog_educativo, $brazalete);
        if (!$stmt->execute()) {
            mostrarMensajeError("Error al insertar en la tabla lista: " . $stmt->error);
        } else {
            mostrarMensajeExito("El participante ha sido agregado a la lista.");
        }
    }

    // Cerrar el statement
    $stmt->close();
}

function mostrarMensajeExito($mensaje) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultado</title>
        <link rel="stylesheet" href="estilos/styles.css">
        <script>
            // Redirigir a confirmacion.php después de 3 segundos
            setTimeout(function() {
                window.location.href = "confirmacion.php";
            }, 3000);
        </script>
    </head>
    <body>
        <div class="menu">
        <img src="images/LOGO%20UNICARIBE%20BLACK.png" alt="Imagen esquina izquierda" class="image-small image-top-left">
        <img src="images/LogoIngenier%C3%ADa_Color.png" alt="Imagen esquina derecha" class="image-small image-top-right">
    </div>

        <div class="mensaje" style="text-align: center; margin-top: 130px;">
            <h1>' . htmlspecialchars($mensaje) . '</h1>
             <img src="images/verde.png" alt="Imagen de éxito" class="image-succes">            
        </div>
    </body>
    </html>
    ';
}

function mostrarMensajeError($mensaje) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <link rel="stylesheet" href="estilos/styles.css">
        <script>
            // Redirigir a confirmacion.php después de 3 segundos
            setTimeout(function() {
                window.location.href = "confirmacion.php";
            }, 3000);
        </script>
    </head>
    <body>
        <div class="menu">
        <img src="images/LOGO%20UNICARIBE%20BLACK.png" alt="Imagen esquina izquierda" class="image-small image-top-left">
        <img src="images/LogoIngenier%C3%ADa_Color.png" alt="Imagen esquina derecha" class="image-small image-top-right">
    </div>

        <div class="mensaje" style="text-align: center; margin-top: 130px;">
            <h1>' . htmlspecialchars($mensaje) . '</h1>
             <img src="images/rojo.png" alt="Imagen de error" class="image-error">
        </div>
    </body>
    </html>
    ';
}
?>

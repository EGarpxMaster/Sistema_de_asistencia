<?php
// Incluir el archivo de conexión
include 'conexion.php';

// Conectar a la base de datos
$connection = serverCon();

// Verificar si la conexión se realizó correctamente
if ($connection != null) {
    // Verificar que se recibieron los datos del formulario
    if (isset($_POST['participante'], $_POST['correo_electronico'], $_POST['brazalete'])) {
        // Obtener los datos del formulario
        $nombre = strtoupper(trim($_POST['participante']));
        $correo = strtoupper(trim($_POST['correo_electronico']));
        $telefono = trim($_POST['telefono']);
        $programaEducativo = strtoupper(trim($_POST['programa_educativo'])); // Agregar el punto y coma
        $brazalete = intval($_POST['brazalete']); // Asegurarse de que sea un número entero

        // Insertar el registro de asistencia con los datos completos
        $sql = "INSERT INTO asistencia (Nombre_completo, Correo_electronico, telefono, programa_educativo, Id_brazalete, fecha_hora_registro) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $connection->prepare($sql);

        if ($stmt === false) {
            mostrarMensajeError("Error en la preparación de la consulta: " . $connection->error);
            exit;
        }

        // Vincular parámetros correctamente
        $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $programaEducativo, $brazalete); 

        if ($stmt->execute()) {
            // Confirmación de asistencia
            mostrarMensajeExito("Asistencia confirmada correctamente.");
        } else {
            mostrarMensajeError("Error al confirmar asistencia: " . $stmt->error);
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

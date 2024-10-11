<?php
include 'conexion.php';

// Conectar a la base de datos
$connection = serverCon('localhost', 'root', '', 'sistemaasistencias');

// Verificar que la conexión se haya realizado correctamente
if ($connection != null) {
    // Obtener y validar los datos del formulario
    if (isset($_POST['nombre'], $_POST['correo'], $_POST['brazalete'])) {
        $nombre = strtoupper(trim($_POST['nombre']));
        $correo = strtoupper(trim($_POST['correo']));
        $brazalete = intval($_POST['brazalete']);

        // Validar que los campos no estén vacíos y que el correo tenga formato válido
        if (!empty($nombre) && !empty($correo) && $brazalete > 0) {

            // Validar el formato de correo
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                mostrarMensajeError("El correo electrónico no es válido.");
                exit;
            }

            // Validar que el número de brazalete esté dentro de un rango permitido
            if ($brazalete < 1 || $brazalete > 500) {
                mostrarMensajeError("El número de brazalete debe estar entre 1 y 500.");
                exit;
            }

            // Validar que el número de brazalete no esté duplicado
            $sql = "SELECT Id_Brazalete FROM asistencia WHERE Id_Brazalete = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $brazalete);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                mostrarMensajeError("El número de brazalete ya está registrado.");
                exit;
            }
            $stmt->close();

            try {
                // Insertar en la tabla Participantes
                insertarParticipante($connection, $nombre, $correo);

                // Insertar en la tabla Asistencia
                insertarAsistencia($connection, $nombre, $correo, $brazalete);

                mostrarMensajeExito("Registro realizado exitosamente en ambas tablas.");
            } catch (Exception $e) {
                mostrarMensajeError("Error en el registro: " . $e->getMessage());
            }
        } else {
            mostrarMensajeError("Por favor, complete todos los campos correctamente.");
        }
    } else {
        mostrarMensajeError("Faltan datos en el formulario.");
    }

    // Cerrar la conexión
    $connection->close();
} else {
    mostrarMensajeError("No se pudo establecer la conexión a la base de datos.");
}

// Función para insertar participante
function insertarParticipante($connection, $nombre, $correo) {
    $sql = "INSERT INTO participantes (nombre_participante, correo_electronico) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Error en la preparación de la consulta: " . $connection->error);
    }
    $stmt->bind_param("ss", $nombre, $correo);
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar participante: " . $stmt->error);
    }
    $stmt->close();
}

// Función para insertar asistencia
function insertarAsistencia($connection, $nombre, $correo, $brazalete) {
    $sql = "INSERT INTO asistencia (nombre_completo, correo_electronico, id_brazalete, fecha_hora_registro) VALUES (?, ?, ?, NOW())";
    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Error en la preparación de la consulta: " . $connection->error);
    }
    $stmt->bind_param("ssi", $nombre, $correo, $brazalete);
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar asistencia: " . $stmt->error);
    }
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
             <img src="images/rojo.png" alt="Imagen de éxito" class="image-error">
        </div>
    </body>
    </html>
    ';
}
?>

<?php
// Función para conectar con la base de datos
function serverCon($host_name, $user_name, $password, $db) {
    $connection = null;
    try {
        $connection = new mysqli($host_name, $user_name, $password, $db);
        if ($connection->connect_error) {
            throw new Exception("Error al conectar: " . $connection->connect_error);
        }
    } catch (Exception $err) {
        mostrarMensajeError($err->getMessage());
        exit; // Terminar el script si hay un error en la conexión
    }
    return $connection;
}

// Función para mostrar mensaje de éxito
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

// Función para mostrar mensaje de error
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

// Función para verificar si el nombre ya existe en la tabla lista
function nombreYaExiste($connection, $nombre) {
    $sql = "SELECT COUNT(*) FROM lista WHERE Nombre_completo = ?";
    $stmt = $connection->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0; // Devuelve verdadero si el nombre ya existe
    } else {
        throw new Exception("Error en la preparación de la consulta para verificar nombre: " . $connection->error);
    }
}

// Función para insertar en la tabla lista
function insertarLista($connection, $nombre, $correo, $telefono, $programaEducativo, $brazalete) {
    $sql = "INSERT INTO lista (Nombre_completo, Correo_electronico, Telefono, Programa_educativo, Id_brazalete) VALUES (?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $programaEducativo, $brazalete);
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar en la lista: " . $stmt->error);
        }
        $stmt->close();
    } else {
        throw new Exception("Error en la preparación de la consulta para lista: " . $connection->error);
    }
}

// Función para insertar en la tabla asistencia
function insertarAsistencia($connection, $nombre, $correo, $telefono, $programaEducativo, $brazalete) {
    $sql = "INSERT INTO asistencia (Nombre_completo, Correo_electronico, Telefono, Programa_educativo, Id_brazalete, fecha_hora_registro) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $connection->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $programaEducativo, $brazalete);
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar en la asistencia: " . $stmt->error);
        }
        $stmt->close();
    } else {
        throw new Exception("Error en la preparación de la consulta para asistencia: " . $connection->error);
    }
}

// Conectar a la base de datos
$connection = serverCon('localhost', 'root', '', 'sistemaasistencias');

// Verificar que la conexión se haya realizado correctamente
if ($connection != null) {
    // Obtener y validar los datos del formulario
    if (isset($_POST['nombre'], $_POST['correo'], $_POST['telefono'], $_POST['programa'], $_POST['brazalete'])) {
        $nombre = strtoupper(trim($_POST['nombre']));
        $correo = strtoupper(trim($_POST['correo']));
        $brazalete = intval($_POST['brazalete']);
        $telefono = trim($_POST['telefono']);
        $programaEducativo = strtoupper(trim($_POST['programa']));

        // Validar que los campos no estén vacíos
        if (!empty($nombre) && !empty($correo) && $brazalete > 0 && !empty($telefono) && !empty($programaEducativo)) {
            // Validar el formato de correo electrónico
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                mostrarMensajeError("El correo electrónico no es válido.");
                exit;
            }

            // Validar que el número de brazalete esté dentro de un rango permitido
            if ($brazalete < 1 || $brazalete > 500) {
                mostrarMensajeError("El número de brazalete debe estar entre 1 y 500.");
                exit;
            }

            // Verificar si el nombre ya existe en la lista
            if (nombreYaExiste($connection, $nombre)) {
                mostrarMensajeError("El nombre ya está registrado.");
                exit;
            }

            try {
                // Insertar en la tabla lista
                insertarLista($connection, $nombre, $correo, $telefono, $programaEducativo, $brazalete);
                
                // Insertar en la tabla asistencia
                insertarAsistencia($connection, $nombre, $correo, $telefono, $programaEducativo, $brazalete);
                
                mostrarMensajeExito("Registro realizado exitosamente en la tabla lista y asistencia.");
            } catch (Exception $e) {
                mostrarMensajeError($e->getMessage());
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
?>

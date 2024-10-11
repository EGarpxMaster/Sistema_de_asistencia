<?php
// Función para establecer conexión con la base de datos
function serverCon() {
    $host_name = 'localhost'; // Cambia si tu servidor no está en localhost
    $user_name = 'root'; // Usuario de la base de datos
    $password = ''; // Contraseña de la base de datos (en blanco si no tienes)
    $db = 'sistemaasistencias'; // Nombre de la base de datos

    // Crear la conexión
    $connection = new mysqli($host_name, $user_name, $password, $db);

    // Comprobar si la conexión fue exitosa
    if ($connection->connect_error) {
        die("Error de conexión: " . $connection->connect_error);
    }
    
    return $connection;
}
?>

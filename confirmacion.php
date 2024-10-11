<?php
include 'conexion.php';
session_start();

// Conectar a la base de datos
$connection = serverCon();

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar asistencia</title>
    <link rel="stylesheet" href="estilos/styles.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap" rel="stylesheet" />
</head>
<body>
    <div class="images">
        <img src="images/LOGO%20UNICARIBE%20BLACK.png" alt="Imagen esquina izquierda" class="image-top-left">
        <img src="images/LogoIngenier%C3%ADa_Color.png" alt="Imagen esquina derecha" class="image-top-right">
    </div>
    <div class="form-container">
        <form action="confirmar_asistencia.php" method="post">
            <h1>Confirmar asistencia</h1>

            <!-- Campo CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="participante">Nombre completo</label>
            <select id="participante" name="participante" onchange="updateInfo()" required>
                <option value="">Seleccione un participante</option>
                
                <?php
                // Verificar que la conexión se haya realizado correctamente
                if ($connection) {
                    // Consulta para obtener los nombres, correos electrónicos y teléfonos
                    $sql = "SELECT nombre_participante, correo_electronico, telefono, programa_educativo FROM participantes ORDER BY nombre_participante"; 
                    $result = $connection->query($sql);

                    // Verificar si se obtuvieron resultados
                    if ($result && $result->num_rows > 0) {
                        // Generar las opciones del select
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['nombre_participante'], ENT_QUOTES, 'UTF-8') . '" 
                                    data-email="' . htmlspecialchars($row['correo_electronico'], ENT_QUOTES, 'UTF-8') . '"
                                    data-phone="' . htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') . '"
                                    data-prog_educativo="' . htmlspecialchars($row['programa_educativo'], ENT_QUOTES, 'UTF-8') . '"> 
                                    ' . htmlspecialchars($row['nombre_participante'], ENT_QUOTES, 'UTF-8') . 
                                  '</option>';
                        }
                    } else {
                        echo '<option value="">No se encontraron participantes</option>';
                    }
                } else {
                    echo '<option value="">Error al conectar a la base de datos</option>';
                }
                ?>
            </select>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="correo_electronico" readonly aria-readonly="true" placeholder="Correo electrónico" required>

            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" readonly aria-readonly="true" placeholder="Teléfono" required>

            <label for="prog_educativo">Programa educativo</label>
            <input type="text" id="prog_educativo" name="programa_educativo" readonly aria-readonly="true" placeholder="Programa educativo" required>

            <label for="brazalete">Número de brazalete</label>
            <input type="number" id="brazalete" name="brazalete" min="1" max="500" placeholder="Número de brazalete" required>

            <!-- Botón para confirmar asistencia -->
            <input type="submit" value="Confirmar asistencia">

            <!-- Botones secundarios -->
            <button class="btn-secondary" type="button" onclick="location.href='asistencia.php'">¿Ya estás registrado?</button>
            <button class="btn-secondary" type="button" onclick="location.href='registro.html'">Registrar nuevo invitado</button>
        </form>

        <!-- Íconos sociales -->
        <div class="social-icons">
            <a href="https://www.facebook.com/profile.php?id=61550645371287&mibextid=LQQJ4d" target="_blank" class="icon">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a aria-label="Chat on WhatsApp" href="https://wa.me/529982288611" target="_blank" class="icon"> 
                <i class="fab fa-whatsapp"></i>
            </a>
            <a aria-label="Chat on WhatsApp" href="https://wa.me/529983982543" target="_blank" class="icon"> 
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
    </div>

    <script>
    function updateInfo() {
        var select = document.getElementById("participante");
        var selectedOption = select.options[select.selectedIndex];
        var email = selectedOption.getAttribute("data-email");
        var phone = selectedOption.getAttribute("data-phone");
        var prog_educativo = selectedOption.getAttribute("data-prog_educativo");
        
        // Actualizar los campos de correo electrónico, teléfono y programa educativo
        document.getElementById("email").value = email;
        document.getElementById("telefono").value = phone;
        document.getElementById("prog_educativo").value = prog_educativo; // Añadido para mostrar el programa educativo
    }
    </script>
</body>
</html>

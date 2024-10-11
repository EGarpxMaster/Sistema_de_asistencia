<?php
include 'conexion.php';
session_start();

// Conectar a la base de datos
$connection = serverCon();

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Variables para almacenar los valores seleccionados
$correo_electronico = "";
$telefono = "";
$programa_educativo = "";
$brazalete = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['participante'])) {
    // Obtener el nombre seleccionado
    $nombre_participante = $_POST['participante'];

    // Consulta para obtener los datos del participante seleccionado
    $sql = "SELECT correo_electronico, telefono, programa_educativo, id_brazalete FROM asistencia WHERE nombre_completo = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('s', $nombre_participante);
    $stmt->execute();
    $stmt->bind_result($correo_electronico, $telefono, $programa_educativo, $brazalete);
    $stmt->fetch();
    $stmt->close();
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia</title>
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
        <form action="confirmar_asistencia2.php" method="post">
            <h1>Asistencia</h1>

            <!-- Campo CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="participante">Nombre completo:</label>
            <select id="participante" name="participante" onchange="updateFields()" required>
                <option value="">Seleccione un participante</option>
                <?php
                $sql = "SELECT nombre_completo, correo_electronico, telefono, programa_educativo, id_brazalete FROM asistencia ORDER BY nombre_completo";
                $result = $connection->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['nombre_completo'], ENT_QUOTES, 'UTF-8') . '" 
                                data-email="' . htmlspecialchars($row['correo_electronico'], ENT_QUOTES, 'UTF-8') . '" 
                                data-telefono="' . htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') . '" 
                                data-programa="' . htmlspecialchars($row['programa_educativo'], ENT_QUOTES, 'UTF-8') . '" 
                                data-brazalete="' . htmlspecialchars($row['id_brazalete'], ENT_QUOTES, 'UTF-8') . '">' . 
                                htmlspecialchars($row['nombre_completo'], ENT_QUOTES, 'UTF-8') . 
                              '</option>';
                    }
                } else {
                    echo '<option value="">No se encontraron participantes registrados</option>';
                }
                ?>
            </select>

            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="correo_electronico" value="<?php echo htmlspecialchars($correo_electronico, ENT_QUOTES, 'UTF-8'); ?>" readonly placeholder="Correo electrónico" required>

            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8'); ?>" readonly placeholder="Teléfono" required>

            <label for="programa">Programa educativo:</label>
            <input type="text" id="programa" name="programa_educativo" value="<?php echo htmlspecialchars($programa_educativo, ENT_QUOTES, 'UTF-8'); ?>" readonly placeholder="Programa educativo" required>

            <label for="brazalete">Número de brazalete:</label>
            <input type="number" id="brazalete" name="brazalete" value="<?php echo htmlspecialchars($brazalete, ENT_QUOTES, 'UTF-8'); ?>" readonly placeholder="Número de brazalete" required>

            <input type="submit" value="Confirmar asistencia">

            <button class="btn-secondary" type="button" onclick="location.href='confirmacion.php'">Regresar a inicio</button>
            <button class="btn-secondary" type="button" onclick="location.href='registro.html'">Registrar nuevo invitado</button>
        </form>

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
    function updateFields() {
        var select = document.getElementById("participante");
        var selectedOption = select.options[select.selectedIndex];
        var email = selectedOption.getAttribute("data-email");
        var telefono = selectedOption.getAttribute("data-telefono");
        var programa = selectedOption.getAttribute("data-programa");
        var brazalete = selectedOption.getAttribute("data-brazalete");

        document.getElementById("email").value = email;
        document.getElementById("telefono").value = telefono;
        document.getElementById("programa").value = programa;
        document.getElementById("brazalete").value = brazalete;
    }
    </script>

</body>
</html>

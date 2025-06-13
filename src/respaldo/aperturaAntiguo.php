<?php
ob_start(); // Iniciar almacenamiento en búfer de salida
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Asegúrate de que la conexión esté configurada para UTF-8
$conexion = mysql_connect('localhost', 'root', 'admin1025');
if (!$conexion) {
    die("Error de conexión: " . mysql_error());
}
mysql_select_db('resto', $conexion);
mysql_set_charset('utf8', $conexion); // Establecer la codificación a UTF-8

// Verificar si hay un mensaje de éxito en la sesión
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Borrar el mensaje para que no se muestre al actualizar
}

// Inicializar variables del formulario
$id_caja = '';
$id_turno = '';
$fecha_inicio = date('Y-m-d H:i:s'); // Establecer la fecha de inicio como la fecha y hora actual
$monto_apertura = '';

// Verificar si el usuario tiene una caja activa
$query_verificar_caja_activa = "SELECT * FROM control_caja WHERE id_usuario = '$idUser' AND fecha_termino IS NULL";
$result_verificar_caja = mysql_query($query_verificar_caja_activa);

if (mysql_num_rows($result_verificar_caja) > 0) {
    // El usuario ya tiene una caja activa
    $alert = "El usuario ya tiene una caja activa. No puede abrir otra hasta que cierre la anterior.";
} else {
    // Manejo del formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recibir y sanitizar los datos del formulario
        $id_caja = mysql_real_escape_string($_POST['id_caja']);
        $id_turno = mysql_real_escape_string($_POST['id_turno']);
        $monto_apertura = mysql_real_escape_string($_POST['monto_apertura']);

        // Verificar si ya existe el registro en la base de datos
        $query_check = "SELECT * FROM control_caja WHERE id_caja = '$id_caja' AND id_turno = '$id_turno' AND DATE(fecha_inicio) = CURDATE()";
        $result_check = mysql_query($query_check);

        if (mysql_num_rows($result_check) > 0) {
            // Registro ya existe
            $alert = "El registro ya existe para la caja, turno y fecha de inicio seleccionados.";
        } else {
            // Insertar los datos en la base de datos
            $query_insert = "INSERT INTO control_caja (id_usuario, id_caja, id_turno, fecha_inicio, monto_apertura)
                             VALUES ('$idUser', '$id_caja', '$id_turno', '$fecha_inicio', '$monto_apertura')";

            $resultado_insert = mysql_query($query_insert);
            if ($resultado_insert) {
                // Guardar un mensaje de éxito en la sesión
                $_SESSION['success_message'] = "¡Los datos se han guardado correctamente!";

                // Redirigir para evitar el reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $alert = "Error al guardar los datos: " . mysql_error();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    
<div class="container d-flex justify-content-center align-items-center" style="height: calc(100vh - 130px);"> <!-- Ajusta la altura -->
    <div class="col-md-6">
        <h2 class="text-center">Apertura de Caja</h2>
        <?php if (!empty($alert)): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo addslashes($alert); ?>', // Escapa el mensaje
                    confirmButtonText: 'Aceptar'
                });
            </script>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="hidden" name="id_control_caja" value="<?php echo isset($id_control_caja) ? $id_control_caja : ''; ?>">

            <div class="form-group">
                <label for="id_usuario">Usuario:</label>
                <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo htmlspecialchars($usuario); ?>" readonly>
            </div>

            <div class="form-group d-flex justify-content-between">
                <div class="w-50 pr-2">
                    <label for="id_caja">Caja:</label>
                    <select class="form-control" id="id_caja" name="id_caja" required>
                        <option value="">Seleccione una caja</option>
                        <?php
                        // Obtener todas las cajas con estado = 1
                        $query_cajas = "SELECT id_caja, descripcion FROM cajas WHERE estado = 1";
                        $result_cajas = mysql_query($query_cajas, $conexion);
                        if ($result_cajas) {
                            while ($row = mysql_fetch_assoc($result_cajas)) {
                                echo "<option value='" . $row['id_caja'] . "'>" . htmlspecialchars($row['descripcion']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Error al cargar cajas</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="w-50 pl-2">
                    <label for="id_turno">Turno:</label>
                    <select class="form-control" id="id_turno" name="id_turno" required>
                        <option value="">Seleccione un turno</option>
                        <?php
                        // Obtener todos los turnos con estado = 1
                        $query_turnos = "SELECT id_turno, descripcion FROM turnos WHERE estado = 1";
                        $result_turnos = mysql_query($query_turnos, $conexion);
                        if ($result_turnos) {
                            while ($row = mysql_fetch_assoc($result_turnos)) {
                                echo "<option value='" . $row['id_turno'] . "'>" . htmlspecialchars($row['descripcion']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Error al cargar turnos</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group d-flex justify-content-between">
                <div class="w-50 pr-2">
                    <label for="fecha_inicio">Fecha Apertura:</label>
                    <input type="text" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d H:i', strtotime($fecha_inicio)); ?>" required readonly>
                </div>
                <div class="w-50 pl-2">
                    <label for="monto_apertura">Monto Apertura:</label>
                    <input type="number" class="form-control" id="monto_apertura" name="monto_apertura" step="0.01" min="0" value="<?php echo $monto_apertura; ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Realizar Apertura</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php
// Fin de almacenamiento en búfer
include_once "includes/footer.php"; 
?>

<script>
    // Mostrar la alerta de éxito si existe un mensaje en la variable PHP
    <?php if (isset($success_message)): ?>
        window.onload = function() {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '<?php echo $success_message; ?>',
                confirmButtonText: 'Aceptar'
            });
        };
    <?php endif; ?>
</script>

</body>
</html>
<?php include_once "includes/footer.php"; ?>
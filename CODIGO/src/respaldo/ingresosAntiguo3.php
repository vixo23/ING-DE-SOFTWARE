<?php
ob_start(); // Iniciar almacenamiento en búfer de salida
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Cambiar a mysqli
$conexion = mysql_connect('localhost', 'root', 'admin1025'); // Conectar a la base de datos
mysql_select_db('resto', $conexion);
mysql_set_charset('utf8'); // Establecer la codificación a UTF-8

// Obtener el ID del usuario desde la sesión
$idUser = $_SESSION['idUser']; // ID del usuario actual

// Consulta para obtener el nombre del usuario
$queryUsuario = mysql_query("SELECT nombre FROM usuarios WHERE id = '$idUser'");

if (!$queryUsuario) {
    die("Error en la consulta: " . mysql_error()); // Manejo de errores
}

$usuario = mysql_fetch_assoc($queryUsuario);
$nombreUsuario = $usuario ? $usuario['nombre'] : ''; // Guardar el nombre del usuario, o vacío si no se encuentra
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

<h1 style="text-align: center;">Ingresos</h1>

<div class="card">
    <div class="card-body">
        <form action="ajax.php" method="post" autocomplete="off" id="formulario" onsubmit="return validarFormulario();">
            <?php echo isset($success_message) ? $success_message : ''; ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="id_usuario">Usuario</label>
                        <input type="text" class="form-control" placeholder="Nombre del Usuario" name="id_usuario" id="id_usuario" value="<?php echo htmlspecialchars($nombreUsuario); ?>" readonly required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="caja">Caja</label>
                        <select id="caja" class="form-control" name="id_caja" required>
                            <option value="">Seleccione Caja</option>
                            <?php
                            // Consulta para obtener las cajas
                            $queryCajas = mysql_query("SELECT id_caja, descripcion FROM cajas");
                            while ($caja = mysql_fetch_assoc($queryCajas)) {
                                echo "<option value='{$caja['id_caja']}'>{$caja['descripcion']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="turno">Turno</label>
                        <select id="turno" class="form-control" name="id_turno" required>
                            <option value="">Seleccione Turno</option>
                            <?php
                            // Consulta para obtener los turnos
                            $queryTurnos = mysql_query("SELECT id_turno, descripcion FROM turnos");
                            while ($turno = mysql_fetch_assoc($queryTurnos)) {
                                echo "<option value='{$turno['id_turno']}'>{$turno['descripcion']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="monto">Monto</label>
                        <input type="number" class="form-control" placeholder="Ingrese Monto" name="monto" id="monto" min="0" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input type="text" class="form-control" placeholder="Ingrese Descripción" name="descripcion" id="descripcion" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                    </div>
                </div>
            </div>
            <input type="hidden" name="tipo_movimiento" value="1"> <!-- Valor predeterminado para tipo movimiento -->
            <input type="submit" value="Registrar Movimiento" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>

<script>
function validarFormulario() {
    var monto = document.getElementById('monto').value;
    var fecha = new Date(document.getElementById('fecha').value);
    var hoy = new Date();

    // Validar que el monto sea un número positivo
    if (monto <= 0) {
        alert('El monto debe ser mayor que cero.');
        return false;
    }

   
    return true; // Si todas las validaciones son correctas, el formulario se envía
}
</script>


<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Caja</th>
                <th>Turno</th>
                <th>Tipo Movimiento</th>
                <th>Monto</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Inicializar la variable para la suma total
        $totalMonto = 0;

        // Consulta para obtener los movimientos de tipo ingreso (tipo_movimiento = 1)
        $query = mysql_query("SELECT mc.*, u.nombre AS usuario_nombre, c.descripcion AS caja_desc, t.descripcion AS turno_desc 
                              FROM movimientos_caja mc 
                              JOIN usuarios u ON mc.id_usuario = u.id 
                              JOIN cajas c ON mc.id_caja = c.id_caja 
                              JOIN turnos t ON mc.id_turno = t.id_turno 
                              WHERE mc.tipo_movimiento = 1"); // Filtrar solo ingresos
        $result = mysql_num_rows($query);
        if ($result > 0) {
            while ($data = mysql_fetch_assoc($query)) {
                // El tipo de movimiento es siempre 'Ingreso' aquí
                $tipoMovimiento = 'Ingreso';

                // Sumar el monto solo si es Ingreso
                $totalMonto += $data['monto'];

                $montoVisualizado = $data['monto']; // Siempre positivo para ingresos
                ?>
                <tr>
                    <td><?php echo $data['id_movimiento']; ?></td>
                    <td><?php echo htmlspecialchars($data['usuario_nombre']); ?></td> <!-- Muestra el nombre del usuario -->
                    <td><?php echo htmlspecialchars($data['caja_desc']); ?></td>
                    <td><?php echo htmlspecialchars($data['turno_desc']); ?></td>
                    <td><?php echo $tipoMovimiento; ?></td> <!-- Muestra 'Ingreso' -->
                    <td><?php echo '$' . number_format(htmlspecialchars($montoVisualizado), 0, ',', '.'); ?></td> <!-- Monto en pesos chilenos -->
                    <td><?php echo htmlspecialchars($data['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($data['fecha']); ?></td>
                </tr>
            <?php }
        }
        ?>

        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                <td><?php echo '$' . number_format($totalMonto, 0, ',', '.'); ?></td> <!-- Mostrar la suma total en pesos chilenos -->
                <td colspan="2"></td> <!-- Espacio en blanco para las columnas restantes -->
            </tr>
        </tfoot>
    </table>
</div>



<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#formulario').on('submit', function(e) {
        e.preventDefault(); // Prevenir el envío del formulario por defecto

        $.ajax({
            type: 'POST',
            url: 'ajax.php', // La URL donde se encuentra tu archivo PHP
            data: $(this).serialize(), // Serializar los datos del formulario
            dataType: 'json', // Esperar respuesta JSON
            success: function(response) {
                if (response.success) {
                    // Mostrar Sweet Alert si se guardaron los datos correctamente
                    Swal.fire({
                        title: 'Éxito!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        // Redirigir dependiendo del tipo de movimiento
                        if ($('input[name="tipo_movimiento"]').val() == 1) {
                            window.location.href = 'ingresos.php';
                        } else {
                            window.location.href = 'egresos.php';
                        }
                    });
                } else {
                    // Mostrar mensaje de error
                    Swal.fire({
                        title: 'Error!',
                        text: response.error,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function() {
                // Mostrar mensaje de error en caso de fallo en la solicitud AJAX
                Swal.fire({
                    title: 'Error!',
                    text: 'Error en la conexión. Por favor, inténtelo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });
});
</script>

</body>
</html>

<?php
ob_end_flush(); // Finalizar almacenamiento en búfer de salida
?>

<?php include_once "includes/footer.php"; ?>

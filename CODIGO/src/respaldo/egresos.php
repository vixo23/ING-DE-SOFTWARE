<?php
ob_start(); // Iniciar almacenamiento en búfer de salida
session_start();

include "../conexion.php";
include "includes/header.php";

// Conectar a la base de datos usando mysqli
$conexion = new mysqli('localhost', 'root', 'admin1025', 'resto');
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}
$conexion->set_charset('utf8'); // Establecer la codificación a UTF-8

// Obtener el ID del usuario desde la sesión
$idUser = $_SESSION['idUser'];

// Consulta para verificar si la caja está abierta
$queryCajaEstado = $conexion->query("SELECT fecha_termino FROM control_caja WHERE id_usuario = '$idUser' AND fecha_termino IS NULL");

if (!$queryCajaEstado) {
    die("Error en la consulta: " . $conexion->error); // Manejo de errores
}

// Comprobar si hay un registro de caja abierta
$cajaAbierta = $queryCajaEstado->num_rows > 0;

// Obtener el nombre del usuario
$queryUsuario = $conexion->query("SELECT nombreUsuario FROM usuarios WHERE id = '$idUser'");
if (!$queryUsuario) {
    die("Error en la consulta: " . $conexion->error); // Manejo de errores
}
$usuario = $queryUsuario->fetch_assoc();
$nombreUsuario = $usuario ? $usuario['nombreUsuario'] : ''; // Guardar el nombre del usuario, o vacío si no se encuentra

// Obtener el id_control_caja activo
$queryControlCaja = $conexion->query("SELECT id_control_caja FROM control_caja WHERE id_usuario = '$idUser' AND fecha_termino IS NULL");
if (!$queryControlCaja) {
    die("Error en la consulta: " . $conexion->error); // Manejo de errores
}

$idControlCaja = $queryControlCaja->fetch_assoc();
$idControlCajaValue = $idControlCaja ? $idControlCaja['id_control_caja'] : ''; // Obtener el id_control_caja

// Consulta para obtener la caja y el turno del usuario
$queryCajaTurno = $conexion->query("SELECT c.id_caja, c.descripcion AS caja_desc, t.id_turno, t.descripcion AS turno_desc, cu.id_control_caja 
                                    FROM control_caja cu 
                                    JOIN cajas c ON cu.id_caja = c.id_caja 
                                    JOIN turnos t ON cu.id_turno = t.id_turno 
                                    WHERE cu.id_usuario = '$idUser' AND cu.fecha_termino IS NULL");

if (!$queryCajaTurno) {
    die("Error en la consulta: " . $conexion->error); // Manejo de errores
}

$cajaTurno = $queryCajaTurno->fetch_assoc();
$cajaDescripcion = $cajaTurno ? $cajaTurno['caja_desc'] : ''; // Obtener la descripción de la caja
$turnoDescripcion = $cajaTurno ? $cajaTurno['turno_desc'] : ''; // Obtener la descripción del turno
$idCaja = $cajaTurno ? $cajaTurno['id_caja'] : ''; // Obtener el ID de la caja
$idTurno = $cajaTurno ? $cajaTurno['id_turno'] : ''; // Obtener el ID del turno
$idControlCaja = $cajaTurno ? $cajaTurno['id_control_caja'] : ''; // Obtener el ID del control de caja
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formulario');

        // Mostrar alerta al cargar la página si la caja está cerrada
        <?php if (!$cajaAbierta): ?>
            Swal.fire({
                icon: 'error',
                title: 'Caja Cerrada',
                text: 'No se puede agregar egresos porque la caja no está activa.',
            }).then(() => {
                // Redirigir a apertura.php después de cerrar la alerta
                window.location.href = 'apertura.php';
            });
        <?php endif; ?>

        form.addEventListener('submit', function(event) {
            // Evitar el envío del formulario si la caja está cerrada
            <?php if (!$cajaAbierta): ?>
                event.preventDefault(); // Prevenir el envío del formulario
                Swal.fire({
                    icon: 'error',
                    title: 'Caja Cerrada',
                    text: 'No se puede agregar egresos porque la caja no está activa.',
                }).then(() => {
                    // Redirigir a apertura.php después de cerrar la alerta
                    window.location.href = 'apertura.php';
                });
            <?php endif; ?>
        });
    });
    </script>
</head>
<body>

<h1 style="text-align: center;">Egresos</h1>

<div class="card">
    <div class="card-body">
        <form action="ajax.php" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <!-- Campos ocultos -->
                <input type="hidden" name="id_usuario_value" value="<?php echo htmlspecialchars($idUser); ?>">
                <input type="hidden" name="id_caja" id="id_caja" value="<?php echo htmlspecialchars($idCaja); ?>">
                <input type="hidden" name="id_turno" id="id_turno" value="<?php echo htmlspecialchars($idTurno); ?>">
                <input type="hidden" name="id_control_caja" id="id_control_caja" value="<?php echo htmlspecialchars($idControlCajaValue); ?>"> <!-- Nuevo campo oculto para id_control_caja -->

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

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="monto">Monto</label>
                        <input type="number" class="form-control" placeholder="Ingrese Monto" name="monto" id="monto" min="0" required>
                    </div>
                </div>
            </div>

            <input type="hidden" name="tipo_movimiento" value="0"> <!-- Valor predeterminado para tipo movimiento (Egreso) -->
            <input type="submit" value="Registrar Egresos" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>

<script>
function limpiar() {
    document.getElementById('monto').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('fecha').value = '';
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

// Consulta para obtener los movimientos de tipo egreso (tipo_movimiento = 0) 
// y que pertenezcan al id_control_caja activo
$query = $conexion->query("SELECT mc.*, u.nombreUsuario AS usuario_nombre, c.descripcion AS caja_desc, t.descripcion AS turno_desc 
                           FROM movimientos_caja mc 
                           JOIN usuarios u ON mc.id_usuario = u.id 
                           JOIN cajas c ON mc.id_caja = c.id_caja 
                           JOIN turnos t ON mc.id_turno = t.id_turno 
                           WHERE mc.tipo_movimiento = 0 AND mc.id_control_caja = '$idControlCajaValue'"); // Filtrar solo egresos

                    if ($query) {
                        $result = $query->num_rows;
                        if ($result > 0) {
                            $num = 1;
                            while ($row = $query->fetch_assoc()) {
                                $totalMonto -= $row['monto']; // Restar monto al total
                                ?>
                                <tr>
                                    <td><?php echo $num++; ?></td>
                                    <td><?php echo htmlspecialchars($row['usuario_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['caja_desc']); ?></td>
                                    <td><?php echo htmlspecialchars($row['turno_desc']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tipo_movimiento'] == 0 ? 'Egreso' : 'Ingreso'); ?></td>
                                    <td>$ -<?php echo number_format($row['monto'], 0, ',', '.'); ?></td> <!-- Monto en color negro -->
                                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='8'>No hay registros disponibles</td></tr>";
                        }
                    } else {
                        echo "Error en la consulta: " . $conexion->error;
                    }
                    ?>

        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align:right;">Total:</th>
                <th>$ <?php echo number_format($totalMonto, 0, ',', '.'); ?></th> <!-- Total en color negro -->
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
</div>



</body>
</html>
<?php include_once "includes/footer.php"; ?>
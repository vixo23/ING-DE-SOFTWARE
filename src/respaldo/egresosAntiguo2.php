<?php
ob_start(); // Iniciar almacenamiento en búfer de salida
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Conectar a la base de datos usando mysql
$conexion = mysql_connect('localhost', 'root', 'admin1025');
mysql_select_db('resto', $conexion);
mysql_set_charset('utf8'); // Establecer la codificación a UTF-8

// Obtener el ID del usuario desde la sesión
$idUser = $_SESSION['idUser'];

// Consulta para obtener el nombre del usuario
$queryUsuario = mysql_query("SELECT nombre FROM usuarios WHERE id = '$idUser'");

if (!$queryUsuario) {
    die("Error en la consulta: " . mysql_error());
}

$usuario = mysql_fetch_assoc($queryUsuario);
$nombreUsuario = $usuario ? $usuario['nombre'] : '';

// Consulta para obtener la caja y el turno del usuario
$queryCajaTurno = mysql_query("SELECT c.id_caja, c.descripcion AS caja_desc, t.id_turno, t.descripcion AS turno_desc 
                                FROM control_caja cu 
                                JOIN cajas c ON cu.id_caja = c.id_caja 
                                JOIN turnos t ON cu.id_turno = t.id_turno 
                                WHERE cu.id_usuario = '$idUser'");

if (!$queryCajaTurno) {
    die("Error en la consulta: " . mysql_error());
}

$cajaTurno = mysql_fetch_assoc($queryCajaTurno);
$cajaDescripcion = $cajaTurno ? $cajaTurno['caja_desc'] : '';
$turnoDescripcion = $cajaTurno ? $cajaTurno['turno_desc'] : '';
$idCaja = $cajaTurno ? $cajaTurno['id_caja'] : '';
$idTurno = $cajaTurno ? $cajaTurno['id_turno'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            <input type="hidden" name="tipo_movimiento" value="0"> <!-- Valor predeterminado para tipo movimiento -->
            <input type="submit" value="Registrar Ingresos" class="btn btn-primary" id="btnAccion">
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

        // Consulta para obtener los movimientos de tipo "Ingreso"
        $query = mysql_query("SELECT mc.*, u.nombre AS usuario_nombre, c.descripcion AS caja_desc, t.descripcion AS turno_desc 
                              FROM movimientos_caja mc 
                              JOIN usuarios u ON mc.id_usuario = u.id 
                              JOIN cajas c ON mc.id_caja = c.id_caja 
                              JOIN turnos t ON mc.id_turno = t.id_turno 
                              WHERE mc.tipo_movimiento = 0"); // Filtra solo movimientos tipo 'Ingreso'

        $result = mysql_num_rows($query);
        if ($result > 0) {
            while ($data = mysql_fetch_assoc($query)) {
                $tipoMovimiento = 'Egreso'; // Asigna el tipo de movimiento como 'Egreso'

                // Multiplica el monto por -1 para que sea negativo
                $montoNegativo = $data['monto'] * -1;

                // Suma el monto negativo
                $totalMonto += $montoNegativo;
                ?>
                <tr>
                    <td><?php echo $data['id_movimiento']; ?></td>
                    <td><?php echo htmlspecialchars($data['usuario_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($data['caja_desc']); ?></td>
                    <td><?php echo htmlspecialchars($data['turno_desc']); ?></td>
                    <td><?php echo htmlspecialchars($tipoMovimiento); ?></td>
                    <td><?php echo number_format($montoNegativo, 2); ?></td> <!-- Mostrar siempre como negativo -->
                    <td><?php echo htmlspecialchars($data['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($data['fecha']); ?></td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='8'>No hay movimientos registrados.</td></tr>";
        }
        ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total:</th>
                <th><?php echo number_format($totalMonto, 2); ?></th> <!-- Mostrar la suma total como negativo -->
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
</div>



</body>
</html>
<?php include_once "includes/footer.php"; ?>
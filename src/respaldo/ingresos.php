<?php
ob_start(); // Iniciar almacenamiento en búfer de salida

session_start();
include "includes/header.php";
include "../conexion.php";

// Conectar a la base de datos usando mysqli
$conexion = new mysqli('localhost', 'root', 'admin1025', 'resto'); // Cambia los valores según tu configuración

// Verificar la conexión
if ($conexion->connect_error) {
    die('No se pudo conectar: ' . $conexion->connect_error);
}

$conexion->set_charset('utf8'); // Establecer la codificación a UTF-8

// Obtener el ID del usuario desde la sesión
$idUser = $_SESSION['idUser'];

// Consulta para verificar si la caja está abierta
$queryCajaEstado = $conexion->query("SELECT fecha_termino FROM control_caja WHERE id_usuario = '$idUser' AND fecha_termino IS NULL");

if (!$queryCajaEstado) {
    die("Error en la consulta: " . $conexion->error);
}

// Comprobar si hay un registro de caja abierta
$cajaAbierta = $queryCajaEstado->num_rows > 0;

// Obtener el nombre del usuario
$queryUsuario = $conexion->query("SELECT nombreUsuario FROM usuarios WHERE id = '$idUser'");
if (!$queryUsuario) {
    die("Error en la consulta: " . $conexion->error);
}
$usuario = $queryUsuario->fetch_assoc();
$nombreUsuario = $usuario ? $usuario['nombreUsuario'] : '';

// Consulta para obtener la caja y el turno del usuario
$queryCajaTurno = $conexion->query("SELECT c.id_caja, c.descripcion AS caja_desc, t.id_turno, t.descripcion AS turno_desc, cu.id_control_caja 
                                    FROM control_caja cu 
                                    JOIN cajas c ON cu.id_caja = c.id_caja 
                                    JOIN turnos t ON cu.id_turno = t.id_turno 
                                    WHERE cu.id_usuario = '$idUser' AND cu.fecha_termino IS NULL");

if (!$queryCajaTurno) {
    die("Error en la consulta: " . $conexion->error);
}

$cajaTurno = $queryCajaTurno->fetch_assoc();
$cajaDescripcion = $cajaTurno ? $cajaTurno['caja_desc'] : '';
$turnoDescripcion = $cajaTurno ? $cajaTurno['turno_desc'] : '';
$idCaja = $cajaTurno ? $cajaTurno['id_caja'] : '';
$idTurno = $cajaTurno ? $cajaTurno['id_turno'] : '';
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
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
                text: 'No se puede agregar ingresos porque la caja no está activa.',
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
                    text: 'No se puede agregar ingresos porque la caja no está activa.',
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

<h1 style="text-align: center;">Ingresos</h1>

<div class="card">
    <div class="card-body">
        <form action="ajax.php" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <!-- Campo Usuario oculto -->
                <input type="hidden" name="id_usuario_value" value="<?php echo htmlspecialchars($idUser); ?>"> <!-- ID del usuario oculto -->
                
                <!-- Campo Caja oculto -->
                <input type="hidden" name="id_caja" id="id_caja" value="<?php echo htmlspecialchars($idCaja); ?>">
                
                <!-- Campo Turno oculto -->
                <input type="hidden" name="id_turno" id="id_turno" value="<?php echo htmlspecialchars($idTurno); ?>">
                
                <!-- Campo Control Caja oculto -->
                <input type="hidden" name="id_control_caja" value="<?php echo htmlspecialchars($cajaTurno['id_control_caja']); ?>"> <!-- ID de control de caja oculto -->

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

            <input type="hidden" name="tipo_movimiento" value="1"> <!-- Valor predeterminado para tipo movimiento -->
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

try {
    // Conexión a la base de datos utilizando PDO
    $pdo = new PDO('mysql:host=localhost;dbname=resto', 'root', 'admin1025');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obtener los movimientos de tipo ingreso (tipo_movimiento = 1) filtrando por id_control_caja donde fecha_termino es NULL
    $query = $pdo->prepare("SELECT mc.*, u.nombreUsuario AS usuario_nombre, c.descripcion AS caja_desc, 
                            t.descripcion AS turno_desc 
                            FROM movimientos_caja mc 
                            JOIN usuarios u ON mc.id_usuario = u.id 
                            JOIN cajas c ON mc.id_caja = c.id_caja 
                            JOIN turnos t ON mc.id_turno = t.id_turno 
                            JOIN control_caja cc ON mc.id_control_caja = cc.id_control_caja 
                            WHERE mc.tipo_movimiento = 1 AND cc.fecha_termino IS NULL");
    $query->execute();

    // Verificar si hay resultados
    if ($query->rowCount() > 0) {
        // Recorrer los resultados
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            // El tipo de movimiento es siempre 'Ingreso' aquí
            $tipoMovimiento = 'Ingreso';

            // Sumar el monto solo si es Ingreso
            $totalMonto += $data['monto'];

            $montoVisualizado = $data['monto']; // Siempre positivo para ingresos
            ?>
            <tr>
                <td><?php echo $data['id_movimiento']; ?></td>
                <td><?php echo htmlspecialchars($data['usuario_nombre']); ?></td> <!-- Muestra el nombre del usuario -->
                <td><?php echo htmlspecialchars($data['caja_desc']); ?></td> <!-- Muestra la caja -->
                <td><?php echo htmlspecialchars($data['turno_desc']); ?></td> <!-- Muestra el turno -->
                <td><?php echo htmlspecialchars($tipoMovimiento); ?></td> <!-- Muestra el tipo de movimiento -->
                <td><?php echo number_format($montoVisualizado, 2); ?></td> <!-- Muestra el monto con formato -->
                <td><?php echo htmlspecialchars($data['descripcion']); ?></td> <!-- Muestra la descripción -->
                <td><?php echo htmlspecialchars($data['fecha']); ?></td> <!-- Muestra la fecha -->
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='8' class='text-center'>No hay movimientos de ingresos registrados.</td></tr>";
    }
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
}
?>


        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <th colspan="3"><?php echo number_format($totalMonto, 2); ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php
include "includes/footer.php"; // Incluir el pie de página
ob_end_flush(); // Finalizar el almacenamiento en búfer de salida
?>

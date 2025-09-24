<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];

// Lógica para el filtrado
$fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : date('Y-m-01');
$fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : date('Y-m-d');
$id_usuario = isset($_POST['id_usuario']) ? mysqli_real_escape_string($conexion, $_POST['id_usuario']) : '';
$nombre_usuario = isset($_POST['nombre_usuario']) ? mysqli_real_escape_string($conexion, $_POST['nombre_usuario']) : '';

?>

<!-- Formulario de Filtro -->
<div class="card">
    <div class="card-header bg-primary text-white">
        Informe de Asistencia
    </div>
    <div class="card-body">
        <form action="informe_asistencia.php" method="post">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="id_usuario">ID Usuario:</label>
                        <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre:</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo $fecha_desde; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_hasta">Hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo $fecha_hasta; ?>">
                    </div>
                </div>
                <div class="col-md-1 align-self-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Informe de Asistencia -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        Resultados del Informe
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="table_asistencia">
                <thead class="thead-dark">
                    <tr>
                        <th>ID Usuario</th>
                        <th>Nombre Usuario</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construcción de la consulta con filtros
                    $sql = "SELECT m.fecha, m.hora, u.id_usuarios, u.nombres 
                            FROM marcas m 
                            JOIN usuarios u ON m.id_usuario = u.id_usuarios 
                            WHERE u.id_empresa = '$id_empresa' 
                            AND m.fecha BETWEEN '$fecha_desde' AND '$fecha_hasta'";

                    if (!empty($id_usuario)) {
                        $sql .= " AND u.id_usuarios = '$id_usuario'";
                    }
                    if (!empty($nombre_usuario)) {
                        $sql .= " AND u.nombres LIKE '%$nombre_usuario%'";
                    }

                    $sql .= " ORDER BY u.nombres, m.fecha, m.hora ASC";

                    $query_asistencia = mysqli_query($conexion, $sql);
                    
                    if($query_asistencia && mysqli_num_rows($query_asistencia) > 0) {
                        while ($data = mysqli_fetch_assoc($query_asistencia)) {
                            $fecha = date('d-m-Y', strtotime($data['fecha']));
                            $hora = $data['hora']; // Ya viene en formato TIME desde la base de datos
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['id_usuarios']); ?></td>
                                <td><?php echo htmlspecialchars($data['nombres']); ?></td>
                                <td><?php echo $fecha; ?></td>
                                <td><?php echo $hora; ?></td>
                            </tr>
                    <?php 
                        }
                    } else if (!$query_asistencia) {
                        echo "<tr><td colspan='4' class='text-center text-danger'>Error en la consulta: " . mysqli_error($conexion) . "</td></tr>";
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No hay registros de asistencia para el período y filtros seleccionados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>

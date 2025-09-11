<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2|| $_SESSION['rol'] == 3) {
    require_once "../conexion.php";
    $id_user = $_SESSION['idUser'];
    
    // Obtener el turno seleccionado si se ha enviado un filtro
    $selectedTurno = isset($_POST['turno']) ? $_POST['turno'] : '';
	//echo $selectedTurno;
	if ($selectedTurno ==""){
		$sqlc="select id_control_caja from control_caja where fecha_termino is null"	;
		$queryca = mysqli_query($conexion, $sqlc);
		if (!$queryca) {
			die("Error en la consulta de pedidos: " . mysqli_error($conexion));
		}
		while ($row = mysqli_fetch_assoc($queryca)){
			$selectedTurno=$turnoRow['id_control_caja'];
		}
	}
    // Consulta base para obtener pedidos
    $queryBase = "SELECT  SUM(cantidad) AS cantidad, nombre, id_producto FROM detalle_pedidos
					GROUP BY id_producto";

    // Agregar filtro de turno si se selecciona
    if ($selectedTurno!="") {
        $queryBase .= " WHERE p.id_control_caja = '$selectedTurno'";
    }
	$queryBase .= " ORDER BY cantidad DESC";
    // Ejecutar la consulta de pedidos y verificar errores
	//echo $queryBase;
    $querylistado = mysqli_query($conexion, $queryBase);
    if (!$querylistado) {
        die("Error en la consulta de pedidos: " . mysqli_error($conexion));
    }

    // Obtener lista de turnos para el filtro
	$sqlcajas="select control_caja.id_control_caja,control_caja.fecha_inicio, control_caja.fecha_termino ,
	usuarios.nombreUsuario, cajas.descripcion as caja, turnos.descripcion as turno
	from control_caja
	INNER JOIN usuarios on usuarios.id=control_caja.id_usuario
	INNER JOIN cajas on cajas.id_caja=control_caja.id_caja
	inner join turnos on turnos.id_turno=control_caja.id_turno
	order by control_caja.id_control_caja desc";
    $turnosQuery = mysqli_query($conexion, $sqlcajas);
    if (!$turnosQuery) {
        die("Error en la consulta de turnos: " . mysqli_error($conexion));
    }

    include_once "includes/header.php";
    
?>
    <div class="card">
        <div class="card-header">
            Listado Productos
        </div>
        <div class="card-body">
            <!-- Formulario de filtrado por turno -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="turno">Filtrar por Turno:</label>
                    <div class="input-group">
                        <select name="turno" id="turno" class="form-control">
                            <option value="">Todos</option>
                            <?php while ($turnoRow = mysqli_fetch_assoc($turnosQuery)) { ?>
                                <option value="<?php echo $turnoRow['id_control_caja']; ?>" <?php echo ($turnoRow['id_control_caja'] == $selectedTurno) ? 'selected' : ''; ?>>
                                    <?php 									
										$temp = explode(' ', $turnoRow['fecha_inicio']);
										$temp1 = explode('-', $temp[0]);
										$FechaIn = $temp1[2] . '-' . $temp1[1] . '-' . $temp1[0] . " " . $temp[1];
										$temp = explode(' ', $turnoRow['fecha_termino']);
										$temp1 = explode('-', $temp[0]);
										$FechaTe = $temp1[2] . '-' . $temp1[1] . '-' . $temp1[0] . " " . $temp[1];
										echo $FechaIn." | ",$FechaTe." | ",$turnoRow['caja']." | ",$turnoRow['turno']; 
									?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive mt-3">
                <table class="table table-striped" id="tbl">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Caantidad</th>                            
                            <th></th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($querylistado)) {
                            $estado = ($row['estado'] == 'PENDIENTE') ? '<span class="badge badge-danger">Abierta</span>' : '<span class="badge badge-success">Cerrada</span>';
                            $temp = explode(' ', $row['fecha']);
                            $temp1 = explode('-', $temp[0]);
                            $tFecha = $temp1[2] . '-' . $temp1[1] . '-' . $temp1[0] . " " . $temp[1];
                        ?>
                            <tr>
                                <td><?php echo $row['id_producto']; ?></td>
								<td><?php echo $row['nombre']; ?></td>
								<td><?php echo $row['cantidad']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
}

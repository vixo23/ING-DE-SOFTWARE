<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";
    $id_user = $_SESSION['idUser'];
    $query = mysqli_query($conexion, "SELECT p.*, s.nombre AS sala, u.nombre FROM pedidos p LEFT JOIN salas s ON p.id_sala = s.id INNER JOIN usuarios u ON p.id_usuario = u.id");
    include_once "includes/header.php";
?>
    <div class="card">
        <div class="card-header">
            Historial pedidos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tbl">
                    <thead>
                        <tr>
							<th>Fecha</th>
                            <th>Salon</th>
                            <th>Mesa</th>
                            <th>Total</th>
                            <th>Usuario</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query)) {
                            if ($row['estado'] == 'PENDIENTE') {
                                $estado = '<span class="badge badge-danger">Pendiente</span>';
                            } else {
                                $estado = '<span class="badge badge-success">Completado</span>';
                            }
							$temp=explode(' ',$row['fecha']);
							$temp1=explode('-',$temp[0]);
							$tFecha=$temp1[2].'-'.$temp1[1].'-'.$temp1[0]." ".$temp[1];
                        ?>
                            <tr>
								<td><?php echo $tFecha; ?></td>
                                <td><?php 
									if ($row['sala']==""){
										echo "VENTA DIRECTA";
									} else {
										echo $row['sala'];
									}	
								?></td>
                                <td><?php 
								if ($row['sala']==""){
									echo "-";
								} else {
									$row['num_mesa'];
								}	
								?></td>
                                
                                <td align='right'>$ <?php echo number_format($row['total'], 0, '.', ','); ?></td> 
                                <td><?php echo $row['nombre']; ?></td>
                                <td>
                                    <a href="#" class="btn"><?php echo $estado; ?></a>
                                </td>
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
?>
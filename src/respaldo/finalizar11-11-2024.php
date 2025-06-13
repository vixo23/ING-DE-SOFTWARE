<?php
session_start();
ob_start();

if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    $fecha = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual
    $id_sala = $_GET['id_sala'];
    $mesa = $_GET['mesa'];
    include_once "includes/header.php";
?>

<div class="card card-primary card-outline">
    <div class="card-body">
        <input type="hidden" id="id_sala" value="<?php echo $_GET['id_sala']; ?>">
        <input type="hidden" id="mesa" value="<?php echo $_GET['mesa']; ?>">

        <div class="row">
            <?php
            include "../conexion.php";
            // Consulta SQL para obtener el pedido pendiente
            $query = mysqli_query($conexion, "SELECT * FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $mesa AND estado = 'PENDIENTE'");
            $result = mysqli_fetch_assoc($query);

            if (!empty($result)) { 
                $id_pedido = $result['id'];
                $id_control_caja = $result['id_control_caja'];

                // Acumular total de todos los pedidos pendientes para esta mesa y sala
                $sql_total_pedidos = "SELECT SUM(detalle_pedidos.cantidad * platos.precio) as total_acumulado
                                      FROM detalle_pedidos
                                      INNER JOIN platos ON platos.id = detalle_pedidos.id_producto
                                      INNER JOIN pedidos ON pedidos.id = detalle_pedidos.id_pedido
                                      WHERE pedidos.id_sala = $id_sala AND pedidos.num_mesa = $mesa AND pedidos.estado = 'PENDIENTE'";
                $query_total = mysqli_query($conexion, $sql_total_pedidos);
                $total_pedido_result = mysqli_fetch_assoc($query_total);
                $total_acumulado = $total_pedido_result['total_acumulado'];

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // Obtener datos del formulario
                    $tipo_documento = $_POST['tipo_documento'];
                    $tipo_pago = $_POST['tipo_pago'];
                    $monto_pago = $_POST['monto_pago'];
                    
                    // Validar que el monto de pago sea mayor o igual al total acumulado
                    if ($monto_pago >= $total_acumulado) {
                        // Insertar el pago
                        $numero_documento = (isset($documentos[$tipo_documento])) ? $documentos[$tipo_documento] + 1 : 1;
                        $insert_pago = "INSERT INTO pagos (id_pedido, id_control_caja, id_forma_pago, tipo_documento, monto_pago, numero_documento, fecha) 
                                        VALUES ('$id_pedido', '$id_control_caja', '$tipo_pago', '$tipo_documento', '$monto_pago', '$numero_documento', '$fecha')";
                        if (!mysqli_query($conexion, $insert_pago)) {
                            echo "Error al guardar el pago: " . mysqli_error($conexion);
                            exit;
                        }

                        // Actualizar el estado de todos los pedidos pendientes de esta mesa y sala
                        $update_pedidos = "UPDATE pedidos SET estado = 'FINALIZADO' 
                                           WHERE id_sala = '$id_sala' 
                                             AND num_mesa = '$mesa' 
                                             AND estado = 'PENDIENTE'";
                        if (!mysqli_query($conexion, $update_pedidos)) {
                            echo "Error al actualizar los pedidos: " . mysqli_error($conexion);
                            exit;
                        }

                        echo "<p class='text-success'>Pago realizado y todos los pedidos finalizados con éxito.</p>";
                        header('Location: index.php');
                        exit;
                    } else {
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'El monto de pago no puede ser menor al total de los platos.'
                            });
                        </script>";
                    }
                }
            ?>
                <div class="col-md-9 text-center">
                    <div class="bg-gray py-2 px-3 mt-4">
                        <h2 class="mb-0">
                            $ <?php echo number_format($total_acumulado, 0, '.', ','); ?> 
                        </h2>
                    </div>
                    <div class="col-12">    
                        Mesa: <?php echo $_GET['mesa']; ?>
                    </div>                    
                    <div class="col-12">
                        Fecha: <?php 
                        $temp=explode(' ',$result['fecha']);
                        $temp1=explode('-',$temp[0]);
                        $tFecha=$temp1[2].'-'.$temp1[1].'-'.$temp1[0]." ".$temp[1];
                        echo $tFecha; 
                        ?>
                    </div>

                    <hr>
                    <h4>Platos</h4>
                    <div class="row">
                    <?php 
                    $query1 = mysqli_query($conexion, "SELECT detalle_pedidos.*, platos.imagen, pedidos.id_sala, pedidos.num_mesa, platos.nombre, platos.precio, detalle_pedidos.cantidad
                                                     FROM detalle_pedidos
                                                     INNER JOIN platos ON platos.id = detalle_pedidos.id_producto
                                                     INNER JOIN pedidos ON pedidos.id = detalle_pedidos.id_pedido
                                                     WHERE pedidos.estado = 'PENDIENTE' 
                                                     AND pedidos.id_sala = '$id_sala' 
                                                     AND pedidos.num_mesa = '$mesa'");

                    while ($data1 = mysqli_fetch_assoc($query1)) { ?>
                        <div class="col-md-3 card card-widget widget-user">
                            <div class="widget-user-header bg-success">
                                <h5 class="widget-user-desc">$ <?php echo number_format($data1['precio'], 0, '.', ','); ?></h5>
                            </div>
                            <div class="widget-user-image">
                                <img src="<?php echo ($data1['imagen'] == null) ? '../assets/img/default.png' : $data1['imagen']; ?>" width="100" height="100">
                            </div>
                            <div class="card-footer">
                            <div class="card-footer">
                            <div class="description-block">
                                <span><?php echo $data1['nombre'] . ' (' . $data1['cantidad'] . ')'; ?></span>
                            </div>
                        </div>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <form action="" method="POST" onsubmit="return validatePayment();">
                            <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                            <input type="hidden" name="total" value="<?php echo $total_acumulado; ?>">
                            <input type="hidden" name="mesa" value="<?php echo $_GET['mesa']; ?>">
                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col">
                                        <label for="tipo_documento">Documento</label>
                                        <select id="tipo_documento" name="tipo_documento" class="form-control form-control-sm">
                                            <option value="" disabled selected>Elija una opción</option>
                                            <?php 
                                                $sql="SELECT * FROM tipo_documento WHERE estado=1 ORDER BY id_tipo_documento";
                                                $query = mysqli_query($conexion, $sql);
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    ?><option value="<?= $row['tipo_documento'] ?>"><?= $row['descripcion'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>    
                                    <div class="col">
                                        <label for="numero_documento">Nº Documento</label>
                                        <input class="form-control form-control-sm" id="numero_documento" name="numero_documento" type="text" value="0" disabled>
                                    </div>
                                </div>    
                            </div>

                            <div class="form-group">
                                <label for="tipo_pago">Tipo de Pago</label>
                                <select class="form-control form-control-sm" id="tipo_pago" name="tipo_pago">
                                    <?php 
                                        $sql="SELECT * FROM forma_pago WHERE estado=1 ORDER BY id_forma_pago";
                                        $query = mysqli_query($conexion, $sql);
                                        while ($row = mysqli_fetch_assoc($query)) {
                                            ?><option value="<?= $row['id_forma_pago'] ?>"><?= $row['descripcion'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="monto_pago">Monto Pago</label>
                                <input type="number" name="monto_pago" class="form-control form-control-sm" id="monto_pago">
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Realizar Pago</button>
                        </form>
                    </div>
                </div>

            <?php } else { ?>
                <p>No hay pedidos pendientes para esta mesa y sala.</p>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    // Función para validación de pago
    function validatePayment() {
        const montoPago = document.getElementById('monto_pago').value;
        const total = document.getElementById('total').value;
        
        if (parseFloat(montoPago) < parseFloat(total)) {
            alert("El monto de pago es menor que el total de los pedidos.");
            return false;
        }
        return true;
    }
</script>

<?php
    include_once "includes/footer.php";
} else {
    header("Location: index.php");
    exit();
}
?>

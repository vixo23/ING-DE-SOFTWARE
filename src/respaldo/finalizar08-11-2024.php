<?php
session_start();
ob_start();  // Iniciar el almacenamiento en búfer de salida

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
                $id_control_caja = $result['id_control_caja']; // Obtener el id_control_caja de los pedidos

                // Obtener los últimos números de documento por tipo
                $sql_documento = "SELECT tipo_documento, MAX(numero_documento) AS max_documento FROM pagos GROUP BY tipo_documento";
                $query_documento = mysqli_query($conexion, $sql_documento);
                $documentos = array();  // Usar array() en lugar de []
                while ($row = mysqli_fetch_assoc($query_documento)) {
                    $documentos[$row['tipo_documento']] = $row['max_documento'];
                }

                // Si no hay registros previos, iniciar con el numero_documento 1
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // Obtener datos del formulario
                    $tipo_documento = $_POST['tipo_documento'];
                    $numero_documento = isset($documentos[$tipo_documento]) ? $documentos[$tipo_documento] + 1 : 1; // Incrementar el último número

                    $tipo_pago = $_POST['tipo_pago'];
                    $monto_pago = $_POST['monto_pago'];
                    $total_pedido = $result['total']; // Total de los platos

                    if ($monto_pago >= $total_pedido) {
                        // Insertar en la tabla pagos
                        $insert_pago = "INSERT INTO pagos (id_pedido, id_control_caja, id_forma_pago, tipo_documento, monto_pago, numero_documento, fecha) 
                                        VALUES ('$id_pedido', '$id_control_caja', '$tipo_pago', '$tipo_documento', '$monto_pago', '$numero_documento', '$fecha')";
                        if (!mysqli_query($conexion, $insert_pago)) {
                            echo "Error al guardar el pago: " . mysqli_error($conexion);
                            exit;
                        }

                        // Actualizar el estado de los pedidos a 'FINALIZADO'
                        $update_pedido = "UPDATE pedidos SET estado = 'FINALIZADO' WHERE id = '$id_pedido'";
                        if (!mysqli_query($conexion, $update_pedido)) {
                            echo "Error al actualizar el pedido: " . mysqli_error($conexion);
                            exit;
                        }

                        echo "<p class='text-success'>Pago realizado y pedido finalizado con éxito.</p>";
                        // Redirigir a index.php después de realizar el pago
                        header('Location: index.php');
                        exit; // Importante para detener la ejecución del script después de la redirección
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
                            $ <?php echo number_format($result['total'], 0, '.', ','); ?> 
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
                                                     INNER JOIN control_caja ON control_caja.id_control_caja = pedidos.id_control_caja
                                                     WHERE pedidos.id_control_caja IS NOT NULL
                                                     AND control_caja.monto_cierre IS NULL
                                                     AND control_caja.fecha_termino IS NULL
                                                     AND pedidos.estado = 'PENDIENTE'
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
                                <div class="description-block">
                                    <span><?php echo $data1['nombre']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                    
                </div>
                <div class="card card-primary card-outline col-md-3">
                    <form action="" method="POST" onsubmit="return validatePayment();">
                      <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                      <input type="hidden" name="total" value="<?php echo $result['total']; ?>">
                      <input type="hidden" name="mesa" value="<?php echo $_GET['mesa']; ?>">
                       <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <label for="tipo_documento">Documento</label>
                                <select id="tipo_documento" name="tipo_documento" class="form-control form-control-sm">
                                    <option value="" disabled selected>Elija una opción</option> <!-- Opción no seleccionable -->
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
                                <input class="form-control form-control-sm" id="numero_documento" name="numero_documento" type="text" value="0" disabled> <!-- Inicializamos en 0 -->
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
                        <?php    }    ?>
                        </select>
                      </div>     
                      <div class="form-group">
                          <label for="monto_pago">Monto Pago</label>
                          <input type="number" name="monto_pago" class="form-control form-control-sm" required min="0" step="any" id="monto_pago">
                      </div>  
                      <button type="submit" class="btn btn-success btn-block">Pagar</button>
                    </form>
                </div>
            <?php
            } else {
                echo "<p class='text-warning'>No hay pedidos pendientes para esta mesa.</p>";
            }
            ?>
        </div>
    </div>
</div>

<?php 
    include_once "includes/footer.php";
} else {
    header('Location: login.php');
}
?>

<script>
    function validatePayment() {
    var monto_pago = parseFloat(document.getElementById('monto_pago').value);
    var total_pedido = parseFloat("<?php echo $result['total']; ?>");

    if (monto_pago < total_pedido) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El monto de pago no puede ser menor al total de los platos.'
        });
        return false; // Evitar el envío del formulario
    } else {
        Swal.fire({
            icon: 'success',
            title: 'Pago Aceptado',
            text: 'El monto de pago es suficiente.',
            timer: 5000, // Tiempo en milisegundos (5 segundos)
            showConfirmButton: false // Eliminar el botón de confirmación
        }).then(() => {
            // Después de que la alerta se cierre, redirigir a index.php
            window.location.href = 'index.php';
        });

        return true; // Permitir el envío del formulario
    }
}


    document.getElementById('tipo_documento').addEventListener('change', function() {
        var tipoDocumento = this.value;
        var numeroDocumentoInput = document.getElementById('numero_documento');
        
        // Consultar el número de documento más alto para el tipo seleccionado
        <?php foreach ($documentos as $tipo_documento => $max_documento): ?>
        if(tipoDocumento === "<?php echo $tipo_documento; ?>") {
            numeroDocumentoInput.value = "<?php echo $max_documento + 1; ?>"; // Incrementar el último número
        }
        <?php endforeach; ?>
    });
</script>




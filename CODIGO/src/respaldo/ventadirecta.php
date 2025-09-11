<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2|| $_SESSION['rol'] == 3) {
    include_once "includes/header.php";
?>
    <div class="card card-primary card-outline">		
        <div class="card-header text-center">
            <h4 class="card-title text-center">
                <span class="badge badge-secondary">Venta Directa</span>
            </h4>
        </div>		
        <div class="card-body">
            <div class="row">
                <div class="col-7 col-sm-9">
                    <div class="tab-content" id="vert-tabs-right-tabContent">
                        <div class="tab-pane fade show active" id="vert-tabs-right-home" role="tabpanel" aria-labelledby="vert-tabs-right-home-tab">
                            <input type="hidden" id="id_sala" value="<?php echo $_GET['id_sala']; ?>">
                            <input type="hidden" id="mesa" value="<?php echo $_GET['mesa']; ?>">
                            <div class="row">
                            <?php
                                    include "../conexion.php";
                                    // Obtener platos disponibles
                                    $query = mysqli_query($conexion, "SELECT * FROM platos WHERE estado = 1");
                                    $result = mysqli_num_rows($query);
                                    if ($result > 0) {
                                        while ($data = mysqli_fetch_assoc($query)) { ?>
                                            <div class="col-md-3">
                                                <div class="col-12 text-center">
                                                    <img src="<?php echo ($data['imagen'] == null) ? '../assets/img/default.png' : $data['imagen']; ?>" width="110" height="110">
                                                </div>
                                                <h6 class="my-1 text-center" style="font-size: 14px;"><?php echo $data['nombre']; ?></h6>
                                                <div class="bg-light py-1 px-2 mt-1 text-center">
                                                    <h6 class="mb-0" style="font-size: 14px;">$ <?php echo number_format($data['precio'], 0, '.', ','); ?></h6>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <a class="btn btn-primary btn-sm btn-block addDetalle" style="font-size: 20px;" href="#" data-id="<?php echo $data['id']; ?>">
                                                        <i class="fas fa-cart-plus mr-1"></i> Agregar
                                                    </a>
                                                </div>
                                            </div>
                                    <?php }
                                    }
                                    ?>

                            </div>
                        </div>
                        <div class="tab-pane fade" id="pedido" role="tabpanel" aria-labelledby="pedido-tab">
                            <div class="form-group">
                                <input type="text" id="observacion" class="form-control" rows="1" placeholder="Observación">
                            </div>

                            <div class="form-group">
                                <label>Cantidad de personas</label>
                                <input for="cantidad_personas" name="cantidad_personas" id="cantidad_personas" type="text" value=1></input>
                            </div>

                            <div class="row" id="detalle_pedido"></div>
                            <hr>
                            <button class="btn btn-primary" type="button" id="realizar_pedido_vd">Enviar pedido</button>
                        </div>
                        <div class="tab-pane fade" id="pagar" role="tabpanel" aria-labelledby="pagar-tab">
                            <div class="row" id="detalle_pagar"></div>
                            <hr>
                            <div class="form-group">
                                <form>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col">
                                                <label for="tipo_documento">Documento</label>
                                                <select id="tipo_documento" name="tipo_documento" class="form-control form-control-sm">
                                                <?php 
                                                    $sql = "SELECT * FROM tipo_documento WHERE estado=1 ORDER BY id_tipo_documento";
                                                    $query = mysqli_query($conexion, $sql);
                                                    while ($row = mysqli_fetch_assoc($query)) {
                                                        echo "<option value='{$row['tipo_documento']}'>{$row['descripcion']}</option>";
                                                    }	
                                                ?>
                                                </select>
                                            </div>	
                                            <div class="col">
                                                <label for="folio">Nº Documento</label>
                                                <?php 
                                                    $sql = "SELECT nro_correlativo FROM tipo_documento WHERE tipo_documento=39";
                                                    $query = mysqli_query($conexion, $sql);
                                                    while ($row = mysqli_fetch_assoc($query)) { ?>
                                                <input class="form-control form-control-sm" id="folio" name="folio" type="text" value="<?= $row['nro_correlativo'] + 1 ?>" disabled>
                                                <?php } ?>
                                            </div>
                                        </div>	
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo_pago">Tipo de Pago</label>
                                        <select id="tipo_pago" name="tipo_pago" class="form-control form-control-sm">
                                        <?php 
                                            $sql = "SELECT * FROM forma_pago WHERE estado=1 ORDER BY id_forma_pago";
                                            $query = mysqli_query($conexion, $sql);
                                            while ($row = mysqli_fetch_assoc($query)) {
                                                echo "<option value='{$row['id_forma_pago']}'>{$row['descripcion']}</option>";
                                            }
                                        ?>
                                        </select>					  
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col">
                                                <label for="total_pago">Total</label>
                                                <?php 
                                                    $sqltotal = "SELECT Total FROM pedidos WHERE id_sala=0 AND num_mesa=1 AND estado='PENDIENTE'";
                                                    $querytotal = mysqli_query($conexion, $sqltotal);
                                                    $total = mysqli_fetch_assoc($querytotal);
                                                ?>
                                                <input class="form-control form-control-sm" id="total_pago" name="total_pago" type="text" value="$ <?php echo number_format($total['Total'], 0, '.', ','); ?>" disabled> 
                                            </div>
                                            <div class="col">
                                                <label for="monto_pago">Monto Pago</label>
                                                <input class="form-control form-control-sm" id="monto_pago" name="monto_pago" type="text" required>
                                                <div class="invalid-feedback">
                                                    Ingrese monto de pago
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <a class="btn btn-primary btn-block btn-flat finalizarPedido" href="#">
                                            <i class="fas fa-cart-plus mr-2"></i>
                                            Pagar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-5 col-sm-3">
                    <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-right-home-tab" data-toggle="pill" href="#vert-tabs-right-home" role="tab" aria-controls="vert-tabs-right-home" aria-selected="true">Platos</a>
                        <a class="nav-link" id="pedido-tab" data-toggle="pill" href="#pedido" role="tab" aria-controls="pedido" aria-selected="false">Ver Pedido</a>
                        <a class="nav-link" id="pagar-tab" data-toggle="pill" href="#pagar" role="tab" aria-controls="pagar" aria-selected="false">Pagar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </div>
<?php include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
    exit;
}
?>
<script>

$('#realizar_pedido_vd').click(function (e) {
    e.preventDefault();
    var action = 'procesarPedido';
    var id_sala = $('#id_sala').val();
    var mesa = $('#mesa').val();
    var observacion = $('#observacion').val();
    var cantidad_personas = $('#cantidad_personas').val();
    
    console.log({
                procesarPedido: action,
                id_sala: id_sala,
                mesa: mesa,
                observacion: observacion,
                cantidad_personas: cantidad_personas
            });

    // Verificar que la sala sea 0 y la mesa 1
    if (id_sala == 0 && mesa == 1) {
        $.ajax({
            url: 'ajax.php',
            async: true,
            data: {
                procesarPedido: action,
                id_sala: id_sala,
                mesa: mesa,
                observacion: observacion,
                cantidad_personas: cantidad_personas,
            },
            success: function (response) {
                const res = JSON.parse(response);
                if (res.mensaje !== 'error') {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Pedido Enviado',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    setTimeout(() => {
                        window.location = 'ventadirecta.php?id_sala=0&mesas=0&mesa=1&prueba';
                    }, 1500);
                } else {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Error al generar',
                        showConfirmButton: false,
                        timer: 2000
                    })
                }
            },
            error: function (error) {
                alert(error);
            }
        });
    } else {
        Swal.fire({
            position: 'center',
            icon: 'error',
            title: 'Selección de salón o mesa incorrecta',
            showConfirmButton: false,
            timer: 2000
        });
    }
});

</script>
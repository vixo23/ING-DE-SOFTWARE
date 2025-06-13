<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3|| $_SESSION['rol'] == 2) {
    include_once "includes/header.php";
    
?>
    <div class="card card-primary card-outline">
        <div class="card-header text-center">			
            <a class="btn btn-success" href="mesas.php?id_sala=<?php echo $_GET['id_sala']?>&mesas=<?php echo $_GET['mesas'] ?>">Volver a Mesas</a>
        </div>
        <div class="card-header text-center">
            <h3 class="card-title text-center">
            <span class="badge badge-secondary" >Mesa <?php echo $_GET['mesa']; ?></span>
            </h3>
        </div>		
        <div class="card-body">
            <div class="row">
                <div class="col-7 col-sm-9">
                    <div class="tab-content" id="vert-tabs-right-tabContent">
                        <div class="tab-pane fade show active" id="vert-tabs-right-home" role="tabpanel" aria-labelledby="vert-tabs-right-home-tab">
                            <input type="hidden" id="id_sala" value="<?php echo $_GET['id_sala'] ?>">
                            <input type="hidden" id="mesa" value="<?php echo $_GET['mesa'] ?>">
                            <div class="row">
                                <?php
                                include "../conexion.php";
                                $query = mysqli_query($conexion, "SELECT * FROM platos WHERE estado = 1");
                                $result = mysqli_num_rows($query);
                                if ($result > 0) {
                                    while ($data = mysqli_fetch_assoc($query)) { ?>
                                        <div class="col-md-3">
                                            <div class="col-12">
                                                <img src="<?php echo ($data['imagen'] == null) ? '../assets/img/default.png' : $data['imagen']; ?>"  width="100" height="100">
                                            </div>
                                            <h6 class="my-3"><?php echo $data['nombre']; ?></h6>

                                            <div class="bg-gray py-2 px-3 mt-4">
                                                <h2 class="mb-0">
                                                    $ <?php echo number_format($data['precio'], 0, '.', ','); ?>
                                                </h2>
                                            </div>

                                            <div class="mt-4">
                                                <a class="btn btn-primary btn-block btn-flat addDetalle" href="#" data-id="<?php echo $data['id']; ?>">
                                                    <i class="fas fa-cart-plus mr-2"></i>
                                                    Agregar
                                                </a>
                                            </div>
                                        </div>
                                    <?php }
                                } ?>
                            </div>
                        </div>

                        <!-- Sección de ver Pedido -->
                        <div class="tab-pane fade" id="pedido" role="tabpanel" aria-labelledby="pedido-tab">
                            <div class="row" id="detalle_pedido">
                                <?php
                                // Obtener los platos ya añadidos al pedido
                                $id_sala = $_GET['id_sala'];  // Asegúrate de que esta variable esté definida
                                $mesa = $_GET['mesa'];        // Asegúrate de que esta variable esté definida
                                $query_platos_usuario = "
                                    SELECT detalle_pedidos.*, platos.imagen, pedidos.id_sala, pedidos.num_mesa, platos.nombre, platos.precio, detalle_pedidos.cantidad
                                    FROM detalle_pedidos
                                    INNER JOIN platos ON platos.id = detalle_pedidos.id_producto
                                    INNER JOIN pedidos ON pedidos.id = detalle_pedidos.id_pedido
                                    INNER JOIN control_caja ON control_caja.id_control_caja = pedidos.id_control_caja
                                    WHERE pedidos.id_control_caja IS NOT NULL
                                    AND control_caja.monto_cierre IS NULL
                                    AND control_caja.fecha_termino IS NULL
                                    AND pedidos.estado = 'PENDIENTE'
                                    AND pedidos.id_sala = '$id_sala'
                                    AND pedidos.num_mesa = '$mesa'
                                ";
                                $resultado_platos_usuario = mysqli_query($conexion, $query_platos_usuario);
                                while ($data_plato = mysqli_fetch_assoc($resultado_platos_usuario)) { ?>
                                    <div class="col-md-3">
                                        <div class="col-12">
                                            <img src="<?php echo ($data_plato['imagen'] == null) ? '../assets/img/default.png' : $data_plato['imagen']; ?>"  width="100" height="100">
                                        </div>
                                        <h6 class="my-3"><?php echo $data_plato['nombre']; ?></h6>
                                        <div class="bg-gray py-2 px-3 mt-4">
                                            <h2 class="mb-0">
                                                $ <?php echo number_format($data_plato['precio'], 0, '.', ','); ?>
                                            </h2>
                                        </div>
                                        <p>Cantidad: <?php echo $data_plato['cantidad']; ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label for="observacion">Observaciones</label>
                                <textarea id="observacion" class="form-control" rows="3" placeholder="Observaciones"></textarea>
                            </div>
                            <button class="btn btn-primary" type="button" id="realizar_pedido">Realizar pedido</button>
                        </div>
                    </div>
                </div>
                <div class="col-5 col-sm-3">
                    <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-right-home-tab" data-toggle="pill" href="#vert-tabs-right-home" role="tab" aria-controls="vert-tabs-right-home" aria-selected="true">Platos</a>
                        <a class="nav-link" id="pedido-tab" data-toggle="pill" href="#pedido" role="tab" aria-controls="pedido" aria-selected="false">ver Pedido</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </div>
<?php include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
}
?>

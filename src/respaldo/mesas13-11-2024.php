<?php
session_start();
include "../conexion.php";

// Verifica que el usuario tenga los permisos necesarios
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    $id = $_GET['id_sala'];
    $mesas = $_GET['mesas'];

    $salon = mysqli_query($conexion, "SELECT nombre FROM salas WHERE id = $id");

    if ($salon && mysqli_num_rows($salon) > 0) {
        $results = mysqli_fetch_assoc($salon);
        $nombre_salon = $results['nombre'];
    } else {
        $nombre_salon = "Salón no encontrado"; // Mensaje alternativo si no se encuentra el salón
    }

    include_once "includes/header.php";

    // Si se ha seleccionado una mesa para asignar el garzón
    if (isset($_GET['mesa']) && isset($_SESSION['garzon_id'])) {
        $mesa_id = $_GET['mesa'];
        $garzon_id = $_SESSION['garzon_id'];
        $garzon_color = $_SESSION['garzon_color']; // Color del garzón
       
        // Actualizamos el color de la mesa ocupada por este garzón
        $updateQuery = "UPDATE pedidos SET Color_Garzon = '$garzon_color' WHERE id_sala = $id AND num_mesa = $mesa_id AND estado = 'PENDIENTE'";
        mysqli_query($conexion, $updateQuery);
    }

?>


<div class="card">
    <div class="card-header text-center">
        <h4 class="my-3 text-center"><span class="badge badge-secondary">Salón <?php echo $nombre_salon; ?></span></h4>
    </div>
    <div class="card-header text-center">
        <a class="btn btn-info" href="index.php">Volver a Salones</a>
    </div>
    <div class="card-body">
        <div class="card-body text-center">
            <h5>Garzón Seleccionado: <?php echo $_SESSION['garzon_name']; ?></h5>
            <div style="display: inline-block; width: 50px; height: 20px; background-color: <?php echo $_SESSION['garzon_color']; ?>;"></div>
        </div>

        <div class="row">
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query);
                if ($data['mesas'] == $mesas) {
                    $item = 1;
                    for ($i = 0; $i < $mesas; $i++) {
                        // Consultamos el color del garzón para la mesa ocupada
                        $consulta = mysqli_query($conexion, "SELECT Color_Garzon FROM pedidos WHERE id_sala = $id AND num_mesa = $item AND estado = 'PENDIENTE'");
                        $resultPedido = mysqli_fetch_assoc($consulta);

                        // Si la mesa está libre, color verde; si está ocupada, color del garzón
                        $mesaColor = empty($resultPedido) ? 'bg-success' : ''; // Verde para mesas libres
                        $colorStyle = empty($resultPedido) ? '' : 'background-color: ' . $resultPedido['Color_Garzon'] . ';'; // Color del garzón para mesas ocupadas
            ?>
                        <div class="col-md-3">
                            <div class="card card-widget widget-user">
                                <div class="widget-user-header <?php echo $mesaColor; ?>" style="<?php echo $colorStyle; ?>">
                                    <h3 class="widget-user-username">MESA</h3>
                                    <h5 class="widget-user-desc"><?php echo $item; ?></h5>
                                </div>
                                <div class="widget-user-image">
                                    <img class="img-circle elevation-2" src="../assets/img/mesa.jpg" alt="User Avatar">
                                </div>
                                <div class="card-footer">
                                    <div class="description-block">
                                        <?php 
                                        if (empty($resultPedido)) {
                                            echo '<a class="btn btn-outline-info" href="pedido.php?id_sala=' . $id . '&mesas=' . $mesas . '&mesa=' . $item . '&garzon_id=' . $_SESSION['garzon_id'] . '">Atender</a>';
                                        } else {
                                            echo '<a class="btn btn-outline-info" href="pedido.php?id_sala=' . $id . '&mesas=' . $mesas . '&mesa=' . $item . '&garzon_id=' . $_SESSION['garzon_id'] . '">Atender</a>';
                                            echo '<a class="btn btn-outline-success" href="finalizar.php?id_sala=' . $id . '&mesa=' . $item . '">Finalizar</a>';
                                        } 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

            <?php 
                        $item++;
                    }
                }
            } ?>
        </div>
    </div>
</div>

<?php 
include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
} 
?>
<script type="text/javascript">
    window.onbeforeunload = function() {
        // Enviar una solicitud para destruir la sesión
        <?php
            // Asegúrate de destruir la sesión al salir de la página
            if (isset($_SESSION['password_verified'])) {
                $_SESSION['password_verified'] = false;  // O puedes usar session_destroy()
            }
        ?>
    };
</script>

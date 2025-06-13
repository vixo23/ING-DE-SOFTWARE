<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3 || $_SESSION['rol'] == 2 ) {
    include_once "includes/header.php";
    
    if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 || $_SESSION['rol'] == 3) {
        $id_sala = isset($_GET['id_sala']) ? (int)$_GET['id_sala'] : 0;
        $mesas = isset($_GET['mesas']) ? (int)$_GET['mesas'] : 0;
        $mesa = isset($_GET['mesa']) ? (int)$_GET['mesa'] : 0;
        $urls="pedido.php?id_sala=$id_sala&mesas=$mesas&mesa=$mesa";
		//echo $urls;
        // Obtener el nombre del salón
        $salon = mysqli_query($conexion, "SELECT nombre FROM salas WHERE id = $id_sala");
        if ($salon && mysqli_num_rows($salon) > 0) {
            $results = mysqli_fetch_assoc($salon);
            $nombre_salon = $results['nombre'];
        } else {
            // Mensaje de error si no se encuentra el salón
            $error_message = "Salón no encontrado";
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: '$error_message',
                    });
                  </script>";
        }
    }
?>
    <div class="card card-primary card-outline">
        <div class="card-header text-center">			
            <a class="btn btn-success" href="mesas.php?id_sala=<?php echo $_GET['id_sala']?>&mesas=<?php echo $_GET['mesas'] ?>">Volver a Mesas</a>
        </div>
        <div class="card-header text-center">
            <h3 class="card-title text-center">
            <span class="my-3 text-center"><span class="badge badge-secondary">Salón <?php echo $nombre_salon; ?></span></span>
            <span class="badge badge-secondary" >Mesa <?php echo $_GET['mesa']; ?></span>
            <span class="badge badge-secondary" >Atendido por: <?php echo $_SESSION['garzon_name']; ?></span>
            <span class="badge badge-secondary" style="background-color: <?php echo htmlspecialchars($_SESSION['garzon_color']); ?>;"></span>
            <div class="col-md-3">                
            </h3>           
        </div>	

        <div class="row">
            <?php
			include "../conexion.php";
			$query = mysqli_query($conexion, "SELECT * FROM grupos WHERE estado = 1");
			$result = mysqli_num_rows($query);
			if ($result > 0) {
				while ($data = mysqli_fetch_assoc($query)) { 
					$id_grupo=$data['id'];
					$colorStyle = "width:200px; height:100px; background-color:" . $data['color'];
					?>
					<div class="col-md-3">	
						<input type="button" style="<?php echo $colorStyle ; ?>" 
						onclick="location.href='<? echo $urls."&grupo=".$data['id']; ?>';" value="<?php echo $data['nombre']; ?>" />
					</div>
				<?php 
				}
			}
			?>
        </div>
        <!-- /.card -->
    </div>
<?php include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
}
?>
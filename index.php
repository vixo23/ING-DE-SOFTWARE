<?php
session_start();
if (!empty($_SESSION['active'])) {
    header('location: src/');
    exit; // Asegúrate de que no se siga ejecutando el script después de la redirección
} else {
    if (!empty($_POST)) {
        $alert = '';
        if (empty($_POST['rut']) || empty($_POST['clave'])) {
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Ingrese RUT y Clave
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        } else {
            require_once "conexion.php";
            $clave = mysqli_real_escape_string($conexion, $_POST['clave']);
            $rut = mysqli_real_escape_string($conexion, $_POST['rut']);
			$sql="SELECT * FROM empresas WHERE rut = '$rut' AND clave = '$clave'";
            $query = mysqli_query($conexion, $sql);
            
            if (mysqli_num_rows($query) > 0) {
                $dato = mysqli_fetch_array($query);
                
                // Verifica el estado de la empresa
                if ($dato['status'] == 0) {
                    header('Location: src/sinAcceso.php');
                    exit; // Termina el script después de la redirección
                } else {
                  $_SESSION['active'] = true;
                  $_SESSION['idempresa'] = $dato['id_empresas'];
                  $_SESSION['nombreempresa'] = $dato['nombreFantasia'];
                  $_SESSION['rol'] = 1;
                    header('Location: src/dashboard.php');
                    exit; // Termina el script después de la redirección
                }
            } else {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Clave incorrecta '.$sql.'
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                session_destroy();
            }
            mysqli_close($conexion); // Cierra la conexión después de usarla
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Eugcom - Sistema TicTac - Ingreso</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">

  <div class="login-logo">
	<img src="assets/img/logo.png"></img>
    <br>
	<a href="#"><b>TicTac</b>Eugcom</a>	
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Iniciar Sesi&oacute;n</p>

      <form action="" method="post" autocomplete="off">
      <?php echo (isset($alert)) ? $alert : '' ; ?>  
      <div class="input-group mb-3">
          <input type="text" class="form-control" name="rut" placeholder="Rut Empresa">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="clave" placeholder="Clave">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <!-- /.col -->
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block" >Ingresar</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>

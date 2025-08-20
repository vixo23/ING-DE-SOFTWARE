<?php
include "../conexion.php";
if (empty($_SESSION['active'])) {
    header('Location: ../');
}
date_default_timezone_set('America/Santiago');
$id_empresa=$_SESSION['idempresa'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eugcom - Sistema TicTac</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <!-- IonIcons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <!-- jQuery completo (no slim) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Popper.js para tooltips/modals -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <!-- Bootstrap 4 JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

	<script language="JavaScript">
		function mueveReloj(){
			momentoActual = new Date()
			   const dias = [
			'Domingo',
			'Lunes',
			'Martes',
			'Miercoles',
			'Jueves',
			'Viernes',
			'Sabado',
			];
			
			const meses = [
			'Enero',
			'Febrero',
			'Marzo',
			'Abril',
			'Mayo',
			'Junio',
			'Julio',
			'Agosto',
			'Septiembre',
			'Octubre',
			'Noviembre',
			'Diciembre',
			];
			const numeroDia = new Date(momentoActual).getDay();
			const nombreDia = dias[numeroDia];
			
			const numeroMes = new Date(momentoActual).getMonth();
			const nombreMes = meses[numeroMes];

			hora = momentoActual.getHours()
			minuto = momentoActual.getMinutes()
			segundo = momentoActual.getSeconds()

			str_segundo = new String (segundo)
			if (str_segundo.length == 1)
			   segundo = "0" + segundo

			str_minuto = new String (minuto)
			if (str_minuto.length == 1)
			   minuto = "0" + minuto

			str_hora = new String (hora)
			if (str_hora.length == 1)
			   hora = "0" + hora
			   dd= momentoActual.getDate()
			   //mm= momentoActual.getMonth() + 1
			   //yy= momentoActual.getFullYear().toString().slice(-2)
			   //yyyy= momentoActual.getFullYear()

			FechaImprimible = nombreDia + ", " + dd + " de " + nombreMes 
			horaImprimible = hora + " : " + minuto + " : " + segundo	
			document.form_reloj.fechita.value = FechaImprimible
			document.form_reloj.reloj.value = horaImprimible
			setTimeout("mueveReloj()",1000)
		}

	</script>
</head>

<body class="hold-transition sidebar-mini" onload="mueveReloj()">
    <div class="wrapper">
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="dashboard.php" class="brand-link">
                <img src="../assets/img/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Eugcom - TicTac</span>
            </a>


            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-info"></i>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $_SESSION['idempresa']."-". $_SESSION['nombreempresa']; ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Principal
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cart-plus"></i>
                                <p>
                                    Tablas Maestras
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
								<li class="nav-item">
                                    <a href="centrocosto.php" class="nav-link">
										<i class="nav-icon fas fa-cash-register"></i>
                                        <p>Centro de Costo</p>
                                    </a>
                                </li>
								<li class="nav-item">
                                    <a href="ficha_empleados.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Ficha de Empleados</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="tipo_vacaciones.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Tipo de Vacaciones</p>
                                    </a>
                                </li>
								<li class="nav-item">
                                    <a href="departamento.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Departamentos</p>
                                    </a>
                                </li>
								<li class="nav-item">
                                    <a href="motivo_permisos.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Motivos de Permisos</p>
                                    </a>
                                </li>
								<li class="nav-item">
                                    <a href="sucursal.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Sucursal</p>
                                    </a>
                                </li>
								<li class="nav-item">
                                    <a href="tipo_contrato.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Tipo de Contrato</p>
                                    </a>
                                </li>
								<li class="nav-item">
                                    <a href="nombreturno.php" class="nav-link">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Nombre Turnos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
						<li class="nav-item">
                            <a href="ingreso_empleado.php" class="nav-link">
                                <i class="nav-icon fas fa-user"></i><p>Ingreso de Empleado</p>
                            </a>
                        </li>
						<li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-tag"></i>
                                <p>Turnos<i class="fas fa-angle-left right"></i></p>
                            </a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
                                    <a href="creacion_turnos.php" class="nav-link">
                                        <i class="nav-icon fas fa-coins"></i><p>Creacion de Turnos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="asignacion_turnos.php" class="nav-link">
                                        <i class="nav-icon fas fa-chart-line"></i><p>Asignacion de Turnos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="listadoturnos.php" class="nav-link">
                                        <i class="nav-icon fas fa-plus"></i><p>Listado de Turnos</p></a>
                                </li>
                            </ul>
                        </li>
						<li class="nav-item">
                            <a href="informa_asistencia.php" class="nav-link">
                                <i class="nav-icon fas fa-coffee"></i><p>Informe de Asistencia</p>
                            </a>
                        </li>
						<li class="nav-item">
                            <a href="ingreso_permisos.php" class="nav-link">
                                <i class="nav-icon fas fa-door-open"></i><p>Ingreso de Permisos</p>
                            </a>
                        </li>
						<li class="nav-item">
							<a href="#" class="nav-link">
								<i class="nav-icon fas fa-list-ul"></i><p>Informes<i class="fas fa-angle-left right"></i></p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="permisos_aprobados.php" class="nav-link">
										<i class="nav-icon fas fa-chart-line"></i><p>Permisos Aprobados</p>
									</a>
								</li>                               
							</ul>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="ventas_producto.php" class="nav-link">
										<i class="nav-icon fas fa-chart-line"></i><p>Listado de Ventas por Producto</p>
									</a>
								</li>                               
							</ul>
                        </li>
                        <li class="nav-item">
                            <a href="salir.php" class="nav-link" data-toggle="modal" data-target="#modalCerrarSesion">
                                <i class="nav-icon fas fa-power-off"></i>
                                <p>
                                    Salir
                                </p>
                            </a>
                        </li>
                        <script>
                        function confirmarCerrarSesion() {
                            return confirm("¿Estás seguro que deseas cerrar sesión?");
                        }
                        </script>

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid py-2">
                    <div class="modal fade" id="modalCerrarSesion" tabindex="-1" role="dialog" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="modalCerrarSesionLabel">Confirmar salida</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ¿Estás seguro que deseas cerrar sesión?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <a href="salir.php" class="btn btn-danger">Cerrar sesión</a>
      </div>
    </div>
  </div>
</div>

</body>
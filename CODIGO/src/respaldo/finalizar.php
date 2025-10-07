<?php
session_start();
ob_start();

if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 || $_SESSION['rol'] == 3) {
    $fecha = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual
    $id_sala = $_GET['id_sala'];
    $mesa=$_GET['mesa'];
    
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
						 $sql_folio = "SELECT nro_correlativo FROM tipo_documento
                                      WHERE tipo_documento=$tipo_documento";
						$query_folio = mysqli_query($conexion, $sql_folio);
						$result_folio = mysqli_fetch_assoc($query_folio);
						$folio = $result_folio['nro_correlativo'] + 1;
						
                        $numero_documento = (isset($documentos[$tipo_documento])) ? $documentos[$tipo_documento] + 1 : 1;
                        $insert_pago = "INSERT INTO pagos (id_pedido, id_control_caja, id_forma_pago, tipo_documento, monto_pago, numero_documento, fecha) 
                                        VALUES ('$id_pedido', '$id_control_caja', '$tipo_pago', '$tipo_documento', '$monto_pago', '$folio', '$fecha')";
                        if (!mysqli_query($conexion, $insert_pago)) {
                            echo "Error al guardar el pago: " . mysqli_error($conexion);
                            exit;
                        }
						
						$update_folio="UPDATE tipo_documento SET nro_correlativo=$folio WHERE tipo_documento=$tipo_documento";
						if (!mysqli_query($conexion, $update_folio)) {
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
						
						
						//INICIA  TXT DE BOLETA		
						
						$lineaB=array();
						
						//$ruta="D:\AppServ\www\EugBoleta\txt_pre\x/";
						$ruta="D:/AppServ/www/EugBoleta/txt_pre/";
						
						$sql_txt="SELECT * FROM empresa";						
						$query_txt = mysqli_query($conexion, $sql_txt);
						$result_txt = mysqli_fetch_assoc($query_txt);

						$cRut  = "15450158-4";//$result_txt['rut_empresa']; 
						$cRazonSocial = "HUGO CRISTIAN GONZALEZ OLEA";//$result_txt['razon_social']; 
						$cGiro="PUB RESTAURANTE";//$result_txt["giro"]; 
						$cDireccion="OHIGGINS 1967";//$result_txt["direccion"]; 
						$cDireccion = str_replace("º"," ",$cDireccion);
						$cDireccion = str_replace("&"," ",$cDireccion);
						$cDireccion = str_replace(";",",",$cDireccion);
		
						$cComuna="CURACAVI";//$result_txt["comuna"]; 
						$cCiudad="SANTIAGO";//$result_txt["ciudad"]; 

						
						$cGiro = str_replace("º"," ",$cGiro);
						$cGiro = str_replace("&"," ",$cGiro);
						$cGiro = str_replace(";",",",$cGiro);
												
						$file = fopen($ruta."15450158-4_39_$folio.txt", "w") or die("Se produjo un error al crear el archivo");
						$tfecha = date('Y-m-d');
						$cliente_bol="66666666-6;1;CLIENTE BOLETA;PRINCIPAL;BOLETA;SANTIAGO;SANTIAGO;;;";
						
						$tTotal=$total_acumulado;
						$tNeto=round($tTotal / 1.19);						
						$tIva=round($tNeto * 0.19);	
						$tNeto=round($tNeto,0);
						$tIva=round($tIva,0);
						$tTotal=round($tTotal,0);

						$lineaA="A;39;$folio;$tfecha;3;2;;;$tfecha;$cRut;$cRazonSocial;$cGiro;PRINCIPAL;$cDireccion;$cComuna;$cCiudad;$cliente_bol;$tNeto;0;$tIva;$tTotal;;;;;";

						$sql_detalle = "SELECT *
                                      FROM detalle_pedidos
                                      INNER JOIN platos ON platos.id = detalle_pedidos.id_producto
                                      INNER JOIN pedidos ON pedidos.id = detalle_pedidos.id_pedido
                                      WHERE pedidos.id=$id_pedido";
						$query_detalle = mysqli_query($conexion, $sql_detalle);
						$result_detalle = mysqli_num_rows($query_detalle);
						if ($result_detalle > 0) {
							$i=0;
							$j=1;
							while ($data_temp2 = mysqli_fetch_assoc($query_detalle)) {
            
								$tCantidad = $data_temp2['cantidad'];
								$tPrecio = $data_temp2['precio'];
								$id_producto = $data_temp2['id_producto'];
								$tNombre= $data_temp2['nombre'];
								$tValor=$tCantidad * $tPrecio;
								$lineaB[$i]="B;$j;interno;$id_producto;;;$cRut;$tNombre;;;;;;;;;;;;;$tCantidad;;$tPrecio;0;0;;;$tValor;";
								$i++;
								$j++;
							}
						}
						$vuelto=$monto_pago-$tTotal;
						$lineaZ="Z;$monto_pago;$vuelto;;CAJA;";
						
						fwrite($file, $lineaA . PHP_EOL);	
						for ($j = 0; $j < $i; $j++) {
							fwrite($file, $lineaB[$j] . PHP_EOL);
						}	
						fwrite($file, $lineaZ . PHP_EOL);		
						fclose($file);
						$tfecha = date('Y-m-d');
						
						$ano_bol=date("Y");
						$mes_bol=date("m");
						$num_bol=$folio;
						$_SESSION['mes_bol']=date("m");
						$_SESSION['ano_bol']=date("Y");
						$_SESSION['num_bol']=$folio;
						//FIN TXT DE BOLETA
						//echo "<p class='text-success'>Pago realizado y todos los pedidos finalizados con éxito.</p>";/
                        //$filepdf = "../../EugBoleta/PDF/Termica/15450158-4/".$ano_bol."-".$mes_bol."/15450158-4_39_".$num_bol."_firmado_Termica.pdf";
						//if(file_exists($filepdf)){
						//	echo "<td align='center'><a target='_blank' href='../../EugBoleta/PDF/Termica/15450158-4/".$ano_bol."-".$mes_bol."/15450158-4_39_".$num_bol."_firmado_Termica.pdf' ><img ".$img.$on1."/></a></td>";
						//}
						sleep(3);						
						header('Location: boleta.php?pop=si ');
						//header(“ubicación: http://www.misitio.com/index.php?pop=si ”); 
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
										<?php 
                                                    $sql = "SELECT nro_correlativo FROM tipo_documento WHERE tipo_documento=39";
                                                    $query = mysqli_query($conexion, $sql);
                                                    while ($row = mysqli_fetch_assoc($query)) { ?>
                                                <input class="form-control form-control-sm" id="numero_documento" name="numero_documento" type="text" value="<?= $row['nro_correlativo'] + 1 ?>" disabled>
                                                <?php } ?>
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
    header("Location: permisos.php");
    exit();
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

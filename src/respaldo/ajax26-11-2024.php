<?php
require_once "../conexion.php";
session_start();

ob_clean();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_empresa = 1;

if (isset($_GET['detalle'])) {
    $id_usuario = $_SESSION['idUser'];
    $id_sala = $_GET['id_sala'];
    $mesa = $_GET['mesa'];

    // Eliminar datos de la tabla temporal si es necesario
    $query = mysqli_query($conexion, "DELETE FROM temp_pedidos2 WHERE id_usuario=$id_usuario AND id_sala=$id_sala AND id_mesa=$mesa");

    // Consulta para obtener los datos de detalle
    $sql = "
        SELECT dp.id, dp.cantidad, dp.precio, dp.id_producto, 
               p.imagen, ped.id_sala, ped.num_mesa, 
               p.nombre, p.precio AS precio_plato,
               0 AS editable  -- No editable para registros de 'detalle_pedidos'
        FROM detalle_pedidos dp
        INNER JOIN platos p ON p.id = dp.id_producto
        INNER JOIN pedidos ped ON ped.id = dp.id_pedido
        WHERE ped.estado = 'PENDIENTE'
          AND ped.id_sala = '$id_sala'
          AND ped.num_mesa = '$mesa'
        
        UNION ALL
        
        SELECT tp.id, tp.cantidad, tp.precio, tp.id_producto, 
               p.imagen, tp.id_sala, tp.id_mesa AS num_mesa, 
               p.nombre, p.precio AS precio_plato,
               1 AS editable  -- Editable para registros de 'temp_pedidos'
        FROM temp_pedidos tp
        INNER JOIN platos p ON p.id = tp.id_producto
        WHERE tp.id_usuario = '$id_usuario'
    ";


    $query_temp2 = mysqli_query($conexion, $sql);
    $result = mysqli_num_rows($query_temp2);

    // Insertar datos en la tabla temporal si es necesario
    if ($result > 0) {
        while ($data_temp2 = mysqli_fetch_assoc($query_temp2)) {
            $cantidad = $data_temp2['cantidad'];
            $preciox = $data_temp2['precio'];
            $id_producto = $data_temp2['id_producto'];
            $idx = $data_temp2['id'];

            $sql_temp2 = "INSERT INTO temp_pedidos2(cantidad, precio, id_producto, id_usuario, id_sala, id_mesa, id_pedido) 
                          VALUES ($cantidad, $preciox, $id_producto, $id_usuario, $id_sala, $mesa, $idx )";
            mysqli_query($conexion, $sql_temp2);
        }
    }

    // Resultado final a devolver
    $datos = array();
    $detalle = mysqli_query($conexion, $sql);

    if ($detalle) {
        while ($row = mysqli_fetch_assoc($detalle)) {
            $data['id'] = $row['id'];
            $data['nombre'] = $row['nombre'];
            $data['cantidad'] = $row['cantidad'];
            $data['precio'] = $row['precio'];
            $data['imagen'] = ($row['imagen'] == null) ? '../assets/img/default.png' : $row['imagen'];
            $data['total'] = $data['precio'] * $data['cantidad'];
            $data['editable'] = $row['editable'];  // Establecer si es editable
            array_push($datos, $data);
        }
    }

    echo json_encode($datos);
    die();
}else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];
    
    // Obtener los datos del plato a eliminar
    $query_select = mysqli_query($conexion, "SELECT * FROM temp_pedidos WHERE id = $id_detalle");
    $data = mysqli_fetch_assoc($query_select);
    
    if ($data) {
        $cantidad = $data['cantidad'];
        $precio = $data['precio'];
        $id_producto = $data['id_producto'];
        $id_usuario = $_SESSION['idUser']; // Usar el ID del usuario de la sesión
        $id_sala = isset($data['id_sala']) ? $data['id_sala'] : 'NULL';
        $id_mesa = isset($data['id_mesa']) ? $data['id_mesa'] : 'NULL';
        
        // Validar si id_pedido es NULL o tiene valor
        $id_pedido = isset($data['id_pedido']) && $data['id_pedido'] !== null ? $data['id_pedido'] : 'NULL';

        // Insertar en la tabla log_eliminaciones
        $query_log = mysqli_query($conexion, "
            INSERT INTO log_eliminaciones (cantidad, precio, id_producto, id_usuario, id_sala, id_mesa, id_pedido)
            VALUES ($cantidad, $precio, $id_producto, $id_usuario, $id_sala, $id_mesa, $id_pedido)
        ");

        // Verificar si la inserción en log fue exitosa
        if ($query_log) {
            // Eliminar el plato de temp_pedidos
            $query_delete = mysqli_query($conexion, "DELETE FROM temp_pedidos WHERE id = $id_detalle");
            if ($query_delete) {
                $msg = "ok";
            } else {
                $msg = "Error al eliminar el producto";
            }
        } else {
            $msg = "Error al registrar en log_eliminaciones";
        }
    } else {
        $msg = "Producto no encontrado";
    }

    echo $msg;
    die();
}


else if (isset($_GET['obtener_detalle'])) {
    $id = $_GET['id'];
    $query = mysqli_query($conexion, "SELECT * FROM productos WHERE id = '$id'");

    if ($query) {
        $plato = mysqli_fetch_assoc($query);
        
        // Asegúrate de que la respuesta esté en formato JSON
        echo json_encode($plato);
    } else {
        // En caso de error, se puede devolver un JSON con el error
        echo json_encode(array('error' => 'No se pudo obtener los detalles del plato'));
    }
    die();
}



 else if (isset($_GET['detalle_cantidad'])) {
    $id_detalle = $_GET['id'];
    $cantidad = $_GET['cantidad'];
    $query = mysqli_query($conexion, "UPDATE temp_pedidos set cantidad = $cantidad WHERE id = $id_detalle");
	$query2 = mysqli_query($conexion, "UPDATE temp_pedidos2 set cantidad = $cantidad WHERE id_pedido = $id_detalle");
    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
}else if (isset($_GET['procesarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $observacion = $_GET['observacion'];
    $cantidad_personas = $_GET['cantidad_personas'];
    // Obtener el id_garzones y color del garzón desde la sesión, solo si hay un garzón
    $garzon_name = isset($_SESSION['garzon_name']) ? $_SESSION['garzon_name'] : null;
    $id_garzon = 0;  // Valor por defecto si no hay garzón
    $color_garzon = 0; // Valor por defecto si no hay garzón

    if ($garzon_name) {
        // Si existe el nombre del garzón, buscar su ID y color
        if ($conexion instanceof mysqli) {
            $query = "SELECT Id_Garzones, Color_Garzon FROM garzones WHERE Nombre = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("s", $garzon_name);
            $stmt->execute();
            $stmt->bind_result($id_garzon, $color_garzon);
            $stmt->fetch();
            $stmt->close();

            $_SESSION['garzon_color'] = $color_garzon;
        } else {
            die("Error en la conexión a la base de datos.");
        }
    }

    // Verificar si ya existe un pedido pendiente para la misma mesa y salón
    $consultaPedidoPendiente = mysqli_query($conexion, "SELECT id FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $mesa AND estado = 'PENDIENTE' LIMIT 1");

    if (!$consultaPedidoPendiente) {
        echo json_encode(array('mensaje' => 'error', 'error' => 'Error al verificar si hay un pedido pendiente.'));
        die();
    }

    $pedidoPendiente = mysqli_fetch_assoc($consultaPedidoPendiente);

    if ($pedidoPendiente) {
        // Si ya existe un pedido, actualizarlo
        $id_pedido = $pedidoPendiente['id'];

        $consulta = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");

        if (!$consulta) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al obtener los detalles del pedido temporal.'));
            die();
        }

        // Insertar o actualizar plato por plato en detalle_pedidos
        while ($row = mysqli_fetch_assoc($consulta)) {
            $nombre = $row['nombre'];
            $cantidad = $row['cantidad'];
            $precio = $row['precio'];
            $id_producto = $row['id_producto'];

            // Verificar si el plato ya existe en detalle_pedidos
            $verificarPlato = mysqli_query($conexion, "SELECT id, cantidad FROM detalle_pedidos WHERE id_pedido = $id_pedido AND id_producto = $id_producto");

            if (!$verificarPlato) {
                echo json_encode(array('mensaje' => 'error', 'error' => 'Error al verificar si el plato ya existe en el pedido.'));
                die();
            }

            $platoExistente = mysqli_fetch_assoc($verificarPlato);

            if ($platoExistente) {
                $nuevaCantidad = $platoExistente['cantidad'] + $cantidad;
                $actualizarCantidad = mysqli_query($conexion, "UPDATE detalle_pedidos SET cantidad = $nuevaCantidad WHERE id = " . $platoExistente['id']);

                if (!$actualizarCantidad) {
                    echo json_encode(array('mensaje' => 'error', 'error' => 'Error al actualizar la cantidad del plato.'));
                    die();
                }
            } else {
                $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_pedidos (id_producto, nombre, precio, cantidad, id_pedido) VALUES ($id_producto, '$nombre', '$precio', $cantidad, $id_pedido)");

                if (!$insertarDet) {
                    echo json_encode(array('mensaje' => 'error', 'error' => 'Error al insertar en detalle_pedidos.'));
                    die();
                }
            }
        }

        // Calcular el nuevo total
        $consultaDetalle = mysqli_query($conexion, "SELECT SUM(d.cantidad * d.precio) AS total_detalle FROM detalle_pedidos d WHERE d.id_pedido = $id_pedido");

        if (!$consultaDetalle) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al obtener el total de detalle_pedidos.'));
            die();
        }

        $detalleData = mysqli_fetch_assoc($consultaDetalle);
        $nuevoTotal = $detalleData['total_detalle'];

        // Actualizar el total en la tabla pedidos
        $actualizarPedido = mysqli_query($conexion, "UPDATE pedidos SET total = '$nuevoTotal', Color_Garzon = '$color_garzon' WHERE id = $id_pedido");

        if (!$actualizarPedido) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al actualizar el total en la tabla pedidos.'));
            die();
        }

        $eliminarTemporal = mysqli_query($conexion, "DELETE FROM temp_pedidos WHERE id_usuario = $id_user");

        if (!$eliminarTemporal) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al eliminar los productos del pedido temporal.'));
            die();
        }

        echo json_encode(array('mensaje' => 'success', 'pedido_id' => $id_pedido));
    } else {
        // Crear nuevo pedido
        $turnoConsulta = mysqli_query($conexion, "SELECT id_turno, id_control_caja FROM control_caja WHERE fecha_termino IS NULL LIMIT 1");
        $turnoData = mysqli_fetch_array($turnoConsulta);
        $id_turno = $turnoData['id_turno'];
        $id_control_caja = $turnoData['id_control_caja'];

        if ($id_sala == 0) {
            // Si id_sala es 0, insertar con id_garzon = 0 y Color_Garzon = 0
            $id_garzon = 0;
            $color_garzon = 0;
            $mesa = 1;  // Aseguramos que la mesa sea 1 en este caso

            // Crear el pedido para sala = 0
            $crearPedido = mysqli_query($conexion, "INSERT INTO pedidos 
            (id_sala, num_mesa, estado, observacion, id_usuario, id_turno, id_control_caja, id_garzon, Color_Garzon, total, cantidad_personas) 
            VALUES 
            ($id_sala, $mesa, 'PENDIENTE', '$observacion', $id_user, $id_turno, $id_control_caja, $id_garzon, '$color_garzon', '0', $cantidad_personas)");

        
            if (!$crearPedido) {
                echo json_encode(array('mensaje' => 'error', 'error' => 'Error al crear el nuevo pedido (sala = 0).'));
                die();
            }
        } else {
            // Si id_sala no es 0, obtener turno y control de caja
            $turnoConsulta = mysqli_query($conexion, "SELECT id_turno, id_control_caja FROM control_caja WHERE fecha_termino IS NULL LIMIT 1");
            $turnoData = mysqli_fetch_array($turnoConsulta);
            $id_turno = $turnoData['id_turno'];
            $id_control_caja = $turnoData['id_control_caja'];
        
            // Crear el pedido para sala != 0
            $crearPedido = mysqli_query($conexion, "INSERT INTO pedidos 
            (id_sala, num_mesa, estado, observacion, id_usuario, id_turno, id_control_caja, id_garzon, Color_Garzon, total, cantidad_personas) 
            VALUES 
            ($id_sala, $mesa, 'PENDIENTE', '$observacion', $id_user, $id_turno, $id_control_caja, $id_garzon, '$color_garzon',' 0', $cantidad_personas)");

        
            if (!$crearPedido) {
                echo json_encode(array('mensaje' => 'error', 'error' => 'Error al crear el nuevo pedido (sala != 0).'));
                die();
            }
        }

        $id_pedido = mysqli_insert_id($conexion);

        // Insertar productos del pedido en detalle_pedidos y calcular el total
        //$consulta = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
		$consulta = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos2 d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user AND d.id_sala=$id_sala AND d.id_mesa=$mesa");
        if (!$consulta) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al obtener los detalles del pedido temporal.'));
            die();
        }

		
        $totalPedido = 0;
        while ($row = mysqli_fetch_assoc($consulta)) {
            $nombre = $row['nombre'];
            $cantidad = $row['cantidad'];
            $precio = $row['precio'];
            $id_producto = $row['id_producto'];

            $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_pedidos (id_producto, nombre, precio, cantidad, id_pedido) VALUES ($id_producto, '$nombre', '$precio', $cantidad, $id_pedido)");

            if (!$insertarDet) {
                echo json_encode(array('mensaje' => 'error', 'error' => 'Error al insertar en detalle_pedidos.'));
                die();
            }

            $totalPedido += $cantidad * $precio;
        }

        // Actualizar el total del pedido
        $actualizarPedido = mysqli_query($conexion, "UPDATE pedidos SET total = '$totalPedido' WHERE id = $id_pedido");
		

        if (!$actualizarPedido) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al actualizar el total en la tabla pedidos.'));
            die();
        }

        // Eliminar productos temporales
        $eliminarTemporal = mysqli_query($conexion, "DELETE FROM temp_pedidos WHERE id_usuario = $id_user");
		
		$eliminarTemporal2 = mysqli_query($conexion, "DELETE  FROM temp_pedidos2 WHERE id_usuario = $id_user AND id_sala=$id_sala AND id_mesa=$mesa");

        if (!$eliminarTemporal) {
            echo json_encode(array('mensaje' => 'error', 'error' => 'Error al eliminar los productos del pedido temporal.'));
            die();
        }

        echo json_encode(array('mensaje' => 'success', 'pedido_id' => $id_pedido));
    }
}



else if (isset($_GET['editarUsuario'])) {
    $idusuario = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM usuario WHERE idusuario = $idusuario");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarProducto'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM platos WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarSalon'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;	
}else if (isset($_GET['finalizarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
	$tipo_documento = $_GET['tipo_documento']; 
	$folio = $_GET['folio']; 
	$tipo_pago = $_GET['tipo_pago']; 
	$monto_pago = $_GET['monto_pago']; 
	$total_pago = $_GET['total_pago']; 
	
	$tipop = mysqli_query($conexion, "SELECT descripcion FROM forma_pago WHERE id_forma_pago=$tipo_pago");
	$datap = mysqli_fetch_array($tipop);
	$formapago = $datap['descripcion'];
	
	// Eliminamos símbolos y comas de $total_pago
	$total_pago = str_replace(",", '', $total_pago);
	$total_pago = str_replace("$", '', $total_pago);
	
	// Obtenemos id de control de caja
	$rescaja = mysqli_query($conexion, "SELECT id_control_caja FROM control_caja WHERE id_usuario=$id_user and fecha_termino IS NULL");
    $data = mysqli_fetch_array($rescaja);
	$id_caja = $data['id_control_caja'];
	
	// Obtenemos id del pedido
	$numpedido = mysqli_query($conexion, "SELECT id FROM pedidos WHERE id_sala=$id_sala AND num_mesa=$mesa AND estado='PENDIENTE' AND id_usuario=$id_user");
    $data = mysqli_fetch_array($numpedido);
	$id_pedido = $data['id'];
	
	// Actualizamos el estado del pedido a 'FINALIZADO'
	$insertar = mysqli_query($conexion, "UPDATE pedidos SET estado='FINALIZADO' WHERE id_sala=$id_sala AND num_mesa=$mesa AND estado='PENDIENTE' AND id_usuario=$id_user");
    if ($insertar) {
		// Insertamos en la tabla pagos usando monto_pago
		$querypago = "INSERT INTO pagos (id_pedido, id_control_caja, id_forma_pago, tipo_documento, numero_documento, monto_pago) VALUES 
		('$id_pedido','$id_caja','$tipo_pago','$tipo_documento','$folio','$monto_pago')";
		$insertarpago = mysqli_query($conexion, $querypago);
		
		// Actualizamos correlativo del tipo de documento
		$querydoc = "UPDATE tipo_documento SET nro_correlativo=nro_correlativo + 1 WHERE tipo_documento = $tipo_documento";
		$insertadoc = mysqli_query($conexion, $querydoc);
		
		// Guardamos detalles del pedido en otra tabla
		$paso=0;
		$sqlw = "SELECT d.id_pedido, d.id_producto, p.nombre, d.cantidad, e.observacion,i.id_impresora, p.precio, e.id_garzon FROM detalle_pedidos d 
		INNER JOIN platos p ON d.id_producto = p.id
		INNER JOIN pedidos e ON d.id_pedido = e.id
        INNER JOIN impresoras i ON i.id_impresora=p.id_impresora
		WHERE e.id_usuario = $id_user AND e.id = $id_pedido";
		
		$consultaw = mysqli_query($conexion, $sqlw);
        while ($datow = mysqli_fetch_assoc($consultaw)) {
            $nombre = $datow['nombre'];
            $cantidad = $datow['cantidad'];
			$id_producto = $datow['id_producto'];
            $observacion = $datow['observacion'];
			$precio = $datow['precio'];
            $id_impresora = $datow['id_impresora'];
			$id_garzon= $datow['id_garzon'];
			$garzon="";
			if ($id_garzon==0){
				$garzon="VENTA DIRECTA";
			}
			else{
				$garzonx = mysqli_query($conexion, "SELECT nombre FROM garzon WHERE id_garzones=$id_garzon");
				$datagarzon = mysqli_fetch_array($garzonx);
				$garzon = $datagarzon['nombre'];
			}
			$sala="";
			if ($id_sala==0){
				$sala="VENTA DIRECTA";
			}
			else{
				$salax = mysqli_query($conexion, "SELECT nombre FROM salas WHERE id=$id_sala");
				$datasala = mysqli_fetch_array($salax);
				$sala = $datagarzon['nombre'];
			}
			
			if ($paso==0){
				$sql_encabezado="INSERT INTO encabezado_pedido (id_pedido,id_empresa,tipo_documento,numero_documento,tipo_pago, total, monto_pago, garzon, salon, mesa) VALUES ($id_pedido, $id_empresa,$tipo_documento,$folio,'$formapago',$total_pago,$monto_pago, '$garzon','$sala', $mesa)";
				$insertaweb = mysqli_query($conexionw, $sql_encabezado);
				$paso=1;
			}
			//$sql = "INSERT INTO detalle_pedido (id_pedido, codigo_producto, descripcion, cantidad, observacion, estado) VALUES ($id_pedido, $id_producto, '$nombre', $cantidad, '$observacion', 0)";
			$sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_empresa, id_impresora, codigo_producto, descripcion, cantidad, observacion, precio, estado, salon, mesa, garzon) VALUES ($id_pedido, $id_empresa, $id_impresora, $id_producto, '$nombre', $cantidad, '$observacion', $precio, 0, '$sala', $mesa, '$garzon')";
            $insertaweb = mysqli_query($conexionw, $sql_detalle);
		}
		$msg = array('mensaje' => '0');
    } else {
        $msg = array('mensaje' => 'error');
    }

    echo json_encode($msg);
    die();
}




if (isset($_POST['regDetalle'])) {
    $id_producto = $_POST['id'];
    $id_user = $_SESSION['idUser'];
	$id_sala = $_POST['id_sala'];
	$mesa = $_POST['id_mesa'];
    $consulta = mysqli_query($conexion, "SELECT * FROM temp_pedidos WHERE id_producto = $id_producto AND id_usuario = $id_user");
    $row = mysqli_fetch_assoc($consulta);
    if (empty($row)) {
        $producto = mysqli_query($conexion, "SELECT * FROM platos WHERE id = $id_producto");
        $result = mysqli_fetch_assoc($producto);
        $precio = $result['precio'];
        $query = mysqli_query($conexion, "INSERT INTO temp_pedidos (cantidad, precio, id_producto, id_usuario, id_sala, id_mesa) VALUES (1, $precio, $id_producto, $id_user, $id_sala, $mesa)");
		$query2 = mysqli_query($conexion, "INSERT INTO temp_pedidos2 (cantidad, precio, id_producto, id_usuario, id_sala, id_mesa) VALUES (1, $precio, $id_producto, $id_user, $id_sala, $mesa)");
    } else {
        $nueva = $row['cantidad'] + 1;
        $query = mysqli_query($conexion, "UPDATE temp_pedidos SET cantidad = $nueva WHERE id_producto = $id_producto AND id_usuario = $id_user");
		$query2 = mysqli_query($conexion, "UPDATE temp_pedidos2 SET cantidad = $nueva WHERE id_producto = $id_producto AND id_usuario = $id_user");
    }
    if ($query) {
        $msg = "registrado";
    } else {
        $msg = "Error al ingresar";
    }
    echo json_encode($msg);
    die();
}


// Conectar a la base de datos usando mysqli
$conexion = new mysqli('localhost', 'root', 'admin1025', 'resto'); // Cambia los valores según tu configuración

// Verificar la conexión
if ($conexion->connect_error) {
    die('No se pudo conectar: ' . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos enviados desde el formulario, asegurándose de escapar los valores
    $idUsuario = isset($_POST['id_usuario_value']) ? $conexion->real_escape_string($_POST['id_usuario_value']) : 0;
    $idCaja = isset($_POST['id_caja']) ? $conexion->real_escape_string($_POST['id_caja']) : 0;
    $idTurno = isset($_POST['id_turno']) ? $conexion->real_escape_string($_POST['id_turno']) : 0;
    $monto = isset($_POST['monto']) ? $conexion->real_escape_string($_POST['monto']) : 0;
    $descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string($_POST['descripcion']) : '';
    $fecha = isset($_POST['fecha']) ? $conexion->real_escape_string($_POST['fecha']) : '';
    $tipoMovimiento = isset($_POST['tipo_movimiento']) ? $conexion->real_escape_string($_POST['tipo_movimiento']) : 0;
    $idControlCaja = isset($_POST['id_control_caja']) ? $conexion->real_escape_string($_POST['id_control_caja']) : 0;

    // Verificar que el ID del usuario sea válido
    if ($idUsuario == 0) {
        echo 'Error: El ID del usuario no es válido.';
        exit; // Termina el script
    }

    // Realizar la consulta para insertar el movimiento en la tabla
    $queryInsertar = "INSERT INTO movimientos_caja (id_usuario, id_caja, id_turno, id_control_caja, monto, descripcion, fecha, tipo_movimiento)
                      VALUES ('$idUsuario', '$idCaja', '$idTurno', '$idControlCaja', '$monto', '$descripcion', '$fecha', '$tipoMovimiento')";

    // Ejecutar la consulta
    if ($conexion->query($queryInsertar)) {
        // Redirigir según el tipo de movimiento
        if ($tipoMovimiento == 1) {
            header('Location: ingresos.php'); // Redirige a ingresos.php
        } else {
            header('Location: egresos.php'); // Redirige a egresos.php
        }
        exit; // Termina el script después de redirigir
    } else {
        // Retornar un mensaje de error
        echo 'Error al registrar el movimiento: ' . $conexion->error;
    }
}



// Cerrar la conexión al final
$conexion->close();


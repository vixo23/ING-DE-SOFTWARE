<?php
require_once "../conexion.php";
session_start();
if (isset($_GET['detalle'])) {
    $id = $_SESSION['idUser'];
	$id_sala = $_GET['id_sala'];
	$mesa = $_GET['mesa'];
	$sql="(SELECT d.*, p.nombre, p.precio, p.imagen FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id)";
    $sql=$sql." UNION ALL (SELECT d.id, d.cantidad, d.precio,d.id_producto, e.id_usuario, e.id_sala, e.num_mesa, p.nombre, p.precio, p.imagen
	FROM detalle_pedidos d INNER JOIN pedidos e ON d.id=e.id 
	INNER JOIN platos p ON d.id_producto = p.id WHERE e.estado ='PENDIENTE' AND e.id_usuario = $id
	AND e.id_sala=$id_sala AND e.num_mesa=$mesa)";
	$datos = array();
    $detalle = mysqli_query($conexion, $sql);
    while ($row = mysqli_fetch_assoc($detalle)) {
        $data['id'] = $row['id'];
        $data['nombre'] = $row['nombre'];
        $data['cantidad'] = $row['cantidad'];
        $data['precio'] = $row['precio'];
        $data['imagen'] = ($row['imagen'] == null) ? '../assets/img/default.png' : $row['imagen'];
        $data['total'] = $data['precio'] * $data['cantidad'];
        array_push($datos, $data);
    }
	
    echo json_encode($datos);
	//echo $sql;
    die();
} else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];
    $query = mysqli_query($conexion, "DELETE FROM temp_pedidos WHERE id = $id_detalle");
    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
} else if (isset($_GET['detalle_cantidad'])) {
    $id_detalle = $_GET['id'];
    $cantidad = $_GET['cantidad'];
    $query = mysqli_query($conexion, "UPDATE temp_pedidos set cantidad = $cantidad WHERE id = $id_detalle");
    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
} else if (isset($_GET['procesarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $observacion = $_GET['observacion'];

    // Obtener el total de los pedidos temporales
    $consulta = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
    $total = 0;
    while ($row = mysqli_fetch_assoc($consulta)) {
        $total += $row['cantidad'] * $row['precio'];
    }

    // Obtener el id_turno de la tabla control_caja
    $turnoConsulta = mysqli_query($conexion, "SELECT id_turno,id_control_caja FROM control_caja WHERE fecha_termino IS NULL LIMIT 1");
    $turnoData = mysqli_fetch_array($turnoConsulta);
    $id_turno = $turnoData['id_turno'];
    $id_control_caja = $turnoData['id_control_caja'];
    // Insertar el pedido incluyendo id_turno
    $insertar = mysqli_query($conexion, "INSERT INTO pedidos (id_sala, num_mesa, total, observacion, id_usuario, id_turno,id_control_caja) VALUES ($id_sala, $mesa, '$total', '$observacion', $id_user, $id_turno,$id_control_caja)");
    $id_pedido = mysqli_insert_id($conexion);
    
    if ($insertar) {
        $consulta = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
        while ($dato = mysqli_fetch_assoc($consulta)) {
            $nombre = $dato['nombre'];
            $cantidad = $dato['cantidad'];
            $precio = $dato['precio'];
            $id_producto = $dato['id_producto'];
            $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_pedidos (id_producto, nombre, precio, cantidad, id_pedido) VALUES ($id_producto,'$nombre', '$precio', $cantidad, $id_pedido)");
        }

        if ($insertarDet > 0) {        
            $sql = "DELETE FROM temp_pedidos WHERE id_usuario = $id_user";
            $eliminar = mysqli_query($conexion, $sql);
            if ($id_sala != 0) {
                $sql = "SELECT * FROM salas WHERE id = $id_sala";
                $sala = mysqli_query($conexion, $sql);
                $resultSala = mysqli_fetch_assoc($sala);
                $msg = array('mensaje' => $resultSala['mesas']);
            } else {
                $msg = array('mensaje' => 0);
            }
        }
    } else {
        $msg = array('mensaje' => 'error');
    }

    echo json_encode($msg);
    die();
} else if (isset($_GET['editarUsuario'])) {
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
	
	$total_pago=str_replace(",", '', $total_pago);
	$total_pago=str_replace("$", '', $total_pago);
	
	$rescaja = mysqli_query($conexion, "SELECT id_control_caja FROM control_caja WHERE id_usuario=$id_user and fecha_termino IS NULL");
    $data = mysqli_fetch_array($rescaja);
	$id_caja=$data['id_control_caja'];
	
	$numpedido = mysqli_query($conexion, "SELECT id FROM pedidos WHERE id_sala=$id_sala AND num_mesa=$mesa AND estado='PENDIENTE' AND id_usuario=$id_user");
    $data = mysqli_fetch_array($numpedido);
	$id_pedido=$data['id'];
	
	$insertar = mysqli_query($conexion, "UPDATE pedidos SET estado='FINALIZADO' WHERE id_sala=$id_sala AND num_mesa=$mesa AND estado='PENDIENTE' AND id_usuario=$id_user");
    if ($insertar) {
		$querypago="INSERT INTO pagos (id_pedido, id_control_caja, id_forma_pago, tipo_documento, numero_documento, monto_pago) VALUES ('$id_pedido','$id_caja','$tipo_pago','$tipo_documento','$folio','$total_pago')";
		$insertarpago = mysqli_query($conexion, $querypago);
		
		$querydoc="UPDATE tipo_documento SET nro_correlativo=nro_correlativo +1 WHERE tipo_documento=$tipo_documento";
		$insertadoc = mysqli_query($conexion, $querydoc);
		
		$sqlw="SELECT d.id_pedido,d.id_producto, p.nombre,d.cantidad, e.observacion FROM detalle_pedidos d 
		INNER JOIN platos p ON d.id_producto = p.id
		INNER JOIN pedidos e ON d.id_pedido=e.id
		WHERE e.id_usuario=$id_user and e.id=$id_pedido";
		$consultaw = mysqli_query($conexion, $sqlw);
        while ($datow = mysqli_fetch_assoc($consultaw)) {
            $nombre = $datow['nombre'];
            $cantidad = $datow['cantidad'];
            //$precio = $datow['precio'];
			$id_producto = $datow['id_producto'];
            $observacion= $datow['observacion'];
			$sql="INSERT INTO detalle_pedido (id_pedido, codigo_producto, descripcion, cantidad, observacion, estado) VALUES ($id_pedido,$id_producto,'$nombre', $cantidad,'$observacion',0)";
			$insertaweb= mysqli_query($conexionw, $sql);
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
    } else {
        $nueva = $row['cantidad'] + 1;
        $query = mysqli_query($conexion, "UPDATE temp_pedidos SET cantidad = $nueva WHERE id_producto = $id_producto AND id_usuario = $id_user");
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


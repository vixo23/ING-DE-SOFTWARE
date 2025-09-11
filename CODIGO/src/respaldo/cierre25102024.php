<?php
ob_start(); // Iniciar almacenamiento en búfer de salida
session_start();
include_once "includes/header.php";
include "../conexion.php";

$conn = mysql_connect('localhost', 'root', 'admin1025'); // Cambia 'usuario' y 'contraseña' por tus credenciales
    if (!$conn) {
        die('No se pudo conectar: ' . mysql_error());
    }
    mysql_select_db('resto', $conn); // Cambia 'nombre_base_datos' por el nombre de tu base de datos
    mysql_query("SET NAMES 'utf8'", $conn);
    mysql_query("SET CHARACTER SET 'utf8'", $conn);
    mysql_query("SET CHARACTER_SET_CONNECTION='utf8'", $conn);


$idUser = $_SESSION['idUser'];

// Verificar si hay una caja activa
$queryCajaActiva = $conexion->prepare("SELECT id_control_caja FROM control_caja WHERE monto_cierre IS NULL AND fecha_termino IS NULL AND id_usuario = ?");
$queryCajaActiva->bind_param("i", $idUser);
$queryCajaActiva->execute();
$queryCajaActiva->store_result();

if ($queryCajaActiva->num_rows == 0) {
    // Si no hay caja activa, mostrar SweetAlert
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js'></script>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'No hay caja activa',
            text: 'No hay caja activa para cerrar.',
            confirmButtonText: 'OK',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'apertura.php'; // Redireccionar si no hay caja activa
            }
        });
    </script>";
    include_once "includes/footer.php";
    exit; 
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['monto_cierre']) && isset($_POST['id_control_caja'])) {
    $monto_cierre = $_POST['monto_cierre'];
    $ajuste = isset($_POST['ajuste']) ? $_POST['ajuste'] : 0; // Obtener ajuste
    $id_control_caja = $_POST['id_control_caja'];

    // Obtener la fecha de término, inicializar a null si no está establecida
    $fecha_termino = isset($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;

    // Escapar variables para prevenir inyección SQL
    $monto_cierre = $conexion->real_escape_string($monto_cierre);
    $ajuste = $conexion->real_escape_string($ajuste);
    $id_control_caja = $conexion->real_escape_string($id_control_caja);
    $fecha_termino = $conexion->real_escape_string($fecha_termino); // Esto escapará NULL o cadena vacía

    // Actualizar el cierre de caja
    $queryUpdate = "UPDATE control_caja SET monto_cierre = '$monto_cierre', ajuste = '$ajuste', fecha_termino = " . ($fecha_termino ? "'$fecha_termino'" : "NULL") . " WHERE id_control_caja = '$id_control_caja'";

    if ($conexion->query($queryUpdate) === TRUE) {
        // Código para SweetAlert 2
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
              <script>
                console.log('Actualización exitosa'); // Agregado para depuración
                Swal.fire({
                    title: 'Éxito',
                    text: 'Se cerró la caja correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = 'apertura.php'; // Redirigir después de cerrar el alert
                });
              </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
              <script>
                console.log('Error en la actualización: " . $conexion->error . "'); // Agregado para depuración
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al actualizar la caja.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
              </script>";
    }
}


// Consulta para obtener datos de control_caja
$query = $conexion->prepare("SELECT 
                                cc.fecha_inicio, 
                                u.nombre AS nombre_usuario, 
                                c.descripcion AS nombre_caja, 
                                t.descripcion AS nombre_turno,
                                cc.id_control_caja
                              FROM control_caja cc
                              INNER JOIN usuarios u ON cc.id_usuario = u.id
                              INNER JOIN cajas c ON cc.id_caja = c.id_caja
                              INNER JOIN turnos t ON cc.id_turno = t.id_turno
                              WHERE cc.id_usuario = ? AND cc.fecha_termino IS NULL");

$query->bind_param("i", $idUser);
$query->execute();
$query->bind_result($fecha_inicio, $nombre_usuario, $nombre_caja, $nombre_turno, $id_control_caja);


if ($query->fetch()) {
    $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio));
    $query->close();

    // Sumar montos de ingresos y egresos de la caja activa
    $queryMontoIngresos = $conexion->prepare("SELECT SUM(mc.monto) FROM movimientos_caja mc WHERE mc.id_usuario = ? AND mc.tipo_movimiento = 1 AND mc.id_control_caja = ?");
    $queryMontoIngresos->bind_param("ii", $idUser, $id_control_caja);
    $queryMontoIngresos->execute();
    $queryMontoIngresos->bind_result($totalMontoIngresos);
    $queryMontoIngresos->fetch();
    $totalMontoIngresos = $totalMontoIngresos ? $totalMontoIngresos : 0;
    $queryMontoIngresos->close();

    $queryMontoEgresos = $conexion->prepare("SELECT SUM(mc.monto) FROM movimientos_caja mc WHERE mc.id_usuario = ? AND mc.tipo_movimiento = 0 AND mc.id_control_caja = ?");
    $queryMontoEgresos->bind_param("ii", $idUser, $id_control_caja);
    $queryMontoEgresos->execute();
    $queryMontoEgresos->bind_result($totalMontoEgresos);
    $queryMontoEgresos->fetch();
    $totalMontoEgresos = $totalMontoEgresos ? $totalMontoEgresos : 0;
    $queryMontoEgresos->close();

    //$totalFinal = $totalMontoIngresos - $totalMontoEgresos;

    // Consulta para obtener todos los montos por forma de pago
    $queryMontoPagos = $conexion->prepare("
        SELECT SUM(pagos.monto_pago) as monto_pago, forma_pago.descripcion 
        FROM pagos 
        INNER JOIN forma_pago ON pagos.id_forma_pago = forma_pago.id_forma_pago 
        WHERE pagos.id_control_caja = ? 
        GROUP BY forma_pago.id_forma_pago
    ");
    $queryMontoPagos->bind_param("i", $id_control_caja);
    $queryMontoPagos->execute();

    // Variables para almacenar los resultados
    $queryMontoPagos->bind_result($monto_pago, $descripcion);

    // Inicializar un array para almacenar los montos de cada forma de pago
    $montosPagos = array(); // Cambiado a array()

    // Fetch de los resultados
    while ($queryMontoPagos->fetch()) {
        $montosPagos[$descripcion] = $monto_pago ? $monto_pago : 0;
    }
    $queryMontoPagos->close();

    // Acceder a los montos por forma de pago
    foreach ($montosPagos as $descripcion => $monto) {
       // echo "Total por $descripcion: $monto<br>";
    }


    // Consulta para obtener el monto de apertura
    $queryMontoApertura = $conexion->prepare("SELECT monto_apertura FROM control_caja WHERE id_control_caja = ?");
    $queryMontoApertura->bind_param("i", $id_control_caja);
    $queryMontoApertura->execute();
    $queryMontoApertura->bind_result($montoApertura);
    $queryMontoApertura->fetch();
    $montoApertura = $montoApertura ? $montoApertura : 0; // Manejo de NULL
    $queryMontoApertura->close();

// Sumar todos los montos de ingresos, el monto de apertura, y los montos de cada forma de pago
$sumaTotalIngresos = $totalMontoIngresos + $montoApertura; // Sumar ingresos y apertura

// Agregar montos de cada forma de pago
foreach ($montosPagos as $monto) {
    $sumaTotalIngresos += $monto; // Sumar cada monto de pago
}


}

$queryMontoIngresosPAGINA = $conexion->prepare("SELECT SUM(mc.monto) FROM movimientos_caja mc WHERE mc.id_usuario = ? AND mc.tipo_movimiento = 1 AND mc.id_control_caja = ?");
    $queryMontoIngresosPAGINA->bind_param("ii", $idUser, $id_control_caja);
    $queryMontoIngresosPAGINA->execute();
    $queryMontoIngresosPAGINA->bind_result($totalMontoIngresosPAGINA);
    $queryMontoIngresosPAGINA->fetch();
    $totalMontoIngresosPAGINA = $totalMontoIngresosPAGINA ? $totalMontoIngresosPAGINA : 0;
    $queryMontoIngresosPAGINA->close();

    $host = 'localhost'; // Cambia esto si es necesario
$username = 'root'; // Cambia esto por tu usuario
$password = 'admin1025'; // Cambia esto por tu contraseña
$database = 'resto'; // Cambia esto por tu base de datos

// Conectar
$conn = mysql_connect($host, $username, $password);
if (!$conn) {
    die('No se pudo conectar: ' . mysql_error());
}

// Seleccionar la base de datos
$db_selected = mysql_select_db($database, $conn);
if (!$db_selected) {
    die ('No se puede usar ' . $database . ': ' . mysql_error());
}



$query = "SELECT fecha_inicio FROM control_caja WHERE id_control_caja =$id_control_caja"; // Asegúrate de que $id sea seguro
$result = mysql_query($query, $conn);

if (!$result) {
    die('Consulta fallida: ' . mysql_error()); // Muestra el error de la consulta
}

// Procesar el resultado
if ($row = mysql_fetch_assoc($result)) {
    $fecha_inicio = $row['fecha_inicio'];
} else {
    $fecha_inicio = null; // Maneja el caso donde no se encuentra ningún resultado
}



$conexion->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <script>
        function imprimirPagina() {
            var montoCierre = document.getElementById("monto_cierre").value;

            // Verificar si el campo montoCierre está vacío
            if (!montoCierre) {
                // Mostrar SweetAlert solo si el monto de cierre está vacío
                Swal.fire({
                    title: "Error",
                    text: "No se imprimirá hasta que ingrese el monto de cierre",
                    icon: "warning"
                });
            } else {
                // Si montoCierre tiene valor, ejecutar la impresión
                window.print(); // Se ejecuta si el campo montoCierre está lleno
            }
        }


    </script>

    <style>

        /* Estilos para centrar el formulario y reducir su tamaño */
.formulario-control-caja {
    background-color: white;
    padding: 10px; 
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%; 
    margin-bottom: 10px; 
    box-sizing: border-box;
}

h2 {
    text-align: center;
    margin-bottom: 10px; 
    color: #000;
}

.formulario-control-caja label {
    display: block;
    margin-bottom: 4px; 
    font-weight: bold;
    color: #000;
}

.formulario-control-caja input[type="text"], 
.formulario-control-caja input[type="date"],
.formulario-control-caja input[type="number"] {
    width: 100%;
    padding: 4px; 
    margin-bottom: 8px; 
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    background-color: #f9f9f9;
}

.formulario-control-caja input[type="submit"], .btn-secondary {
    width: 100%;
    padding: 6px; 
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.formulario-control-caja input[type="submit"] {
    background-color: #4CAF50;
    color: white;
}

.formulario-control-caja input[type="submit"]:hover {
    background-color: #45a049;
}

.container-forms {
    display: flex;
    justify-content: space-between;
    margin: 10px 0; 
}

.formulario-ingresos, .formulario-egresos {
    width: 190%; 
    padding: 10px; 
}

/* Distribución de los elementos en filas de dos columnas iguales */
.input-group {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.input-item {
    flex: 1 1 calc(50% - 10px); /* Dos columnas del 50% */
}

/* Estilos para los resultados de ingresos y egresos */
.resultado-ingresos {
    background-color: #e0ffe0; 
    border: 1px solid #4CAF50; 
    border-radius: 5px;
    padding: 10px;
    margin-top: 15px;
    text-align: center;
    width: 49%;
}

.resultado-ingresosNega {
    background-color: #ffe0e0; 
    border: 1px solid #4CAF50; 
    border-radius: 5px;
    padding: 10px;
    margin-top: 15px;
    text-align: center;
    width: 49%;
}

.resultado-ingresos h4 {
    margin: 0; 
    color: #4CAF50; 
}

.total-ingresos {
    font-weight: bold; 
    font-size: 1.2em; 
}


    </style>

</head>
<body>
<!-- CUERPO PAGINA -->
<div class="container">
    <div class="card formulario-control-caja">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off" id="formulario">
                <div class="row">
                    <!-- Campo Fecha de Apertura -->
                    <div class="col-md-3">
                    
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha y Hora de Apertura</label>
                        <?php
                        // Verifica el valor de $fecha_inicio
                        //var_dump($fecha_inicio);
                        ?>
                        <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                            value="<?php echo isset($fecha_inicio) ? date('Y-m-d\TH:i', strtotime($fecha_inicio)) : ''; ?>" readonly>
                    </div>

                    </div>
                    <!-- Campo Nombre del Administrador -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_usuario">Nombre del Usuario</label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" 
                                   value="<?php echo isset($nombre_usuario) ? $nombre_usuario : ''; ?>" readonly>
                        </div>
                    </div>

                    <!-- Campo Nombre de la Caja -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_caja">Caja:</label>
                            <input type="text" class="form-control" id="nombre_caja" name="nombre_caja" 
                                   value="<?php echo isset($nombre_caja) ? $nombre_caja : ''; ?>" readonly>
                        </div>
                    </div>

                    <!-- Campo Nombre del Turno -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_turno">Turno:</label>
                            <input type="text" class="form-control" id="nombre_turno" name="nombre_turno" 
                                   value="<?php echo isset($nombre_turno) ? $nombre_turno : ''; ?>" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div> 
<!-- Contenedor para Ingresos y Egresos -->

<?php
// Inicializamos con el monto de apertura
$totalMontoIngresos = $montoApertura+$totalMontoIngresosPAGINA; 

// Sumamos cada monto de los pagos
foreach ($montosPagos as $monto) {
    $totalMontoIngresos += $monto;
}

// Formateamos el total de ingresos
$totalMontoIngresosFormatted = number_format($totalMontoIngresos, 0, ',', '.');


?>



<div class="container-forms">
    <div class="formulario-ingresos formulario-control-caja">
        <form action="procesar_ingresos.php" method="POST">
            <div class="input-group" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                <div class="input-item" style="width: 48%;">
                    <label for="monto_apertura">Monto de Apertura:</label>
                    <input type="text" id="monto_apertura" name="monto_apertura" value="$ <?php echo number_format($montoApertura, 0, ',', '.'); ?>" readonly>
                </div>
                <div class="input-item" style="width: 50%;">
                    <label for="monto_ingreso">Total de ingresos:</label>
                    <input type="text" id="monto_ingreso" name="monto_ingreso" value="$ <?php echo number_format($totalMontoIngresosPAGINA, 0, ',', '.'); ?>" readonly>
                </div>
            </div>
            <hr>

            <!-- Mostrar cada forma de pago -->
            <?php 
            $items = array(); // Usamos array()
            foreach ($montosPagos as $descripcion => $monto) {
                if ($monto != 0) {
                    $items[] = array('descripcion' => $descripcion, 'monto' => number_format($monto, 0, ',', '.'));
                }
            }

            // Asegúrate de que el número de elementos sea múltiplo de 3
            while (count($items) % 3 != 0) {
                $items[] = array('descripcion' => '', 'monto' => ''); // Elemento vacío
            }

            // Agrupar en tercios para mostrar tres por línea
            for ($i = 0; $i < count($items); $i += 3): ?>
                <div class="input-group" style="display: flex; justify-content: flex-start; flex-wrap: wrap; gap: 20px;">
                    <!-- Primer ítem -->
                    <div class="input-item" style="flex: 1 1 calc(33% - 10px);">
                        <?php if (!empty($items[$i]['descripcion'])): ?>
                            <label for="<?php echo strtolower(str_replace(' ', '_', $items[$i]['descripcion'])); ?>">
                                <?php echo $items[$i]['descripcion']; ?>
                            </label>
                            <input type="text" name="<?php echo strtolower(str_replace(' ', '_', $items[$i]['descripcion'])); ?>" 
                                value="$ <?php echo $items[$i]['monto']; ?>" readonly>
                        <?php else: ?>
                            <label>&nbsp;</label> <!-- Espacio para mantener la estructura -->
                            <input type="text" name="empty_1" value="" readonly style="background-color: transparent; border: none;">
                        <?php endif; ?>
                    </div>

                    <!-- Segundo ítem (solo si existe) -->
                    <?php if (isset($items[$i + 1])): ?>
                        <div class="input-item" style="flex: 1 1 calc(33% - 10px);">
                            <?php if (!empty($items[$i + 1]['descripcion'])): ?>
                                <label for="<?php echo strtolower(str_replace(' ', '_', $items[$i + 1]['descripcion'])); ?>">
                                    <?php echo $items[$i + 1]['descripcion']; ?>
                                </label>
                                <input type="text" name="<?php echo strtolower(str_replace(' ', '_', $items[$i + 1]['descripcion'])); ?>" 
                                    value="$ <?php echo $items[$i + 1]['monto']; ?>" readonly>
                            <?php else: ?>
                                <label>&nbsp;</label>
                                <input type="text" name="empty_2" value="" readonly style="background-color: transparent; border: none;">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tercer ítem (solo si existe) -->
                    <?php if (isset($items[$i + 2])): ?>
                        <div class="input-item" style="flex: 1 1 calc(33% - 10px);">
                            <?php if (!empty($items[$i + 2]['descripcion'])): ?>
                                <label for="<?php echo strtolower(str_replace(' ', '_', $items[$i + 2]['descripcion'])); ?>">
                                    <?php echo $items[$i + 2]['descripcion']; ?>
                                </label>
                                <input type="text" name="<?php echo strtolower(str_replace(' ', '_', $items[$i + 2]['descripcion'])); ?>" 
                                    value="$ <?php echo $items[$i + 2]['monto']; ?>" readonly>
                            <?php else: ?>
                                <label>&nbsp;</label>
                                <input type="text" name="empty_3" value="" readonly style="background-color: transparent; border: none;">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>

            <style>
            .containerflex {
                display: flex; /* Alinea los elementos en una fila */
                gap: 20px; /* Espacio entre los elementos */
            }

            .resultado-ingresos {
                /* Puedes agregar más estilos aquí si lo deseas */
            }

            .input-group {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 20px;
            }

            .input-item {
                flex: 1 1 calc(33% - 10px);
            }

            /* Ajustes para cuando hay solo dos ítems */
            @media screen and (max-width: 600px) {
                .input-item {
                    flex: 1 1 calc(50% - 10px);
                }
            }

            /* Ajustes para un solo ítem */
            @media screen and (max-width: 400px) {
                .input-item {
                    flex: 1 1 100%;
                }
            }
            </style>

            <div class="containerflex">
                <div class="resultado-ingresos">
                    <label for="sumatotal">Total de ingresos</label>
                    <input type="text" id="total" value="$ <?php echo $totalMontoIngresosFormatted; ?>" readonly>
                </div>
                <div class="resultado-ingresosNega">
                    <label for="sumatotal">Total de egresos</label>
                    <input type="text" id="total" value="$- <?php echo $totalMontoEgresos; ?>" readonly>
                </div>
            </div>
        </form>
    </div>
</div>




    
</div>

<div class="container-forms">
    <div class="formulario-control-caja">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="formulario_cierre">
            <input type="hidden" name="id_control_caja" value="<?php echo $id_control_caja; ?>">

            <!-- Campo oculto para fecha_termino -->
            <input type="hidden" name="fecha_termino" value="<?php echo date('Y-m-d H:i:s'); ?>">

            <div class="row">
                <div class="col-md-6">
                <div class="form-group">
                        <label for="monto_cierre">Monto de Cierre:</label>
                        <input type="text" class="form-control" id="monto_cierre" name="monto_cierre" 
                            value="<?php echo isset($monto_cierre) ? $monto_cierre : ''; ?>" placeholder="Ingrese monto de cierre" oninput="calcularTotal()" required>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ajuste">Ajuste:</label>
                        <input type="text" class="form-control" id="ajuste" name="ajuste" 
                            value="<?php echo isset($ajuste) ? $ajuste : ''; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
    <div class="col-md-6">
        <input type="submit" value="Cerrar Caja" class="btn btn-primary btn-block">
    </div>
    <div class="col-md-6">
        <button type="button" class="btn btn-secondary btn-block" onclick="imprimirPagina()">Imprimir</button>
    </div>
</div>

<form method="post" action="tu_proceso.php">
    <div class="row mt-3">
        <div class="col-md-9">
            <label for="id_turno">Buscar Cierres</label>
            <select class="form-control" id="id_cierres" name="id_cierres">
                <option value="">Seleccione un cierre</option>

                <?php
                $query_cierres = "SELECT control_caja.*, 
                        cajas.descripcion AS caja, 
                        usuarios.nombre AS usuario, 
                        turnos.descripcion AS turno
                        FROM control_caja 
                        INNER JOIN cajas ON cajas.id_caja = control_caja.id_caja
                        INNER JOIN usuarios ON control_caja.id_usuario = usuarios.id
                        INNER JOIN turnos ON turnos.id_turno = control_caja.id_turno
                        WHERE fecha_termino IS NOT NULL 
                        ORDER BY id_control_caja DESC";

                $result_cierres = mysql_query($query_cierres, $conn);
                if ($result_cierres) {
                    while ($rowc = mysql_fetch_assoc($result_cierres)) {
                        $texto = $rowc['fecha_inicio'] . " | " . 
                                 $rowc['usuario'] . " | " . 
                                 $rowc['caja'] . " | " . 
                                 $rowc['turno'] . " | " . 
                                 $rowc['fecha_termino'] . " | " . 
                                 $rowc['monto_apertura'] . " | " . 
                                 $rowc['monto_cierre'] . " | " . 
                                 $rowc['ajuste'];
                                  
                        echo "<option value='" . $rowc['id_control_caja'] . "' 
                                data-fecha-inicio='" . $rowc['fecha_inicio'] . "' 
                                data-usuario='" . $rowc['usuario'] . "' 
                                data-caja='" . $rowc['caja'] . "' 
                                data-turno='" . $rowc['turno'] . "' 
                                data-monto-apertura='" . $rowc['monto_apertura'] . "' 
                                data-monto-cierre='" . $rowc['monto_cierre'] . "' 
                                data-ajuste='" . $rowc['ajuste'] . "'>" . 
                                $texto . 
                            "</option>";
                    }
                } else {
                    echo "<option value=''>Error al cargar cierres</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-primary btn-block" onclick="redirectToCierreHistorico()">Ver Cierre Histórico</button>
        </div>
    </div>
</form>

<script>
function redirectToCierreHistorico() {
    var select = document.getElementById('id_cierres');
    var selectedValue = select.value;

    if (selectedValue) {
        var url = 'cierre_Historico.php?id_cierre=' + selectedValue;
        window.location.href = url; // Redirigir a la nueva URL
    } else {
        alert('Por favor, seleccione un cierre para continuar.');
    }
}
</script>

        </form>
    </div>
        </div>
        </div></div>   
        
</div>




<style>
    @media print {
        /* Ocultar botones o elementos que no quieres imprimir */
        .btn, .no-print {
            display: none;
        }

        /* Ajustes generales para que todo se vea bien al imprimir */
        body {
            font-size: 12px;
            color: black;
        }

        /* Asegurarse de que el formulario ocupe todo el ancho */
        .container-forms {
            width: 100%;
        }

        /* Ajustar márgenes y otros estilos que se ven mejor en papel */
        .formulario-control-caja {
            padding: 0;
            margin: 0;
        }

        /* Otras clases que quieras ajustar */
        .form-group {
            margin-bottom: 10px;
        }
    }
    
</style>

<script>
// Función para calcular el ajuste restando el monto de cierre de los ingresos y egresos
function calcularTotal() {
    // Verificar que las variables PHP existan y tengan valores numéricos
    var totalIngresos = parseFloat(<?php echo isset($totalMontoIngresos) ? $totalMontoIngresos : 0; ?>); 
    var totalEgresos = parseFloat(<?php echo isset($totalMontoEgresos) ? $totalMontoEgresos : 0; ?>); 
    
    // Convertir el valor de monto_cierre a número
    var montoCierre = parseFloat(document.getElementById('monto_cierre').value.replace(/[^\d.-]/g, '')) || 0;

    // Verificar si los valores de ingreso y egreso son correctos (solo para depuración)
    console.log("Ingresos: " + totalIngresos);
    console.log("Egresos: " + totalEgresos);
    console.log("Monto Cierre: " + montoCierre);

    // Realizar la resta
    var Resta = totalIngresos - totalEgresos;

    // Calcular el ajuste
    var ajuste = montoCierre - Resta;

    // Mostrar el ajuste formateado, incluyendo negativos
    document.getElementById('ajuste').value = ajuste.toLocaleString('es-CL', { 
        minimumFractionDigits: 0, 
        maximumFractionDigits: 0 
    }); 
}
</script>




</body>
</html>


<?php
include "includes/footer.php"; // Incluir el pie de página
?>

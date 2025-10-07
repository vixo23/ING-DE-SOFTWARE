<?php
session_start();
include "includes/header.php";
include "../conexion.php";

if (isset($_GET['id_cierre'])) {
    // Obtener el ID del cierre desde la URL
    $id_cierre = $_GET['id_cierre'];

    // Conectar a la base de datos
    $conn = new mysqli('localhost', 'root', 'admin1025', 'resto'); // Cambia 'usuario' y 'contraseña' por tus credenciales
    if ($conn->connect_error) {
        die('No se pudo conectar: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');

    // Escapar el ID para evitar inyección SQL
    $id_cierre = $conn->real_escape_string($id_cierre);

    // Realizar una consulta para obtener los datos del cierre
    $query = "SELECT control_caja.*, cajas.descripcion AS caja, usuarios.nombreUsuario AS usuario, turnos.descripcion AS turno
              FROM control_caja
              INNER JOIN cajas ON cajas.id_caja = control_caja.id_caja
              INNER JOIN usuarios ON control_caja.id_usuario = usuarios.id
              INNER JOIN turnos ON turnos.id_turno = control_caja.id_turno
              WHERE id_control_caja = '$id_cierre'";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        // Obtener los datos del cierre
        $cierre = $result->fetch_assoc();
        
        // Definir las variables a utilizar en los campos
        $fecha_inicio2 = $cierre['fecha_inicio'];
        $nombre_usuario2 = $cierre['usuario'];
        $nombre_caja2 = $cierre['caja'];
        $nombre_turno2 = $cierre['turno'];
        $monto_cierre2 = $cierre['monto_cierre'];
        $monto_apertura2 = $cierre['monto_apertura']; // Obtener el monto_apertura
        $ajuste2 = $cierre['ajuste']; // Suponiendo que este campo existe en la tabla

        // Aquí puedes agregar la lógica para mostrar los datos en el HTML o procesarlos
    } else {
        echo "Error: No se encontró el cierre especificado.";
    }

    $result->free();
}

$queryMontoIngresosPAGINA = $conexion->prepare("
    SELECT SUM(mc.monto) 
    FROM movimientos_caja mc 
    WHERE mc.id_usuario = ? 
    AND mc.tipo_movimiento = 1 
    AND mc.id_control_caja = ?
");

$queryMontoIngresosPAGINA->bind_param("ii", $idUser, $id_cierre); // Cambia $id_control_caja por $id_cierre
$queryMontoIngresosPAGINA->execute();
$queryMontoIngresosPAGINA->bind_result($totalMontoIngresosPAGINA);
$queryMontoIngresosPAGINA->fetch();
$totalMontoIngresosPAGINA = $totalMontoIngresosPAGINA ? $totalMontoIngresosPAGINA : 0;
$queryMontoIngresosPAGINA->close();

$queryMontoEgresos = $conexion->prepare("
    SELECT SUM(mc.monto) 
    FROM movimientos_caja mc 
    WHERE mc.id_usuario = ? 
    AND mc.tipo_movimiento = 0 
    AND mc.id_control_caja = ?
");

$queryMontoPagos = $conexion->prepare("
    SELECT SUM(pagos.monto_pago) AS monto_pago, forma_pago.descripcion 
    FROM pagos 
    INNER JOIN forma_pago ON pagos.id_forma_pago = forma_pago.id_forma_pago 
    WHERE pagos.id_control_caja = ? 
    GROUP BY forma_pago.id_forma_pago
");
$queryMontoPagos->bind_param("i", $id_cierre); // Usar $id_cierre aquí
$queryMontoPagos->execute();

$queryMontoPagos->bind_result($monto_pago, $descripcion);

$montosPagos = array();
$totalMontoPagos = $totalMontoIngresosPAGINA + $monto_apertura2;

while ($queryMontoPagos->fetch()) {
    $montosPagos[$descripcion] = $monto_pago ? $monto_pago : 0;
    $totalMontoPagos += $montosPagos[$descripcion];
}
$queryMontoPagos->close();

foreach ($montosPagos as $descripcion => $monto) {
    //echo "Total por $descripcion: $monto<br>"; // Descomenta esta línea para mostrar los resultados
}

$queryMontoEgresos->bind_param("ii", $idUser, $id_cierre); // Cambia $id_control_caja por $id_cierre
$queryMontoEgresos->execute();
$queryMontoEgresos->bind_result($totalMontoEgresos);
$queryMontoEgresos->fetch();
$totalMontoEgresos = $totalMontoEgresos ? $totalMontoEgresos : 0;
$queryMontoEgresos->close();

$conn->close();
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
<style>


            .containerflex {
                display: flex; /* Alinea los elementos en una fila */
                gap: 20px; /* Espacio entre los elementos */
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
            

</head>
<body>

<!--PRIMER FORM -->
<div class="container">
    <div class="card formulario-control-caja">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off" id="formulario">
                <div class="row">
                    <!-- Campo Fecha de Apertura -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha y Hora de Apertura</label>
                            <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                value="<?php echo isset($fecha_inicio2) ? date('Y-m-d\TH:i', strtotime($fecha_inicio2)) : ''; ?>" readonly>
                        </div>
                    </div>
                    
                    <!-- Campo Nombre del Administrador -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_usuario">Nombre del Usuario</label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" 
                                   value="<?php echo isset($nombre_usuario2) ? htmlspecialchars($nombre_usuario2) : ''; ?>" readonly>
                        </div>
                    </div>

                    <!-- Campo Nombre de la Caja -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_caja">Caja:</label>
                            <input type="text" class="form-control" id="nombre_caja" name="nombre_caja" 
                                   value="<?php echo isset($nombre_caja2) ? htmlspecialchars($nombre_caja2) : ''; ?>" readonly>
                        </div>
                    </div>

                    <!-- Campo Nombre del Turno -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_turno">Turno:</label>
                            <input type="text" class="form-control" id="nombre_turno" name="nombre_turno" 
                                   value="<?php echo isset($nombre_turno2) ? htmlspecialchars($nombre_turno2) : ''; ?>" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<!--SEGUNDO FORM -->
<?php
// Inicializamos con el monto de apertura
$totalMontoIngresos = $monto_apertura2+$totalMontoIngresosPAGINA; 

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
                    <label for="monto_apertura">Monto Apertura:</label>
                    <input type="text" id="monto_apertura" name="monto_apertura" value="<?php echo isset($monto_apertura2) ? htmlspecialchars($monto_apertura2) : ''; ?>" readonly>
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


<!--TERCER FORM -->
    <div class="container-forms">
        <div class="formulario-control-caja">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="formulario_cierre">
                <input type="hidden" name="id_control_caja" value="<?php echo htmlspecialchars($id_cierre); ?>">



                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_cierre">Monto de Cierre:</label>
                            <input type="text" class="form-control" id="monto_cierre" name="monto_cierre" 
                                   value="<?php echo isset($monto_cierre2) ? number_format($monto_cierre2, 0, ',', '.') : ''; ?>"readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                    <div class="form-group">
                            <label for="ajuste">Ajuste:</label>
                            <input type="text" class="form-control" id="ajuste" name="ajuste" 
                                value="" readonly> <!-- Este campo mostrará el ajuste calculado -->
                        </div>

                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="printable">
                <!-- Aquí va el contenido que deseas imprimir -->
            </div>
            <div class="d-flex justify-content-between mt-3">
                <div class="w-50 pr-1"> <!-- Ajusta el ancho del primer botón -->
                    <a href="cierre.php" class="btn btn-success btn-block">Regresar a Cierre de Caja</a> <!-- Botón verde -->
                </div>
                <div class="w-50 pl-1"> <!-- Ajusta el ancho del segundo botón -->
                    <button type="button" class="btn btn-secondary btn-block" onclick="imprimirPagina()">Imprimir</button>
                </div>
            </div>
        </div>
    </div>
</div>


</html>

<script>
// Función para calcular el ajuste restando el monto de cierre de los ingresos y egresos
function calcularTotal() {
    // Verificar que las variables PHP existan y tengan valores numéricos
    var totalIngresos = parseFloat(<?php echo isset($totalMontoIngresos) ? $totalMontoIngresos : 0; ?>); 
    var totalEgresos = parseFloat(<?php echo isset($totalMontoEgresos) ? $totalMontoEgresos : 0; ?>); 
    
    // Convertir el valor de monto_cierre a número
    var montoCierre =parseFloat(<?php echo isset($monto_cierre2) ? $monto_cierre2 : 0; ?>);

    // Realizar la resta
    var Resta = (totalIngresos - totalEgresos);

    // Calcular el ajuste
    var ajuste = montoCierre - Resta;

    // Mostrar el ajuste formateado, incluyendo negativos
    document.getElementById('ajuste').value = ajuste.toLocaleString('es-CL', { 
        minimumFractionDigits: 0, 
        maximumFractionDigits: 0 
    }); 
}

// Llama a la función al cargar la página
document.addEventListener("DOMContentLoaded", function() {
    calcularTotal();
});
</script>


<?php
include "includes/footer.php"; // Incluir el pie de página
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
<style>


            .containerflex {
                display: flex; /* Alinea los elementos en una fila */
                gap: 20px; /* Espacio entre los elementos */
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
            

</head>
<body>



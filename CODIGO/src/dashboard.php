<?php
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Obtener el id del usuario desde la sesión
$idempresa = $_SESSION['idempresa'];
/*
// Consultar el rol del usuario
$queryRol = mysqli_query($conexion, "SELECT rol FROM usuarios WHERE id = '$idUser'");
$usuario = mysqli_fetch_assoc($queryRol);
$rolUsuario = $usuario['rol'];

// Contadores generales
$query1 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM salas WHERE estado = 1");
$totalSalas = mysqli_fetch_assoc($query1);

$query2 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM platos WHERE estado = 1");
$totalPlatos = mysqli_fetch_assoc($query2);

$query3 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM usuarios WHERE estado = 1");
$totalUsuarios = mysqli_fetch_assoc($query3);

$query4 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM pedidos WHERE estado = 1");
$totalPedidos = mysqli_fetch_assoc($query4);

$querymesas = mysqli_query($conexion, "SELECT COUNT(id) AS mesas_abiertas FROM pedidos WHERE estado = 'PENDIENTE'");
$totalMesas = mysqli_fetch_assoc($querymesas);

$sqlh="SELECT count(id) as Total FROM pedidos WHERE estado='FINALIZADO' AND year(fecha)=".date('Y')." AND month(fecha)=".date('m')." AND day(fecha)=".date('d');
$queryventashoy= mysqli_query($conexion,$sqlh);
$totalhoy= mysqli_fetch_assoc($queryventashoy);

// Obtener ventas del día actual
$fechaActual = date('Y-m-d');
$queryVentasDelDia = mysqli_query($conexion, "
    SELECT SUM(total) AS total
    FROM pedidos
    WHERE DATE(fecha) = '$fechaActual' AND estado = 'FINALIZADO'
");
$ventasDelDia = mysqli_fetch_assoc($queryVentasDelDia);
$totalVentasDelDia = $ventasDelDia['total'] ? $ventasDelDia['total'] : 0;

$query5 = mysqli_query($conexion, "SELECT SUM(total) AS total FROM pedidos");
$totalVentas = mysqli_fetch_assoc($query5);

// Ventas por día del mes actual
$mesActual = date('m');
$anioActual = date('Y');

$queryVentasPorDia = mysqli_query($conexion, "
    SELECT DAY(fecha) AS dia, SUM(total) AS total
    FROM pedidos
    WHERE MONTH(fecha) = '$mesActual' AND YEAR(fecha) = '$anioActual' AND estado = 'FINALIZADO'
    GROUP BY dia
");

$ventasPorDiaActual = array_fill(1, 31, 0);
while ($row = mysqli_fetch_assoc($queryVentasPorDia)) {
    $ventasPorDiaActual[(int)$row['dia']] = (float)$row['total'];
}
$ventasPorDiaJson = json_encode(array_values($ventasPorDiaActual));
$ventasPorDiaLabels = json_encode(array_keys($ventasPorDiaActual));
?>

<div class="card">
    <div class="card-header text-center">
        Principal
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalMesas['mesas_abiertas']; ?></h3>
                        <p>Mesas Abiertas</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="index.php" class="small-box-footer">M&aacute;s info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $totalSalas['total']; ?></h3>
                        <p>Salones</p>
                    </div>
                    <div class="icon">
                        <i class="nav-icon fas fa-door-open"></i>
                    </div>
                    <a href="salas.php" class="small-box-footer">M&aacute;s info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- Sección de Usuarios: Mostrar solo si el rol es 1 -->
            <?php if ($rolUsuario == 1): ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $totalPlatos['total']; ?></h3>
                        <p>Platos Activos</p>
                    </div>
                    <div class="icon">
                        <i class="nav-icon fas fa-coffee"></i>
                    </div>
                    <a href="usuarios.php" class="small-box-footer">M&aacute;s info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $totalhoy['Total']; ?></h3>
                        <p>Ventas Hoy</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="lista_ventas.php" class="small-box-footer">M&aacute;s info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <!-- Sección para ventas diarias -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Ventas Diarias del Mes (<?php echo date('d/m/Y'); ?>)</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span>Total Ventas -  Día Actual:</span>
                                <span class="text-bold text-lg">$<?php echo number_format($totalVentasDelDia, 0, '.', ','); ?></span>
                            </p>
                        </div>
                        <div class="position-relative mb-4">
                            <canvas id="daily-sales-chart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección para ventas mensuales -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Ventas Mensuales</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span>Total Anual</span>
                                <span class="text-bold text-lg">$<?php echo number_format($totalVentas['total'], 0, '.', ','); ?></span>
                            </p>
                        </div>
                        <div class="position-relative mb-4">
                            <canvas id="sales-chart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>*/
include_once "includes/footer.php";
/*
<script src="../assets/js/dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos para el gráfico de ventas diarias
let salesData = <?php echo $ventasPorDiaJson; ?>;
let labels = <?php echo $ventasPorDiaLabels; ?>;

// Inicializar el gráfico de ventas diarias
const ctxDaily = document.getElementById('daily-sales-chart').getContext('2d');
const dailySalesChart = new Chart(ctxDaily, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Ventas Diarias',
            data: salesData,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Total Ventas ($)'
                },
                ticks: {
                    stepSize: 200000,
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Días del Mes'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return 'Ventas: $' + tooltipItem.raw.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<style>
    #daily-sales-chart {
        height: 200px; 
    }
</style>
*/
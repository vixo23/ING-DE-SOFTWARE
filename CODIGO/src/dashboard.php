<?php
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Obtener la empresa del usuario logueado
$idempresa = $_SESSION['idempresa'];
$fechaActual = date('Y-m-d');

// ======== CONSULTA DE AUSENTES ========
$queryAusentes = "
  SELECT COUNT(*) AS ausentes
  FROM usuarios u
  LEFT JOIN marcas m 
    ON u.id_usuarios = m.id_usuario 
    AND DATE(m.fecha) = '$fechaActual'
  WHERE 
    u.id_empresa = '$idempresa'
    AND u.status = 1
    AND m.id_marcas IS NULL;
";
$resultAusentes = mysqli_query($conexion, $queryAusentes);
$ausentes = mysqli_fetch_assoc($resultAusentes)['ausentes'];

// ======== CONSULTA DE TOTAL DE EMPLEADOS ========
$queryTotal = "SELECT COUNT(*) AS total FROM usuarios WHERE id_empresa = '$idempresa' AND status = 1;";
$resultTotal = mysqli_query($conexion, $queryTotal);
$total = mysqli_fetch_assoc($resultTotal)['total'];

// ======== CÁLCULO DE ASISTENTES ========
$asistentes = $total - $ausentes;
?>

<div class="card mt-4">
  <div class="card-body">

    <!-- BOTÓN GENERAR TABLA -->
    <div class="d-flex justify-content-end mb-3">
      <button id="btnInasistencia" class="btn btn-primary shadow-sm px-3 py-2 rounded-pill d-flex align-items-center gap-2">
        <i class="fas fa-user-times"></i>
        <span>Generar Tabla de Inasistencia</span>
      </button>
    </div>

    <!-- TABLA DE INASISTENCIA -->
    <div id="tablaInasistencia" style="display:none;">
      <h5 class="text-center mb-3">Personal ausente del día <?php echo $fechaActual; ?></h5>

      <?php
      $query = "
        SELECT 
          u.id_usuarios,
          u.rut,
          u.nombres,
          u.apellido1,
          u.apellido2,
          d.descripcion AS departamento
        FROM usuarios u
        LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento
        LEFT JOIN marcas m 
          ON u.id_usuarios = m.id_usuario 
          AND DATE(m.fecha) = '$fechaActual'
        WHERE 
          u.id_empresa = '$idempresa'
          AND u.status = 1
          AND m.id_marcas IS NULL
        ORDER BY u.apellido1, u.apellido2;
      ";

      $result = mysqli_query($conexion, $query);
      ?>

      <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
              <tr>
                <th>RUT</th>
                <th>Nombre Completo</th>
                <th>Departamento</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?php echo $row['rut']; ?></td>
                  <td><?php echo $row['nombres'] . ' ' . $row['apellido1'] . ' ' . $row['apellido2']; ?></td>
                  <td><?php echo $row['departamento'] ?: 'Sin asignar'; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-success text-center">
          Todos los empleados han registrado marcas hoy.
        </div>
      <?php endif; ?>
    </div>

    <!-- ======== GRÁFICO DE ASISTENCIA ======== -->
    <div class="mt-5">
      <h5 class="text-center mb-2">Resumen de asistencia del día <?php echo $fechaActual; ?></h5>
      <p class="text-center mb-4">
        <strong>Asistentes:</strong> <?php echo $asistentes; ?> |
        <strong>Ausentes:</strong> <?php echo $ausentes; ?>
      </p>
      <canvas id="graficoAsistencia" height="100"></canvas>
    </div>

  </div>
</div>

<!-- ======== SCRIPT PARA TABLA ======== -->
<script>
document.getElementById('btnInasistencia').addEventListener('click', function() {
  const tabla = document.getElementById('tablaInasistencia');
  if (tabla.style.display === 'none') {
    tabla.style.display = 'block';
    this.innerHTML = '<i class="fas fa-eye-slash"></i><span> Ocultar Tabla de Inasistencia</span>';
  } else {
    tabla.style.display = 'none';
    this.innerHTML = '<i class="fas fa-user-times"></i><span> Generar Tabla de Inasistencia</span>';
  }
});
</script>

<!-- ======== SCRIPT DEL GRÁFICO (Chart.js) ======== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('graficoAsistencia').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Asistentes', 'Ausentes'],
    datasets: [{
      label: 'Cantidad de empleados',
      data: [<?php echo $asistentes; ?>, <?php echo $ausentes; ?>],
      backgroundColor: ['#28a745', '#dc3545'],
      borderColor: ['#1e7e34', '#a71d2a'],
      borderWidth: 1,
      barThickness: 50, // Barras más delgadas
      maxBarThickness: 60
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: { enabled: true }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      },
      x: {
        grid: { display: false }
      }
    }
  }
});
</script>

<!-- ======== ESTILOS ======== -->
<style>
  .card {
    margin: 30px auto;
    max-width: 1000px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-radius: 12px;
    overflow: hidden;
  }

  #btnInasistencia {
    font-size: 0.95rem;
    font-weight: 500;
    background-color: #007bff;
    border: none;
    transition: all 0.3s ease;
  }

  #btnInasistencia:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
  }

  .table {
    background-color: #fff;
  }

  .table th {
    background-color: #343a40;
    color: #fff;
  }

  .alert {
    font-size: 1rem;
    padding: 15px;
    border-radius: 8px;
  }

  p strong {
    color: #343a40;
  }
</style>

<?php include_once "includes/footer.php"; ?>

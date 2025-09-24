<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];
?>

<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<div class="card">
    <h2>Ficha de Empleados</h2>
</div>

<div class="mb-3 mt-3">
    <button type="button" class="btn btn-warning" id="btnFiltro" onclick="filtraractivos()">
        <i class="fa-solid fa-sliders"></i> Mostrar Solo Activos
    </button>

    <button type="button" class="btn btn-success" id="btnImprimir" onclick="imprimirPDFs()" disabled>
        <i class="fa-solid fa-print"></i> Imprimir Ficha(s)
    </button>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id_empresa='$id_empresa' ORDER BY nombres");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    
                    $empleado_data = [
                        'id' => $data['id_usuarios'],
                        'nombre' => $data['nombres'],
                        'apellido1' => $data['apellido1'],
                        'apellido2' => $data['apellido2'],
                        'email' => $data['email'],
                        'tipoContrato' => $data['id_tipocontrato'],
                        'cargo' => $data['cargo'],
                        'telefono' => $data['telefono'],
                        'direccion' => $data['direccion'],
                        'fechaIngreso' => $data['fechaCreacion']
                    ];
                    $empleado_json = htmlspecialchars(json_encode($empleado_data), ENT_QUOTES, 'UTF-8');
            ?>
                    <tr data-status="<?php echo $data['status']; ?>">
                        <td><?php echo $data['id_usuarios']; ?></td>
                        <td><?php echo $data['nombres']; ?></td>
                        <td><?php echo $data['cargo']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick='fichaempleado(<?php echo $empleado_json; ?>)' class="btn btn-success" title="Ver Ficha">
                                <i class='fas fa-id-card'></i>
                            </a>

                            <button type="button" onclick='seleccionarFicha(this, <?php echo $empleado_json; ?>)' class="btn btn-outline-primary" title="Seleccionar para Imprimir">
                               <i class="fas fa-check"></i> Seleccionar
                            </button>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Ficha Empleado</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>ID:</strong> <span id="id_usuarios"></span></p>
        <p><strong>Nombre:</strong> <span id="nombres"></span></p>
        <p><strong>Apellido Paterno:</strong> <span id="apellido1"></span></p>
        <p><strong>Apellido Materno:</strong> <span id="apellido2"></span></p>
        <p><strong>Correo:</strong> <span id="email"></span></p>
        <p><strong>Tipo Contrato:</strong> <span id="tipoContrato"></span></p>
        <p><strong>Cargo:</strong> <span id="cargo"></span></p>
        <p><strong>Teléfono:</strong> <span id="telefono"></span></p>
        <p><strong>Dirección:</strong> <span id="direccion"></span></p>
        <p><strong>Fecha Ingreso:</strong> <span id="fecha_ingreso"></span></p>
      </div>
    </div>
  </div>
</div>

<script>
// AHORA ES UN ARRAY para guardar múltiples empleados seleccionados
let empleadosSeleccionados = [];

function fichaempleado(datos) {
    document.getElementById('id_usuarios').textContent = datos.id;
    document.getElementById('nombres').textContent = datos.nombre;
    document.getElementById('apellido1').textContent = datos.apellido1;
    document.getElementById('apellido2').textContent = datos.apellido2;
    document.getElementById('email').textContent = datos.email;
    document.getElementById('tipoContrato').textContent = datos.tipoContrato;
    document.getElementById('cargo').textContent = datos.cargo;
    document.getElementById('telefono').textContent = datos.telefono;
    document.getElementById('direccion').textContent = datos.direccion;
    document.getElementById('fecha_ingreso').textContent = datos.fechaIngreso;
    $('#editarModal').modal('show');
}

/**
 * FUNCIÓN MODIFICADA: Selecciona o deselecciona un empleado.
 */
function seleccionarFicha(boton, datosEmpleado) {
    const fila = boton.closest('tr');
    // Buscamos si el empleado ya está en el array por su ID.
    const indice = empleadosSeleccionados.findIndex(e => e.id === datosEmpleado.id);

    if (indice > -1) {
        // Si ya existe, lo quitamos del array (deseleccionar)
        empleadosSeleccionados.splice(indice, 1);
        fila.classList.remove('table-info'); // Quita el resaltado
    } else {
        // Si no existe, lo añadimos (seleccionar)
        empleadosSeleccionados.push(datosEmpleado);
        fila.classList.add('table-info'); // Añade el resaltado
    }
    
    // Actualizamos el estado y texto del botón de imprimir
    actualizarBotonImprimir();
}

/**
 * NUEVA FUNCIÓN: Actualiza el botón de imprimir según cuántos empleados hay seleccionados.
 */
function actualizarBotonImprimir() {
    const btnImprimir = document.getElementById('btnImprimir');
    const cantidad = empleadosSeleccionados.length;

    if (cantidad === 0) {
        btnImprimir.disabled = true;
        btnImprimir.innerHTML = '<i class="fa-solid fa-print"></i> Imprimir Ficha(s)';
    } else if (cantidad === 1) {
        btnImprimir.disabled = false;
        btnImprimir.innerHTML = '<i class="fa-solid fa-print"></i> Imprimir Ficha';
    } else {
        btnImprimir.disabled = false;
        btnImprimir.innerHTML = `<i class="fa-solid fa-print"></i> Imprimir Fichas (${cantidad})`;
    }
}

/**
 * FUNCIÓN MODIFICADA: Genera un PDF con todos los empleados seleccionados.
 */
function imprimirPDFs() {
    if (empleadosSeleccionados.length === 0) {
        alert("Por favor, seleccione al menos un empleado.");
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Recorremos el array de empleados seleccionados
    empleadosSeleccionados.forEach((empleado, index) => {
        // Si no es el primer empleado, añadimos una nueva página
        if (index > 0) {
            doc.addPage();
        }

        // Dibuja el contenido de la ficha en la página actual
        doc.setFontSize(20);
        doc.text("Ficha de Empleado", 105, 20, null, null, "center");

        doc.setFontSize(12);
        let y = 40;
        doc.text(`ID: ${empleado.id}`, 20, y); y += 10;
        doc.text(`Nombre Completo: ${empleado.nombre} ${empleado.apellido1} ${empleado.apellido2}`, 20, y); y += 10;
        doc.text(`Correo Electrónico: ${empleado.email}`, 20, y); y += 10;
        doc.text(`Cargo: ${empleado.cargo}`, 20, y); y += 10;
        doc.text(`Tipo de Contrato: ${empleado.tipoContrato}`, 20, y); y += 10;
        doc.text(`Teléfono: ${empleado.telefono}`, 20, y); y += 10;
        doc.text(`Dirección: ${empleado.direccion}`, 20, y); y += 10;
        doc.text(`Fecha de Ingreso: ${empleado.fechaIngreso}`, 20, y);
    });

    // Guarda el archivo PDF con un nombre genérico
    doc.save('Fichas-Empleados.pdf');
}


// Función de filtro (sin cambios)
let filtroEstaActivo = false;
function filtraractivos() {
    filtroEstaActivo = !filtroEstaActivo;
    const boton = document.getElementById('btnFiltro');
    const filas = document.querySelectorAll('#tbl tbody tr');
    filas.forEach(fila => {
        const estadoNumerico = fila.dataset.status;
        if (filtroEstaActivo) {
            if (estadoNumerico === '0') fila.style.display = 'none';
            else fila.style.display = '';
        } else {
            fila.style.display = '';
        }
    });
    if (filtroEstaActivo) {
        boton.innerHTML = '<i class="fa-solid fa-sliders"></i> Mostrar Todos';
        boton.classList.remove('btn-warning');
        boton.classList.add('btn-info');
    } else {
        boton.innerHTML = '<i class="fa-solid fa-sliders"></i> Mostrar Solo Activos';
        boton.classList.remove('btn-info');
        boton.classList.add('btn-warning');
    }
}
</script>

<?php include_once "includes/footer.php"; ?>
<?php
session_start();
include "../conexion.php";
include "includes/header.php";

$id_empresa_sesion = $_SESSION['idempresa'];
$mensaje = '';

// --- BLOQUE DE GUARDADO SEGURO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos (ahora el de 'empresa' será un ID)
    $rut = $_POST['rut'];
    $digitoRut = $_POST['digitoRut'];
    $nombres = $_POST['nombres'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $direccion = $_POST['direccion'];
    $comuna = $_POST['comuna'];
    $celular = $_POST['celular'];
    $correo = $_POST['email'];
    $telefono = $_POST['telefono'];
    $id_tipocontrato = $_POST['id_tipocontrato']; // Nota: esto también debería ser un combo con IDs
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $empresa_id = $_POST['id_empresa']; // Recibimos el ID de la empresa seleccionada
    $id_sucursal = $_POST['id_sucursal'];
    $id_centrocosto =$_POST['id_centrocosto'];
    $id_departamento =$_POST['id_departamento'];
    $status =$_POST['status'];


    // Valores fijos
    $password_hash =1234; // Hashear la contraseña es más seguro
    $username = $rut;
    $turno_id = 1;

    // Usamos sentencias preparadas para evitar inyección SQL
    $query = "INSERT INTO usuarios (rut, digitoRut, nombres, apellido1, apellido2, id_tipocontrato, direccion, comuna, telefono, celular, email, password, username, cargo, status, id_sucursal, id_centrocosto, id_departamento, fechaCreacion, id_empresa) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conexion->prepare($query)) {
        // Vinculamos los parámetros
        // Nota: debes ajustar los campos que faltan (cargo, sucursal, etc.)
        $cargo_placeholder = $_POST['cargo']; // Temporal

        $stmt->bind_param("ssssssssssssssisissi", 
            $rut, $digitoRut, $nombres, $apellido1, 
            $apellido2, $id_tipocontrato, $direccion, 
            $comuna, $telefono, $celular, $correo, $password_hash, $username, 
            $cargo_placeholder, $status, $id_sucursal, $id_centrocosto, $id_departamento, $fecha_ingreso, $empresa_id
        );

        if ($stmt->execute()) {
            $mensaje = "Empleado registrado con éxito.";
        } else {
            $mensaje = "Error al registrar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje = "Error al preparar la consulta: " . $conexion->error;
    }
}

// --- NUEVO: CONSULTA PARA OBTENER LAS EMPRESAS ---
$resultado_empresas = mysqli_query($conexion, "SELECT id_empresas, nombreFantasia FROM empresas WHERE status = 1 ORDER BY id_empresas");
$resultado_sucursales = mysqli_query($conexion, "SELECT id_sucursal, nombre FROM sucursal WHERE status = 1 ORDER BY id_sucursal");
$resultado_centrocostos = mysqli_query($conexion, "SELECT id_centro, descripcion FROM centro_costo WHERE status = 1 ORDER BY id_centro");
$resultado_departamentos = mysqli_query($conexion, "SELECT id_departamento, descripcion FROM departamentos WHERE status = 1 ORDER BY id_departamento");
$resultado_tipocontrato = mysqli_query($conexion, "SELECT id_tipocontrato, descripcion FROM tipo_contrato WHERE status = 1 ORDER BY id_tipocontrato");

$comunas_rm = [
    'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central', 
    'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 
    'La Pintana', 'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo', 
    'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 'Pedro Aguirre Cerda', 'Peñalolén', 
    'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal', 'Recoleta', 
    'Renca', 'San Joaquín', 'San Miguel', 'San Ramón', 'Santiago', 'Vitacura'
];
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Empleado</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; }
        .select2-container .select2-selection--single { height: calc(1.5em + .75rem + 2px); }

        /* --- AÑADIDO: REGLA CLAVE PARA OCULTAR LOS PASOS --- */
        .tab {
            display: none;
        }
        /* Estilo para el feedback de validación en Select2 */
        .is-invalid + .select2-container .select2-selection--single {
            border-color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h2 class="mb-0 h4"><i class="fas fa-user-plus mr-2"></i>Formulario de Registro de Empleado</h2>
        </div>
        <div class="card-body">
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-info"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="regForm">
                
                <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" style="width: 33.33%;">Paso 1 de 3</div>
                </div>

                <div class="tab">
                    <h5><i class="fas fa-address-card text-primary mr-2"></i>Información Personal</h5>
                    <hr class="mt-2 mb-4">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label>RUT:</label>
                            <input placeholder="12345678" name="rut" class="form-control" required>
                        </div>
                        <div class="col-md-1 mb-3">
                            <label>DV:</label>
                            <input placeholder="K" name="digitoRut" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nombres:</label>
                            <input placeholder="Nombre completo..." name="nombres" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellido Paterno:</label>
                            <input placeholder="Apellido paterno..." name="apellido1" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellido Materno:</label>
                            <input placeholder="Apellido materno..." name="apellido2" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="tab">
                    <h5><i class="fas fa-map-marker-alt text-primary mr-2"></i>Datos de Contacto</h5>
                    <hr class="mt-2 mb-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label>Dirección:</label>
                            <input placeholder="Calle, número, depto..." name="direccion" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Comuna:</label>
                            <select name="comuna" class="form-control" required>
                                <option></option>
                                <?php foreach ($comunas_rm as $comuna) { echo '<option value="' . htmlspecialchars($comuna) . '">' . htmlspecialchars($comuna) . '</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Celular:</label>
                            <input placeholder="+56 9..." name="celular" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Teléfono Fijo (Opcional):</label>
                            <input name="telefono" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Correo Electrónico:</label>
                            <input placeholder="ejemplo@correo.com" name="email" class="form-control" type="email" required>
                        </div>
                    </div>
                </div>

                <div class="tab">
                    <h5><i class="fas fa-briefcase text-primary mr-2"></i>Información Laboral</h5>
                    <hr class="mt-2 mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Empresa:</label>
                            <select name="id_empresa" class="form-control" required>
                                <option></option>
                                <?php mysqli_data_seek($resultado_empresas, 0); while ($fila = mysqli_fetch_assoc($resultado_empresas)) { echo '<option value="' . $fila['id_empresas'] . '">' . htmlspecialchars($fila['nombreFantasia']) . '</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Sucursal:</label>
                            <select name="id_sucursal" class="form-control" required>
                                <option></option>
                                <?php mysqli_data_seek($resultado_sucursales, 0); while ($fila = mysqli_fetch_assoc($resultado_sucursales)) { echo '<option value="' . $fila['id_sucursal'] . '">' . htmlspecialchars($fila['nombre']) . '</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Departamento:</label>
                            <select name="id_departamento" class="form-control" required>
                                <option></option>
                                <?php mysqli_data_seek($resultado_departamentos, 0); while ($fila = mysqli_fetch_assoc($resultado_departamentos)) { echo '<option value="' . $fila['id_departamento'] . '">' . htmlspecialchars($fila['descripcion']) . '</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Centro de Costo:</label>
                            <select name="id_centrocosto" class="form-control" required>
                                <option></option>
                                <?php mysqli_data_seek($resultado_centrocostos, 0); while ($fila = mysqli_fetch_assoc($resultado_centrocostos)) { echo '<option value="' . $fila['id_centro'] . '">' . htmlspecialchars($fila['descripcion']) . '</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Cargo:</label>
                            <input name="cargo" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tipo de Contrato:</label>
                            <select name="id_tipocontrato" class="form-control" required>
                                <option></option>
                                <?php mysqli_data_seek($resultado_tipocontrato, 0); while ($fila = mysqli_fetch_assoc($resultado_tipocontrato)) { echo '<option value="' . $fila['id_tipocontrato'] . '">' . htmlspecialchars($fila['descripcion']) . '</option>'; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Fecha de Ingreso:</label>
                            <input name="fecha_ingreso" class="form-control" type="date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Estado:</label>
                            <select name="status" class="form-control" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-right">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="nextPrev(-1)">Anterior</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextPrev(1)">Siguiente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
var currentTab = 0; 
showTab(currentTab); 

function showTab(n) {
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    document.getElementById("prevBtn").style.display = (n == 0) ? "none" : "inline";
    if (n == (x.length - 1)) {
        document.getElementById("nextBtn").innerHTML = '<i class="fas fa-save mr-2"></i>Registrar Empleado';
    } else {
        document.getElementById("nextBtn").innerHTML = "Siguiente";
    }
    var progress = ((n + 1) / x.length) * 100;
    var progressBar = document.querySelector(".progress-bar");
    progressBar.style.width = progress + "%";
    progressBar.innerHTML = "Paso " + (n + 1) + " de " + x.length;
}

function nextPrev(n) {
    var x = document.getElementsByClassName("tab");
    if (n == 1 && !validateForm()) return false;
    x[currentTab].style.display = "none";
    currentTab = currentTab + n;
    if (currentTab >= x.length) {
        document.getElementById("regForm").submit();
        return false;
    }
    showTab(currentTab);
}

function validateForm() {
    var tab, inputs, selects, i, valid = true;
    tab = document.getElementsByClassName("tab")[currentTab];
    inputs = tab.getElementsByTagName("input");
    selects = tab.getElementsByTagName("select");

    for (i = 0; i < inputs.length; i++) {
        if (inputs[i].hasAttribute("required") && inputs[i].value.trim() === "") {
            inputs[i].classList.add("is-invalid");
            valid = false;
        } else {
            inputs[i].classList.remove("is-invalid");
        }
    }
    
    for (i = 0; i < selects.length; i++) {
        if (selects[i].hasAttribute("required") && selects[i].value === "") {
            selects[i].classList.add("is-invalid");
            valid = false;
        } else {
            selects[i].classList.remove("is-invalid");
        }
    }
    return valid;
}

$(document).ready(function() {
    $('#regForm select').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una opción',
        allowClear: true
    }).on('change', function() {
        // Remueve la clase de error de Select2 cuando se selecciona algo
        $(this).removeClass('is-invalid');
    });
});
</script>

<?php include_once "includes/footer.php"; ?>
</body>
</html>


<?php
session_start();
include "../conexion.php";
include "includes/header.php";

$id_empresa = $_SESSION['idempresa'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $digitoverif = $_POST['digitoverif'];
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $direccion = $_POST['direccion'];
    $comuna = $_POST['comuna'];
    $celular = $_POST['celular'];
    $correo = $_POST['correo'];
    $telefono_particular = $_POST['telefono_particular'];
    $tipo_contrato = $_POST['tipo_contrato'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $empresa = $_POST['empresa'];
    $sucursal = $_POST['sucursal'];
    $centro_costo = $_POST['centro_costo'];
    $departamento = $_POST['departamento'];
    $cargo = $_POST['cargo'];
    $id_empresa = $_SESSION['idempresa'];

    // Valores fijos o de prueba (ajusta según tus necesidades)
    $password = '1234';
    $username = $rut;
    $sucursal_id = 12;
    $turno_id = 1;
    $status = 1;

    $query = "INSERT INTO usuarios (rut, digitoRut, nombres, apellido1, apellido2, tipoContrato, direccion, comuna, telefono, celular, email, password, username, cargo, status, sucursal_id_sucursal, turnos_id_turnos, fechaCreacion, id_empresa) 
              VALUES ('$rut', '$digitoverif', '$nombre', '$apellido_paterno', '$apellido_materno', '$tipo_contrato', '$direccion', '$comuna', '$telefono_particular', '$celular', '$correo', '$password', '$username', '$cargo', '$status', '$sucursal_id', '$turno_id', '$fecha_ingreso', '$id_empresa')";

    if (mysqli_query($conexion, $query)) {
        $mensaje = "Empleado registrado con éxito.";
    } else {
        $mensaje = "Error al registrar: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Empleado</title>
    <style>
        body { font-family: Arial; }
        .form-container { max-width: 900px; margin: auto; padding: 20px; }
        form { display: flex; flex-wrap: wrap; gap: 20px; }
        .form-group { flex: 1 1 45%; display: flex; flex-direction: column; }
        .full-width { flex: 1 1 100%; }
        input, select { padding: 6px; font-size: 14px; }
        input[type="submit"] { margin-top: 20px; padding: 10px; background-color: #4CAF50; color: white; border: none; font-size: 16px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
        p.mensaje { color: green; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<div class="form-container mt-20">
    <h2>Formulario de Registro de Empleado</h2>

    <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>

    <form method="POST" action="">
        <!-- Aquí van todos los campos del formulario como los tienes actualmente -->
        <div class="form-group">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required>
        </div>

        <div class="form-group">
            <label for="digitoverif">Dígito Verificador:</label>
            <input type="text" id="digitoverif" name="digitoverif" required>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>

        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno:</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" required>
        </div>

        <div class="form-group">
            <label for="apellido_materno">Apellido Materno:</label>
            <input type="text" id="apellido_materno" name="apellido_materno" required>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" required>
        </div>

        <div class="form-group">
            <label for="comuna">Comuna:</label>
            <input type="text" id="comuna" name="comuna" required>
        </div>

        <div class="form-group">
            <label for="celular">Celular:</label>
            <input type="text" id="celular" name="celular" required>
        </div>

        <div class="form-group">
            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" required>
        </div>

        <div class="form-group">
            <label for="telefono_particular">Teléfono Particular:</label>
            <input type="text" id="telefono_particular" name="telefono_particular">
        </div>

        <div class="form-group">
            <label for="tipo_contrato">Tipo de Contrato:</label>
            <select id="tipo_contrato" name="tipo_contrato" required>
                <option value="">Seleccione...</option>
                <option value="Plazo Fijo">Contrato Plazo Fijo</option>
                <option value="Indefinido">Contrato Indefinido</option>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
        </div>

        <div class="form-group">
            <label for="empresa">Empresa:</label>
            <input type="text" id="empresa" name="empresa" required>
        </div>

        <div class="form-group">
            <label for="sucursal">Sucursal:</label>
            <input type="text" id="sucursal" name="sucursal" required>
        </div>

        <div class="form-group">
            <label for="centro_costo">Centro de Costo:</label>
            <input type="text" id="centro_costo" name="centro_costo" required>
        </div>

        <div class="form-group">
            <label for="departamento">Departamento:</label>
            <input type="text" id="departamento" name="departamento" required>
        </div>

        <div class="form-group">
            <label for="cargo">Cargo:</label>
            <input type="text" id="cargo" name="cargo" required>
        </div>

        <div class="form-group full-width">
            <input type="submit" value="Agregar Empleado">
        </div>
    </form>
</div>

</body>
</html>






<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Variable para guardar el ID del tipo de contrato
let tipocontratoId;

// Función para abrir el modal de confirmación
function abrirModalConfirmacion(id) {
    tipocontratoId = id; // Guardar el ID del garzón
    $('#confirmModal').modal('show'); // Mostrar el modal
}

// Función para confirmar el cambio de estado
$('#confirmBtn').on('click', function() {
    if (tipocontratoId) {
        // Redirigir a cambiar_estado.php con el ID del garzón
        window.location.href = cambiar_estado.php?id=${tipocontratoId};
    }
});

// Función para abrir el modal de edición
function editartipocontrato(id, nombre, estado) {
    // Rellenar el formulario de edición
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#editarModal').modal('show'); // Mostrar el modal de edición
}

// Función para limpiar el formulario
function limpiar() {
    $('#formulario')[0].reset(); // Restablecer el formulario
}

// Función para mostrar alerta
function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje); // Rellenar el mensaje de alerta
    $('#alertModal').modal('show'); // Mostrar el modal de alerta
}
</script>

<?php include_once "includes/footer.php"; ?>
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
    $query = "INSERT INTO usuarios (rut, digitoRut, nombres, apellido1, apellido2, id_tipocontrato, direccion, comuna, telefono, celular, email, password, username, cargo, status, id_sucursal, id_centrocosto, id_departamento, turnos_id_turnos, fechaCreacion, id_empresa) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conexion->prepare($query)) {
        // Vinculamos los parámetros
        // Nota: debes ajustar los campos que faltan (cargo, sucursal, etc.)
        $cargo_placeholder = $_POST['cargo']; // Temporal

        $stmt->bind_param("ssssssssssssssisisisi", 
            $rut, $digitoRut, $nombres, $apellido1, 
            $apellido2, $id_tipocontrato, $direccion, 
            $comuna, $telefono, $celular, $correo, $password_hash, $username, 
            $cargo_placeholder, $status, $id_sucursal, $id_centrocosto, $id_departamento, 
            $turno_id, $fecha_ingreso, $empresa_id
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
    <style>
        body { font-family: Arial; }
        .form-container { max-width: auto; margin: auto; padding: 20px; }
        form { display: flex; flex-wrap: wrap; gap: 40px; }
        .form-group { flex: 1 1 1 45%; display: flex; flex-direction: column; }
        .full-width { flex: 1 1 100%; }
        input, select { padding: 5px; font-size: 20px; }
        input[type="submit"] { width:200px ; margin: 20px auto; display:block; padding: 10px; background-color: #4CAF50; color: white; border: none; font-size: 16px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
        p.mensaje { color: green; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<div class="form-container mt-20 ">
    <h2>Formulario de Registro de Empleado</h2>

    <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>

    <form method="POST" action="">
        <!-- Aquí van todos los campos del formulario como los tienes actualmente -->
        <div class="form-group">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required>
        </div>

        <div class="form-group">
            <label for="digitoRut">Dígito Verificador:</label>
            <input type="text" id="digitoRut" name="digitoRut" required>
        </div>

        <div class="form-group">
            <label for="nombres">Nombre:</label>
            <input type="text" id="nombres" name="nombres" required>
        </div>

        <div class="form-group">
            <label for="apellido1">Apellido Paterno:</label>
            <input type="text" id="apellido1" name="apellido1" required>
        </div>

        <div class="form-group">
            <label for="apellido2">Apellido Materno:</label>
            <input type="text" id="apellido2" name="apellido2" required>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" required>
        </div>

        <div class="form-group">
            <label for="comuna">Comuna:</label>
            <select id="comuna" name="comuna" class="form-control" required>
                <option value="">Seleccione una comuna...</option>
                <?php
                // Creamos una opcion por cada comuna en el array
                foreach ($comunas_rm as $comuna) {
                    echo '<option value="' . htmlspecialchars($comuna) . '">' . htmlspecialchars($comuna) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="celular">Celular:</label>
            <input type="text" id="celular" name="celular" required>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="telefono">Telefono Particular:</label>
            <input type="text" id="telefono" name="telefono">
        </div>

        <div class="form-group">
            <label for="id_empresa">Empresa:</label>
            <select id="id_empresa" name="id_empresa" class="form-control" required>
                <option value="">Seleccione una empresa...</option>
                <?php
                // Iteramos sobre los resultados de la consulta de empresas
                while ($fila_empresa = mysqli_fetch_assoc($resultado_empresas)) {
                    // El 'value' es el ID, y el texto visible es el nombre
                    echo '<option value="' . $fila_empresa['id_empresas'] . '">' . htmlspecialchars($fila_empresa['nombreFantasia']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_sucursal">Sucursal:</label>
            <select id="id_sucursal" name="id_sucursal" class="form-control" required>
                <option value="">Seleccione una sucursal...</option>
                <?php
                // Iteramos sobre los resultados de la consulta de sucursal
                while ($fila_sucursal = mysqli_fetch_assoc($resultado_sucursales)) {
                    // El 'value' es el ID, y el texto visible es el nombre
                    echo '<option value="' . $fila_sucursal['id_sucursal'] . '">' . htmlspecialchars($fila_sucursal['nombre']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_centrocosto">Centro de Costo:</label>
            <select id="id_centrocosto" name="id_centrocosto" class="form-control" required>
                <option value="">Seleccione un centro de costo...</option>
                <?php
                // Iteramos sobre los resultados de la consulta de sucursales
                while ($fila_centrocosto = mysqli_fetch_assoc($resultado_centrocostos)) {
                    // El 'value' es el ID, y el texto visible es el nombre
                    echo '<option value="' . $fila_centrocosto['id_centro'] . '">' . htmlspecialchars($fila_centrocosto['descripcion']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_departamento">Departamento:</label>
            <select id="id_departamento" name="id_departamento" class="form-control" required>
                <option value="">Seleccione un Departamento...</option>
                <?php
                // Iteramos sobre los resultados de la consulta de sucursales
                while ($fila_departamento = mysqli_fetch_assoc($resultado_departamentos)) {
                    // El 'value' es el ID, y el texto visible es el nombre
                    echo '<option value="' . $fila_departamento['id_departamento'] . '">' . htmlspecialchars($fila_departamento['descripcion']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="cargo">Cargo:</label>
            <input type="text" id="cargo" name="cargo" required>
        </div>

        <div class="form-group">
            <label for="id_tipocontrato">Tipo de Contrato:</label>
            <select id="id_tipocontrato" name="id_tipocontrato" class="form-control" required>
                <option value="">Seleccione un Tipo de Contrato...</option>
                <?php
                // Iteramos sobre los resultados de la consulta de sucursales
                while ($fila_tipo_contrato = mysqli_fetch_assoc($resultado_tipocontrato)) {
                    // El 'value' es el ID, y el texto visible es el nombre
                    echo '<option value="' . $fila_tipo_contrato['id_tipocontrato'] . '">' . htmlspecialchars($fila_tipo_contrato['descripcion']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
        </div>

        <div class="form-group">
            <label for="status">Estado:</label>
            <select id="status" name="status" class="form-control" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
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
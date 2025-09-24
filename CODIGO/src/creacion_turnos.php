<?php
session_start();
include "../conexion.php";





// Lógica para crear una nueva jornada
if (!empty($_POST['nombre_jornada']) && !empty($_POST['dia_entrada']) && !empty($_POST['hora_inicio_jornada']) && !empty($_POST['entrada_colacion']) && !empty($_POST['salida_colacion']) && !empty($_POST['hora_fin_jornada']) && !empty($_POST['dia_salida']) && !empty($_POST['tolerancia']) && !isset($_POST['action'])) {
    $nombre_jornada = mysqli_real_escape_string($conexion, $_POST['nombre_jornada']);
    $dia_entrada = mysqli_real_escape_string($conexion, $_POST['dia_entrada']);
    $hora_inicio = mysqli_real_escape_string($conexion, $_POST['hora_inicio_jornada']);
    $entrada_colacion = mysqli_real_escape_string($conexion, $_POST['entrada_colacion']);
    $salida_colacion = mysqli_real_escape_string($conexion, $_POST['salida_colacion']);
    $hora_fin = mysqli_real_escape_string($conexion, $_POST['hora_fin_jornada']);
    $dia_salida = mysqli_real_escape_string($conexion, $_POST['dia_salida']);
    $tolerancia = mysqli_real_escape_string($conexion, $_POST['tolerancia']);
    $id_empresa = $_SESSION['idempresa'];
    // Usamos los nombres correctos de las columnas
    $query_insert = mysqli_query($conexion, "INSERT INTO turnos (id_empresa, nombreTurno, diaEntrada, horaEntrada, entradaColacion, salidaColacion, horaSalida, diaSalida, tolerancia) VALUES ('$id_empresa', '$nombre_jornada', '$dia_entrada','$hora_inicio', '$entrada_colacion', '$salida_colacion', '$hora_fin', '$dia_salida', '$tolerancia')");
    if ($query_insert) {
        header("Location: creacion_turnos.php?mensaje=Jornada creada con éxito");
    } else {
        $error_msg = mysqli_error($conexion);
        header("Location: creacion_turnos.php?error=Error al crear la jornada: " . urlencode($error_msg));
    }
    exit();
}

include "includes/header.php";

// Mensajes de feedback
if (isset($_GET['mensaje'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
}
if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}



?>

<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; }
    </style>
</head>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h2 class="mb-0 h2"><i class="fas fa-clock mr-2"></i>Creación de Turnos y Jornadas</h2>
        </div>
        <div class="card-body">
            
            <?php
            if (isset($_GET['mensaje'])) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>' . htmlspecialchars($_GET['mensaje']) . '</div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>

            <form action="creacion_turnos.php" method="post" autocomplete="off">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="nombre_jornada">Nombre de la Jornada</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-tag"></i></span></div>
                            <input type="text" class="form-control" id="nombre_jornada" name="nombre_jornada" placeholder="Ej: Turno de Mañana L-V" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="dia_entrada">Día de Entrada</label>
                         <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-day"></i></span></div>
                            <select class="form-control" id="dia_entrada" name="dia_entrada" required>
                                <option value="">Seleccione...</option>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="hora_inicio_jornada">Hora de Inicio</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-sign-in-alt"></i></span></div>
                            <input type="time" class="form-control" id="hora_inicio_jornada" name="hora_inicio_jornada" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="entrada_colacion">Inicio Colación</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-utensils"></i></span></div>
                            <input type="time" class="form-control" id="entrada_colacion" name="entrada_colacion" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="salida_colacion">Fin Colación</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-utensils"></i></span></div>
                            <input type="time" class="form-control" id="salida_colacion" name="salida_colacion" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="dia_salida">Día de Salida</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-day"></i></span></div>
                            <select class="form-control" id="dia_salida" name="dia_salida" required>
                                <option value="">Seleccione...</option>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="hora_fin_jornada">Hora de Fin</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-sign-out-alt"></i></span></div>
                            <input type="time" class="form-control" id="hora_fin_jornada" name="hora_fin_jornada" required>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="tolerancia">Tolerancia de Atraso</label>
                        <div class="input-group">
                             <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-hourglass-start"></i></span></div>
                            <input type="number" class="form-control" id="tolerancia" name="tolerancia" min="0" placeholder="Ej: 15" required>
                        </div>
                        <small class="form-text text-muted">Ingrese el tiempo de tolerancia en minutos.</small>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Crear Jornada</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<?php include_once "includes/footer.php"; ?>

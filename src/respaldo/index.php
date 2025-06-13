<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2|| $_SESSION['rol'] == 3) {
    include_once "includes/header.php";
    include "../conexion.php";
    // Verificar la contraseña al inicio
    }
    
?>

<!-- Modal para verificar la contraseña -->
<?php if (!isset($_SESSION['password_verified']) || !$_SESSION['password_verified']) { ?>
    <div class="modal fade" id="modalPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center" id="modalPasswordLabel">Verificar Garzón</h5>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <!-- Contraseña Input -->
                        <div class="mb-3 text-center">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="text" class="form-control w-50 mx-auto d-block text-center" id="password" name="password" readonly required>
                        </div>

                        <!-- Botones para ingresar los dígitos -->
                        <div class="row justify-content-center">
                            <?php for ($i = 1; $i <= 9; $i++) { ?>
                                <div class="col-4 mb-2">
                                    <button type="button" class="btn btn-light w-100 btn-lg" onclick="addDigit('<?php echo $i; ?>')"><?php echo $i; ?></button>
                                </div>
                            <?php } ?>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-light w-100 btn-lg" onclick="addDigit('0')">0</button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-danger w-100 btn-lg" onclick="clearPassword()">C</button>
                            </div>
                        </div>

                        <!-- Botón de verificación -->
                        <div class="mb-3 mt-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">Verificar</button>
                        </div>

                        <!-- Mensaje de error -->
                        <?php if (isset($error_message)) { ?>
                            <div class="text-danger text-center"><?php echo $error_message; ?></div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<!-- Contenido principal de la página -->

    <div class="card">
        <div class="card-header text-center">
            Salones
        </div>
        <div class="card-body">
            <div class="row">
                <?php
            
                $query = mysqli_query($conexion, "SELECT * FROM salas WHERE estado = 1");
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_assoc($query)) { ?>
                        <div class="col-md-3 shadow-lg">
                            <div class="col-12">
                                <img src="../assets/img/salones.jpeg" class="product-image" alt="Product Image">
                            </div>
                            <h6 class="my-3 text-center"><span class="badge badge-info"><?php echo $data['nombre']; ?></span></h6>

                            <div class="mt-4">
                                <a class="btn btn-primary btn-block btn-flat" href="mesas.php?id_sala=<?php echo $data['id']; ?>&mesas=<?php echo $data['mesas']; ?>">
                                    <i class="far fa-eye mr-2"></i>
                                    Ver Mesas
                                </a>
                            </div>
                        </div>
                        <?php } ?>    
            </div>
        </div>
    </div>

    <!-- Nueva sección para mostrar el nombre del garzón -->
    


<?php include_once "includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>


<?php
} else {
    header('Location: permisos.php');
}
?>

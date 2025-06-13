document.addEventListener("DOMContentLoaded", function () {
    //alert("js");
        if ($('#detalle_pedido').length > 0) {
            listar();
        }
        
        $('#tbl').DataTable({
            language: {
                "url": "//cdn.datatables.net/plug-ins/1.10.11/i18n/Spanish.json"
            },
            "order": [
                [0, "desc"]
            ]
        });
    
        $(".confirmar").submit(function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Esta seguro de eliminar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'SI, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        })
    
        $('.addDetalle').click(function () {
            let id_producto = $(this).data('id');
            registrarDetalle(id_producto);
        })
    
        //AQUI VA EL DE VENTA DIRECTA// lo deje en venta directa
        
        $('#realizar_pedido').click(function (e) {
            e.preventDefault();
        
            var action = 'procesarPedido';
            var id_sala = $('#id_sala').val();
            var mesa = $('#mesa').val();
            var observacion = $('#observacion').val();
            var cantidad_personas = $('#cantidad_personas').val();
        
            // Muestra los datos en la consola antes de enviarlos
            console.log({
                procesarPedido: action,
                id_sala: id_sala,
                mesa: mesa,
                observacion: observacion,
                cantidad_personas: cantidad_personas
            });
        
            $.ajax({
                url: 'ajax.php',
                async: true,
                data: {
                    procesarPedido: action,
                    id_sala: id_sala,
                    mesa: mesa,
                    observacion: observacion,
                    cantidad_personas: cantidad_personas,
                },
                success: function (response) {
                    const res = JSON.parse(response);
                    console.log('Respuesta del servidor:', res); // Muestra la respuesta en la consola
                    if (response != 'error') {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Pedido Realizado',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        setTimeout(() => {
                            window.location = 'dashboard.php?id_sala=0&mesas=0&mesa=1&prueba';
                        }, 1500);
                    } else {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error al generar',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },
                error: function (error) {
                    console.error('Error en la solicitud:', error); // Muestra el error en la consola
                    alert(error);
                }
            });
        });
        
    
        $('.finalizarPedido').click(function () {
            var action = 'finalizarPedido';
            var id_sala = $('#id_sala').val();
            var mesa = $('#mesa').val();
            var tipo_documento = $('#tipo_documento').val();
            var folio = $('#folio').val();
            var tipo_pago = $('#tipo_pago').val();		
            var monto_pago = $('#monto_pago').val();
            var total_pago = $('#total_pago').val();
            total_pago=total_pago.replace('$', '');
            total_pago=total_pago.replace(',', '');
            pago = monto_pago - total_pago;
    //alert(monto_pago+">="+total_pago);
    //alert(pago);
        if (pago>=0){	
            $.ajax({
                url: 'ajax.php',
                async: true,
                data: {
                    finalizarPedido: action,
                    id_sala: id_sala,
                    mesa: mesa,
                    tipo_documento: tipo_documento,
                    folio: folio,
                    tipo_pago: tipo_pago,
                    monto_pago: monto_pago,
                    total_pago: total_pago
                },
                success: function (response) {
                //alert(response);
                    const res = JSON.parse(response);
                    if (response != 'error') {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Pedido Finalizado',
                            showConfirmButton: false,
                            timer: 2000
                        })
                        setTimeout(() => {
                            //window.location = 'mesas.php?id_sala=' + id_sala + '&mesas=' + res.mensaje;
                            window.location = 'dashboard.php?id_sala=0&mesas=0&mesa=1&prueba';
    
                        }, 1500);
                    } else {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error al finalizar',
                            showConfirmButton: false,
                            timer: 2000
                        })
                    }
                },
                error: function (error) {
                    alert(error);
                }
            });
        }else{
            Swal.fire({
            position: 'center',
            icon: 'error',
            title: 'Monto Pago menor a Total',
            showConfirmButton: false,
            timer: 2000
            })
        }
        })
    })
    
    
    
    
    function registrarDetalle(id_pro) {
        var id_sala = $('#id_sala').val();
        var mesa = $('#mesa').val();
        let action = 'regDetalle';
        $.ajax({
            url: "ajax.php",
            type: 'POST',
            dataType: "json",
            data: {
                id: id_pro,
                id_sala: id_sala,
                id_mesa: mesa,
                regDetalle: action
            },
            success: function (response) {
                if (response == 'registrado') {
                    
                    listar();
                }
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Producto Agregado',
                    showConfirmButton: false,
                    timer: 1000
                })
            }
        });
    }
    
    function eliminarPlato(id) {
		alert(id);
        let detalle = 'Eliminar'
        $.ajax({
            url: "ajax.php",
            data: {
                id: id,
                delete_detalle: detalle
            },
            success: function (response) {
    
                if (response == 'ok') {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Producto Eliminado',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    listar();
                } else {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Error al Eliminar el producto',
                        showConfirmButton: false,
                        timer: 2000
                    })
                }
            }
        });
    }
    

    function listar() {
        let html = '';
        let detalle = 'detalle';
        var id_sala = $('#id_sala').val();
        var mesa = $('#mesa').val();
        
        //alert("listar");
        $.ajax({
            url: "ajax.php",
            dataType: "json",
            data: {
                id_sala: id_sala,
                mesa: mesa,
                detalle: detalle
            },
            success: function (response) {
                response.forEach(row => {
                    html += `<div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-12">
                                <img src="${ row.imagen }" width="70" height="70">
                            </div>
                            <p class="my-1">${row.nombre}</p>
                            <h6 class="mb-1">$ ${row.precio}</h6> 
                            <div class="mt-1">
                                <input type="number" class="form-control addCantidad mb-1" data-id="${row.id}" value="${row.cantidad}">
                                <button class="btn btn-danger btn-sm eliminarPlato" type="button" data-id="${row.id}">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                
                
                });
                document.querySelector("#detalle_pedido").innerHTML = html;
                $('.eliminarPlato').click(function () {
                    let id = $(this).data('id');
                    eliminarPlato(id);
                })
                $('.addCantidad').change(function (e) {
                    let id = $(this).data('id');
                    cantidadPlato(e.target.value, id);
                })
            }
        });
    }

    

    function cantidadPlato(cantidad, id) {
        //alert(id);
        let detalle = 'cantidad'
        $.ajax({
            url: "ajax.php",
            data: {
                id: id,
                cantidad: cantidad,
                detalle_cantidad: detalle
            },
            success: function (response) {
    
                if (response != 'ok') {
                    listar();
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Error al agregar cantidad',
                        showConfirmButton: false,
                        timer: 2000
                    })
                }
            }
        });
    }
    
    function btnCambiar(e) {
        e.preventDefault();
        const actual = document.getElementById('actual').value;
        const nueva = document.getElementById('nueva').value;
        if (actual == "" || nueva == "") {
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Los campos estan vacios',
                showConfirmButton: false,
                timer: 2000
            })
        } else {
            const cambio = 'pass';
            $.ajax({
                url: "ajax.php",
                type: 'POST',
                data: {
                    actual: actual,
                    nueva: nueva,
                    cambio: cambio
                },
                success: function (response) {
                    if (response == 'ok') {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Contraseña modificado',
                            showConfirmButton: false,
                            timer: 2000
                        })
                        document.querySelector('#frmPass').reset();
                        $("#nuevo_pass").modal("hide");
                    } else if (response == 'dif') {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'La contraseña actual incorrecta',
                            showConfirmButton: false,
                            timer: 2000
                        })
                    } else {
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Error al modificar la contraseña',
                            showConfirmButton: false,
                            timer: 2000
                        })
                    }
                }
            });
        }
    }
    
    function editarUsuario(id) {
        const action = "editarUsuario";
        $.ajax({
            url: 'ajax.php',
            type: 'GET',
            async: true,
            data: {
                editarUsuario: action,
                id: id
            },
            success: function (response) {
                const datos = JSON.parse(response);
                $('#nombre').val(datos.nombre);
                $('#rol').val(datos.rol);
                $('#correo').val(datos.correo);
                $('#id').val(datos.id);
                $('#btnAccion').val('Modificar');
            },
            error: function (error) {
                console.log(error);
    
            }
        });
    }
    
    function editarPlato(id) {
        const action = "editarProducto";
        $.ajax({
            url: 'ajax.php',
            type: 'GET',
            async: true,
            data: {
                editarProducto: action,
                id: id
            },
            success: function (response) {
                const datos = JSON.parse(response);
                $('#plato').val(datos.nombre);
                $('#precio').val(datos.precio);
                $('#foto_actual').val(datos.foto_actual);
                $('#id').val(datos.id);
                $('#btnAccion').val('Modificar');
            },
            error: function (error) {
                console.log(error);
    
            }
        });
    }
    
    function editarSalon(id) {
        alert (id);
        const action = "editarSalon";
        $.ajax({
            url: 'ajax.php',
            type: 'GET',
            async: true,
            data: {
                editarSalon: action,
                id: id
            },
            success: function (response) {
                const datos = JSON.parse(response);
                $('#nombre').val(datos.nombre);
                $('#mesas').val(datos.mesas);
                $('#id').val(datos.id);
                $('#btnAccion').val('Modificar');
            },
            error: function (error) {
                console.log(error);
    
            }
        });
    }
    
    function limpiar() {
        $('#formulario')[0].reset();
        $('#id').val('');
        $('#btnAccion').val('Registrar');
    }
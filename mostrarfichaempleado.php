<script>
function mostrarFichaEmpleado(idEmpleado) {
  $.ajax({
    url: 'obtener_empleado.php',
    type: 'GET',
    data: { id: idEmpleado },
    dataType: 'json',
    success: function(data) {
      $('#ficha_id').text(data.id);
      $('#ficha_nombre').text(data.nombre);
      $('#ficha_correo').text(data.correo);
      $('#ficha_tipocontrato').text(data.tipo_contrato);
      $('#editarModal').modal('show');
    },
    error: function() {
      alert('No se pudo obtener la información del empleado.');
    }
  });
}
</script>
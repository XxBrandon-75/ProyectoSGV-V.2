<?php if (isset($ver_cont_gest) && $ver_cont_gest): ?>


  <aside class="admin-panel">
    <h3>Panel de administración</h3>
    <button id="btn-agregar" class="btn-agregar">
      <i class="fa-solid fa-plus"></i> Agregar documento
    </button>
  </aside>

  <div id="formulario-panel" class="formulario-panel">
    <form id="form-documento" class="formulario">
      <h2 id="form-titulo">Agregar nuevo documento</h2>
      <input type="hidden" id="index-editar">

      <label for="titulo">Título del documento</label>
      <input type="text" id="titulo" required>

      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" rows="3"></textarea>

      <label for="archivo">Seleccionar archivo</label>
      <input type="file" id="archivo" accept=".pdf,.doc,.docx" required>

      <div class="form-buttons">
        <button type="submit" class="btn-guardar">Guardar</button>
        <button type="button" id="btn-cerrar" class="btn-cancelar">Cancelar</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
$documentacion_style = (isset($ver_cont_gest) && $ver_cont_gest) ? '' : 'style="margin-left: 0;"';
?>
<section class="documentos" <?php echo $documentacion_style; ?>>
  <h1>Documentos disponibles</h1>
  <div id="lista-documentos" class="lista-documentos">
    <p class="sin-documentos">No hay documentos registrados.</p>
  </div>
</section>
<script>
  const CAN_EDIT_CARDS = <?php echo (isset($ver_card_edit) && $ver_card_edit) ? 'true' : 'false'; ?>;
</script>
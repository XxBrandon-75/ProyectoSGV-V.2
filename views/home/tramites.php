<?php $base_url = '/ProyectoSGV/'; ?>
<?php if (isset($ver_cont_gest) && $ver_cont_gest): ?>


    <aside class="admin-panel">
      <h3>Panel de administración</h3>
      <button id="btn-agregar" class="btn-agregar">
        <i class="fa-solid fa-plus"></i> Agregar trámite
      </button>
    </aside>

    <div id="formulario-panel" class="formulario-panel">
      <form id="form-tramite" class="formulario">
        <h2 id="form-titulo">Agregar nuevo trámite</h2>
        <input type="hidden" id="index-editar">

        <label for="nombre">Nombre del trámite</label>
        <input type="text" id="nombre" required>

        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" rows="3" required></textarea>

        <label for="fecha_inicio">Fecha de inicio</label>
        <input type="date" id="fecha_inicio" required>

        <label for="fecha_corte">Fecha de corte</label>
        <input type="date" id="fecha_corte" required>

        <div class="requerimientos-admin">
          <h3>Requerimientos</h3>
          <div id="lista-requerimientos"></div>
          <div class="nuevo-requerimiento">
            <input type="text" id="nuevo-label" placeholder="Campo (ej. CURP)">
            <select id="nuevo-tipo">
              <option value="text">Texto</option>
              <option value="number">Número</option>
              <option value="file">Archivo</option>
            </select>
            <button type="button" id="btn-add-requerimiento">+</button>
          </div>
        </div>

        <div class="form-buttons">
          <button type="submit" class="btn-guardar">Guardar</button>
          <button type="button" id="btn-cerrar" class="btn-cancelar">Cancelar</button>
        </div>
      </form>
    </div>
    <?php endif; ?>

  <?php 
  $tramites_style = (isset($ver_cont_gest) && $ver_cont_gest) ? '' : 'style="margin-left: 0;"';
  ?>
    <section class="tramites" <?php echo $tramites_style; ?>>
      <h1>Trámites disponibles</h1>
      <div id="lista-tramites" class="lista-tramites">
        <p class="sin-tramites">No hay trámites registrados.</p>
      </div>
    </section>

    <div id="modal-solicitud" class="modal">
      <div class="modal-contenido">
        <span class="cerrar-modal" id="cerrar-modal">&times;</span>
        <h2 id="titulo-solicitud"></h2>
        <p id="descripcion-solicitud"></p>
        <p><strong>Inicio:</strong> <span id="inicio-solicitud"></span></p>
        <p><strong>Corte:</strong> <span id="corte-solicitud"></span></p>
        <form id="form-solicitud" class="form-solicitud"></form>
      </div>
    </div>

 <script>
    const CAN_EDIT_CARDS = <?php echo (isset($ver_card_edit) && $ver_card_edit) ? 'true' : 'false'; ?>;
</script>
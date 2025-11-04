<?php if (isset($ver_cont_gest) && $ver_cont_gest): ?>
  <!--PARA EL PANEL DE ADMINISTRADOR LUEGO LE PONGO UN BOTON PARA QUE SOLO APAREZCA SI ES ADMIN-->
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
            <option value="file">Archivo</option>
          </select>
          <button type="button" id="btn-add-requerimiento">+</button>
        </div>
      </div>
      <!--CONFIESO QUE ME CUESTA TRABAJO DORMIR YA TIENE RATO QUE DUERMO TARDE-->
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
    <!-- Los trámites se cargarán dinámicamente desde la BD -->
  </div>

  <h2 style="margin-top: 3rem; margin-bottom: 1rem;">
    <i class="fa-solid fa-circle-check"></i> Trámites completados
  </h2>
  <div id="lista-tramites-completados" class="lista-tramites">
    <p class="sin-tramites">No tienes trámites completados.</p>
  </div>
</section>

<script>
  const CAN_EDIT_CARDS = <?php echo (isset($ver_card_edit) && $ver_card_edit) ? 'true' : 'false'; ?>;
  const VOLUNTARIO_ID = <?php echo isset($voluntarioID) ? (int)$voluntarioID : 0; ?>;

  const TRAMITES_INICIALES = <?php echo json_encode($tramites ?? []); ?>;
</script>
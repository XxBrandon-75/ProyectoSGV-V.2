<?php if (isset($ver_cont_gest) && $ver_cont_gest): ?>


  <aside class="admin-panel">
    <h3>Panel de administración</h3>
    <button id="btn-agregar" class="btn-agregar">
      <i class="fa-solid fa-plus"></i> Agregar especialidad
    </button>
  </aside>

  <div id="formulario-panel" class="formulario-panel">
    <form id="form-especialidad" class="formulario">
      <h2 id="form-titulo">Agregar nueva especialidad</h2>
      <input type="hidden" id="index-editar">

      <div class="campo-imagen">
       <label for="imagen" class="label-imagen">
        <i class="fa-solid fa-image"></i> Imagen +
      </label>
      <input type="file" id="imagen" accept="image/*" style="display: none;">
      </div>

      <label for="nombre">Nombre</label>
      <input type="text" id="nombre" required>

      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" rows="3" required></textarea>

      <label for="fecha_apertura">Fecha de apertura</label>
      <input type="date" id="fecha_apertura" required>

      <label for="fecha_cierre">Fecha de cierre</label>
      <input type="date" id="fecha_cierre" required>

      <div class="requerimientos-admin">
        <h3>Requerimientos</h3>
        <div id="lista-requerimientos"></div>
        <div class="nuevo-requerimiento">
          <input type="text" id="nuevo-label" placeholder="Nombre del campo (ej. Edad)">
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
  $especialidades_style = (isset($ver_cont_gest) && $ver_cont_gest) ? '' : 'style="margin-left: 0;"';
  ?>
  <section class="especialidades" <?php echo $especialidades_style; ?>>
    <h1>Especialidades disponibles</h1>
    <div id="lista-especialidades" class="lista-especialidades">
      <p class="sin-cursos">No hay especialidades registradas.</p>
    </div>
  </section>

<div id="modal-inscripcion" class="modal" style="display:none;">
    <div class="modal-contenido">
      
      <button id="cerrar-modal" class="modal-cerrar" aria-label="Cerrar">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div class="modal-header">
        <img src="/ProyectoSGV/public/img/cruz_roja_logo.png" alt="Logo cruz roja">
        <h3 id="modal-titulo-fijo">Inscripción a Especialidad</h3>
      </div>
      <div class="modal-body">
        <h2 id="titulo-inscripcion"></h2>
        <p id="descripcion-inscripcion"></p>
        <div class="modal-datos">
          <p><strong>Inicio:</strong> <span id="inicio-inscripcion"></span></p>
          <p><strong>Cierre:</strong> <span id="cierre-inscripcion"></span></p>
        </div>
        <form id="form-inscripcion" class="form-inscripcion"></form>
      </div>
    </div>
</div>

<script>
  const CAN_EDIT_CARDS = <?php echo (isset($ver_card_edit) && $ver_card_edit) ? 'true' : 'false'; ?>;
</script>
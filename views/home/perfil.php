<?php $base_url = '/ProyectoSGV/'; ?>
    
    <aside class="perfil-aside">
      <h3>Panel de administración</h3>
      <ul class="perfil-menu">
        <li class="activo" data-section="perfil"><i class="fa-solid fa-id-card"></i> Mi perfil</li>
        <li data-section="solicitudes"><i class="fa-solid fa-file-pen"></i> Mis solicitudes</li>
        <li data-section="cargo"><i class="fa-solid fa-users"></i> A mi cargo</li>
        <li data-section="coordinadores"><i class="fa-solid fa-user-tie"></i> Coordinadores</li>
      </ul>
    </aside>

    <section class="perfil-contenido" id="perfil-contenido">
    </section>
    </main>

  <div id="modal-editar" class="modal">
    <div class="modal-content">
      <span id="cerrar-modal" class="cerrar-modal">&times;</span>
      <h2>Editar información</h2>
      <form id="form-editar">
        <label>Nombre</label>
        <input type="text" id="edit-nombre" />
        <label>Apellidos</label>
        <input type="text" id="edit-apellidos" />
        <label>Domicilio</label>
        <input type="text" id="edit-domicilio" />
        <label>Tipo de sangre</label>
        <input type="text" id="edit-sangre" />
        <button type="submit" class="btn-guardar">Guardar cambios</button>
      </form>
    </div>
  </div>
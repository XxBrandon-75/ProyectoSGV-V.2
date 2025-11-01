<aside class="perfil-aside">
    <div class="perfil-foto-container">
        <?php
        $fotoPerfil = isset($datosUsuario['FotoPerfil']) && $datosUsuario['FotoPerfil']
            ? $datosUsuario['FotoPerfil']
            : $base_url . 'public/img/perfiles/default.png';
        ?>
        <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" class="perfil-foto" id="img-perfil-preview">
        <?php if (isset($esPropioUsuario) && $esPropioUsuario): ?>
            <button type="button" class="btn-cambiar-foto" title="Cambiar foto de perfil" onclick="editarSeccion('foto_perfil', event)">
                <i class="fa-solid fa-camera"></i>
            </button>
        <?php endif; ?>
    </div>
    <h3>Panel de administración</h3>
    <ul class="perfil-menu">
        <li class="<?= ($paginaActual === 'perfil') ? 'activo' : '' ?>">
            <a href="<?= $base_url ?>index.php?controller=home&action=perfil<?= isset($datosUsuario['VoluntarioID']) ? '&id=' . $datosUsuario['VoluntarioID'] : '' ?>">
                <i class="fa-solid fa-id-card"></i> Mi perfil
            </a>
        </li>
        <li class="<?= ($paginaActual === 'especialidades') ? 'activo' : '' ?>">
            <a href="<?= $base_url ?>index.php?controller=home&action=especialidades<?= isset($datosUsuario['VoluntarioID']) ? '&id=' . $datosUsuario['VoluntarioID'] : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i> Especialidades
            </a>
        </li>
        <li class="<?= ($paginaActual === 'tramites') ? 'activo' : '' ?>">
            <a href="<?= $base_url ?>index.php?controller=home&action=tramites<?= isset($datosUsuario['VoluntarioID']) ? '&id=' . $datosUsuario['VoluntarioID'] : '' ?>">
                <i class="fa-solid fa-file-alt"></i> Trámites
            </a>
        </li>
        <li class="<?= ($paginaActual === 'documentacion') ? 'activo' : '' ?>">
            <a href="<?= $base_url ?>index.php?controller=home&action=documentacion<?= isset($datosUsuario['VoluntarioID']) ? '&id=' . $datosUsuario['VoluntarioID'] : '' ?>">
                <i class="fa-solid fa-folder-open"></i> Documentos
            </a>
        </li>
        <?php if ($puedeVerVoluntarios): ?>
            <li class="<?= ($paginaActual === 'personal') ? 'activo' : '' ?>">
                <a href="<?= $base_url ?>index.php?controller=home&action=personal">
                    <i class="fa-solid fa-users"></i> Personal
                </a>
            </li>
        <?php endif; ?>
        <?php if ($puedeEditarRol): ?>
            <li class="<?= ($paginaActual === 'coordinadores') ? 'activo' : '' ?>">
                <a href="<?= $base_url ?>index.php?controller=home&action=coordinadores">
                    <i class="fa-solid fa-user-tie"></i> Coordinadores
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>
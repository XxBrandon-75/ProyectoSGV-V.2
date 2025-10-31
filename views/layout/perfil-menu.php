<aside class="perfil-aside">
    <div class="perfil-foto-container" style="text-align:center; margin-bottom: 20px;">
        <?php
        $fotoPerfil = isset($datosUsuario['FotoPerfil']) && $datosUsuario['FotoPerfil']
            ? $datosUsuario['FotoPerfil']
            : $base_url . 'public\img\perfiles\default.png'; // sirve por si no hay foto
        ?>
        <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" class="perfil-foto">
    </div>
    <h3>Panel de administración</h3>
    <ul class="perfil-menu">
        <li class="<?= ($paginaActual === 'perfil') ? 'activo' : '' ?>">
            <a href="<?= $base_url ?>index.php?controller=home&action=perfil">
                <i class="fa-solid fa-id-card"></i> Mi perfil
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
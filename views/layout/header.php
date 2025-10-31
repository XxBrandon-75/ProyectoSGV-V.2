<?php

if (!isset($titulo_pagina)) {
      $titulo_pagina = "Red de Voluntarios";
}
if (!isset($styles)) {
      $styles = [];
}

// Detectar la ruta base automáticamente (compatible con Azure y proxies)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
      || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

$protocol = $isHttps ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $host . ($script != '/' ? $script : '') . '/';
?>
<!DOCTYPE html>
<html lang="es">

<head>
       
      <meta charset="UTF-8" />
       
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php echo $titulo_pagina; ?></title>

         
      <link rel="stylesheet" href="<?php echo $base_url; ?>public/css/g.style.css" />

          <?php foreach ($styles as $style_path): ?>
               
            <link rel="stylesheet" href="<?php echo $style_path; ?>" />
              <?php endforeach; ?>

          <script src="https://kit.fontawesome.com/910c92f415.js" crossorigin="anonymous"></script>
</head>

<body>
        <header>
                <div class="logo">
                        <img src="<?php echo $base_url; ?>public/icons/logo_cruz.png" alt="">
                        <h2>Red de voluntarios</h2>
                      </div>

                <nav class="barra-navegacion">
                  <a href="<?php echo $base_url; ?>index.php?controller=home&action=index">Inicio</a>
                      <a href="<?php echo $base_url; ?>index.php?controller=home&action=especialidades">Especialidades</a>
                      <a href="<?php echo $base_url; ?>index.php?controller=home&action=tramites">Trámites</a>
                        <a href="<?php echo $base_url; ?>index.php?controller=home&action=documentacion">Documentación</a>
                     
            </nav>

            <div class="menu-icons">
                  <a href="<?php echo $base_url; ?>index.php?controller=home&action=notificaciones">
                        <div id="notify" class="fa-solid fa-bell" style="color: #ffffff;"></div>
                  </a>
                  <div id="menu-bar" class="fa-solid fa-bars"></div>
                  <a href="<?php echo $base_url; ?>index.php?controller=home&action=perfil" style="display: flex; align-items: center; gap: 6px;">
                        <div id="user" class="fa-solid fa-user" style="color: #ffffff;"></div>
                        <span style="color: #fff; font-weight: 600; font-size: 1.25rem; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                              <?php echo isset($_SESSION['user']['nombre']) ? htmlspecialchars($_SESSION['user']['nombre']) : 'Usuario'; ?>
                        </span>
                  </a>
                  <a href="<?php echo $base_url; ?>logout.php" title="Cerrar sesión" style="display: flex; align-items: center; gap: 6px;">
                        <div id="logout" class="fa-solid fa-right-from-bracket" style="color: #ffffff;"></div>
                        <span style="color: #fff; font-weight: 600; font-size: 1.25rem; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">Salir</span>
                  </a>
            </div>
             
      </header>

      <!-- Overlay para el menú móvil -->
      <div id="nav-overlay" aria-hidden="true"></div>

        <main class="contenido-pagina">
</body>

</html>
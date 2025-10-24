<?php

if (!isset($titulo_pagina)) { $titulo_pagina = "Red de Voluntarios"; }
if (!isset($styles)) { $styles = []; }

$base_url = '/ProyectoSGV/'; 
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
      <a href="<?php echo $base_url; ?>index.php?controller=home&action=notificaciones"><div id="notify" class="fa-solid fa-bell" style="color: #ffffff;"></div></a>
      <div id="menu-bar" class="fa-solid fa-bars"></div>
      <a href="<?php echo $base_url; ?>index.php?controller=home&action=perfil"><div id="user" class="fa-solid fa-user" style="color: #ffffff;"></div></a>
    </div>
  </header>

  <main class="contenido-pagina">
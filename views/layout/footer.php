</main>

  <footer class="footer">
    <div class="inicio-footer">
      <div class="contenedor-inicio-footer">
        <h2>Contacto soporte:<span> Bryan Hernández Solís</span></h2>
        <a href="#">Teléfono</a>
        <a href="#">Correo</a>
        <a href="#">Dirección</a>
      </div>
      <div class="contenedor-inicio-footer">
        <h2>Contacto soporte:<span> Brandon Rodríguez Gutiérrez</span></h2>
        <a href="#">Teléfono</a>
        <a href="#">Correo</a>
        <a href="#">Dirección</a>
      </div>
      <div class="contenedor-inicio-footer">
        <h2>Contacto soporte:<span> Carlos Daniel Pardo Viramontes</span></h2>
        <a href="#">Teléfono</a>
        <a href="#">Correo</a>
        <a href="#">Dirección</a>
      </div>
    </div>
  </footer>

<?php $v = time(); ?> 

    <?php if (isset($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script_path): ?>
            <script src="<?php echo $script_path; ?>?v=<?php echo $v; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>

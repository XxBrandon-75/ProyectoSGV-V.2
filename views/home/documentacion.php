<?php
// Determinar si el usuario es administrador o superior
require_once __DIR__ . '/../../helpers/RolHelper.php';
$esAdmin = RolHelper::puedeVerCoordinadores(); // Admin o superior
$esVoluntario = !$esAdmin;
?>

<section class="documentos-voluntario">
  <h1><i class="fa-solid fa-folder-open"></i>
    <?php
    if (isset($verSoloUnVoluntario) && $verSoloUnVoluntario) {
      echo "Expediente de " . htmlspecialchars($nombreVoluntario);
    } else {
      echo "Mi Expediente de Documentos";
    }
    ?>
  </h1>
  <p class="descripcion-pagina">
    <?php if (isset($verSoloUnVoluntario) && $verSoloUnVoluntario): ?>
      Documentos del voluntario <strong><?= htmlspecialchars($nombreVoluntario) ?></strong>. Puedes revisar y aprobar los documentos subidos.
    <?php elseif ($esAdmin): ?>
      Gestiona los documentos del sistema. Puedes definir qué documentos son requeridos con o sin plantilla.
    <?php else: ?>
      Aquí encontrarás los documentos requeridos. Descarga las plantillas, rellénalas y súbelas para revisión.
    <?php endif; ?>
  </p>

  <?php if ($esAdmin && (!isset($verSoloUnVoluntario) || !$verSoloUnVoluntario)): ?>
    <button id="btn-agregar-nueva" class="btn-agregar-nueva">
      <i class="fa-solid fa-plus"></i> Definir Nuevo Tipo de Documento
    </button>
  <?php endif; ?>

  <div id="lista-documentos" class="lista-documentos">
    <div class="cargando-documentos">
      <i class="fa-solid fa-spinner fa-spin"></i> Cargando documentos...
    </div>
  </div>
</section>

<!-- Panel lateral para agregar/editar documento -->
<div id="formulario-panel" class="formulario-panel">
  <form id="form-documento" class="formulario" enctype="multipart/form-data">
    <h2 id="form-titulo">
      <i class="fa-solid fa-file-circle-plus"></i>
      <?php echo $esAdmin ? 'Definir Tipo de Documento' : 'Subir Documento Rellenado'; ?>
    </h2>

    <?php if ($esAdmin): ?>
      <!-- Formulario para Admin: Definir tipo de documento -->
      <label for="nombreDocumento">Nombre del Documento <span style="color: red;">*</span></label>
      <input type="text" id="nombreDocumento" name="nombreDocumento" placeholder="Ej: CURP, Carta Responsiva" required>

      <label for="tipoVer">Tipo de Documento <span style="color: red;">*</span></label>
      <select id="tipoVer" name="tipoVer" required onchange="togglePlantillaField(this.value)">
        <option value="Plantilla">Con Plantilla (subir archivo modelo)</option>
        <option value="Solo Subir">Solo Subir (sin plantilla)</option>
      </select>
      <small style="color: #666; display: block; margin-top: 0.5rem;">
        <strong>Con Plantilla:</strong> Los voluntarios descargarán el archivo, lo llenarán y subirán.<br>
        <strong>Solo Subir:</strong> Los voluntarios subirán sus propios archivos sin modelo previo.
      </small>

      <div class="requerimientos-admin" id="plantillaField">
        <h3><i class="fa-solid fa-file-arrow-up"></i> Archivo de Plantilla</h3>
        <p class="instruccion-req">
          Sube el documento en blanco (plantilla) que los voluntarios descargarán y rellenarán.
        </p>

        <label for="archivo">Seleccionar Archivo <span style="color: red;">*</span></label>
        <input type="file" id="archivo" name="archivo" accept=".pdf,application/pdf">
        <small style="color: #666; display: block; margin-top: 0.5rem;">
          Formato permitido: PDF (máx. 10MB)
        </small>
      </div>

      <script>
        function togglePlantillaField(tipoVer) {
          const plantillaField = document.getElementById('plantillaField');
          const archivoInput = document.getElementById('archivo');

          if (tipoVer === 'Plantilla') {
            plantillaField.style.display = 'block';
            archivoInput.required = true;
          } else {
            plantillaField.style.display = 'none';
            archivoInput.required = false;
            archivoInput.value = '';
          }
        }
      </script>
    <?php else: ?>
      <!-- Formulario para Voluntario: Subir Documento -->
      <input type="hidden" id="nombreDocumento" name="nombreDocumento">

      <div class="requerimientos-admin">
        <h3><i class="fa-solid fa-file-arrow-up"></i> Subir Documento Completado</h3>
        <p class="instruccion-req">
          Sube el documento ya rellenado para que sea revisado por un administrador.
        </p>

        <label for="archivo">Seleccionar Archivo <span style="color: red;">*</span></label>
        <input type="file" id="archivo" name="archivo" accept=".pdf,application/pdf" required>
        <small style="color: #666; display: block; margin-top: 0.5rem;">
          Formato permitido: PDF (máx. 10MB)
        </small>
      </div>
    <?php endif; ?>

    <div class="form-buttons">
      <button type="submit" class="btn-guardar">
        <i class="fa-solid fa-save"></i> Guardar Documento
      </button>
      <button type="button" id="btn-cancelar" class="btn-cancelar">
        <i class="fa-solid fa-times"></i> Cancelar
      </button>
    </div>
  </form>
</div>

<script>
  const ES_ADMIN = <?php echo $esAdmin ? 'true' : 'false'; ?>;
  const VOLUNTARIO_ID = <?php echo isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0; ?>;
  const VOLUNTARIO_FILTRO_ID = <?php echo isset($voluntarioFiltroID) ? (int)$voluntarioFiltroID : 'null'; ?>;
  const VER_SOLO_UN_VOLUNTARIO = <?php echo isset($verSoloUnVoluntario) && $verSoloUnVoluntario ? 'true' : 'false'; ?>;
</script>
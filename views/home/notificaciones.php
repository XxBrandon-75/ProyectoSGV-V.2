<?php

// Verificar que las variables existen (seguridad adicional)
if (!isset($esCoordinadorOMas)) {
    $esCoordinadorOMas = false;
}
if (!isset($notificacionesPendientes)) {
    $notificacionesPendientes = [];
}
if (!isset($totalPendientes)) {
    $totalPendientes = 0;
}
if (!isset($tramitesSolicitados)) {
    $tramitesSolicitados = [];
}
if (!isset($totalTramites)) {
    $totalTramites = 0;
}
if (!isset($expedientesPendientes)) {
    $expedientesPendientes = [];
}
if (!isset($totalExpedientes)) {
    $totalExpedientes = 0;
}
if (!isset($notificacionesGenerales)) {
    $notificacionesGenerales = [];
}
?>

<!-- El CSS ya se carga desde el header.php con n.style.css -->

<section class="notificaciones-container" id="notificaciones-container">
    <div class="notificaciones-header">
        <h2><i class="fa-solid fa-bell"></i> Notificaciones</h2>
        <div class="notificaciones-filtros">
            <button class="filtro-btn activo" data-filtro="todas">
                <i class="fa-solid fa-list"></i> Todas
                <?php if ($esCoordinadorOMas && ($totalPendientes + $totalTramites + $totalExpedientes + $totalEspecialidades) > 0): ?>
                    <span class="badge-contador"><?= ($totalPendientes + $totalTramites + $totalExpedientes + $totalEspecialidades) ?></span>
                <?php endif; ?>
            </button>
            <?php if ($esCoordinadorOMas): ?>
                <button class="filtro-btn" data-filtro="voluntarios">
                    <i class="fa-solid fa-user-clock"></i> Voluntarios
                    <?php if ($totalPendientes > 0): ?>
                        <span class="badge-contador"><?= $totalPendientes ?></span>
                    <?php endif; ?>
                </button>
                <button class="filtro-btn" data-filtro="tramites">
                    <i class="fa-solid fa-file-contract"></i> Trámites
                    <?php if ($totalTramites > 0): ?>
                        <span class="badge-contador"><?= $totalTramites ?></span>
                    <?php endif; ?>
                </button>
                <button class="filtro-btn" data-filtro="documentos">
                    <i class="fa-solid fa-file-invoice"></i> Documentos
                    <?php if ($totalExpedientes > 0): ?>
                        <span class="badge-contador"><?= $totalExpedientes ?></span>
                    <?php endif; ?>
                </button>
                <button class="filtro-btn" data-filtro="especialidades">
                    <i class="fa-solid fa-user-graduate"></i> Especialidades
                    <?php if ($totalEspecialidades > 0): ?>
                        <span class="badge-contador"><?= $totalEspecialidades ?></span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
            <button class="filtro-btn" data-filtro="leidas">
                <i class="fa-solid fa-check"></i> Leídas
            </button>
        </div>
    </div>

    <div class="notificaciones-contenido">

        <!-- ============================================================ -->
        <!-- SECCIÓN DE VOLUNTARIOS PENDIENTES -->
        <!-- ============================================================ -->
        <?php if ($esCoordinadorOMas && $totalPendientes > 0): ?>
            <div class="notificaciones-seccion voluntarios-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-user-plus"></i> Voluntarios Pendientes de Aprobación
                    <span class="badge-pendientes"><?= $totalPendientes ?></span>
                </h3>

                <div class="notificaciones-lista">
                    <?php foreach ($notificacionesPendientes as $notif): ?>
                        <div class="notificacion-card pendiente" data-id="<?= htmlspecialchars($notif['id']) ?>">
                            <div class="notificacion-icono pendiente-icono">
                                <i class="fa-solid fa-user-clock"></i>
                            </div>
                            <div class="notificacion-contenido">
                                <div class="notificacion-header">
                                    <h4><?= htmlspecialchars($notif['nombre']) ?></h4>
                                    <span class="notificacion-tiempo">
                                        <i class="fa-regular fa-clock"></i> Pendiente
                                    </span>
                                </div>
                                <p class="notificacion-mensaje">
                                    Nuevo registro de voluntario solicitando aprobación
                                </p>
                                <div class="notificacion-detalles">
                                    <span class="detalle-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        <?= htmlspecialchars($notif['email']) ?>
                                    </span>
                                    <?php if (isset($notif['delegacion'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($notif['delegacion']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($notif['area'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-briefcase"></i>
                                            <?= htmlspecialchars($notif['area']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="notificacion-acciones">
                                    <button class="btn-accion btn-aprobar" onclick="aprobarVoluntario(<?= $notif['id'] ?>)">
                                        <i class="fa-solid fa-check"></i> Aprobar
                                    </button>
                                    <button class="btn-accion btn-rechazar" onclick="rechazarVoluntario(<?= $notif['id'] ?>)">
                                        <i class="fa-solid fa-times"></i> Rechazar
                                    </button>
                                    <button class="btn-accion btn-ver" onclick="verDetalles(<?= $notif['id'] ?>)">
                                        <i class="fa-solid fa-eye"></i> Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($esCoordinadorOMas): ?>
            <div class="notificaciones-seccion voluntarios-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-check-circle"></i>
                    No hay voluntarios pendientes de aprobación
                </h3>
                <div class="mensaje-vacio">
                    <i class="fa-solid fa-user-check"></i>
                    <p>Todos los voluntarios han sido revisados.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- SECCIÓN DE TRÁMITES SOLICITADOS -->
        <!-- ============================================================ -->
        <?php if ($esCoordinadorOMas && $totalTramites > 0): ?>
            <div class="notificaciones-seccion tramites-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-file-contract"></i> Trámites Pendientes de Validación
                    <span class="badge-pendientes badge-tramites"><?= $totalTramites ?></span>
                </h3>

                <div class="notificaciones-lista">
                    <!-- AQUÍ VA EL CÓDIGO QUE ME DISTE -->
                    <?php foreach ($tramitesSolicitados as $tramite): ?>
                        <div class="notificacion-card tramite-card" data-id="<?= htmlspecialchars($tramite['SolicitudID']) ?>">
                            <div class="notificacion-icono tramite-icono">
                                <i class="fa-solid fa-file-contract"></i>
                            </div>
                            <div class="notificacion-contenido">
                                <div class="notificacion-header">
                                    <h4><?= htmlspecialchars($tramite['Nombres']) ?></h4>
                                    <span class="notificacion-tiempo">
                                        <i class="fa-regular fa-clock"></i>
                                        <?= date('d/m/Y', strtotime($tramite['FechaSolicitud'])) ?>
                                    </span>
                                </div>
                                <p class="notificacion-mensaje">
                                    <strong>Trámite:</strong> <?= htmlspecialchars($tramite['NombreTramite']) ?>
                                </p>
                                <div class="notificacion-detalles">
                                    <span class="detalle-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        <?= htmlspecialchars($tramite['email']) ?>
                                    </span>
                                    <span class="detalle-item">
                                        <i class="fa-solid fa-id-card"></i>
                                        <?= htmlspecialchars($tramite['curp']) ?>
                                    </span>
                                </div>
                                <div class="notificacion-acciones">
                                    <button class="btn-accion btn-aprobar" onclick="aprobarTramite(<?= $tramite['SolicitudID'] ?>)">
                                        <i class="fa-solid fa-check"></i> Aprobar
                                    </button>
                                    <button class="btn-accion btn-rechazar" onclick="rechazarTramite(<?= $tramite['SolicitudID'] ?>)">
                                        <i class="fa-solid fa-times"></i> Rechazar
                                    </button>
                                    <button class="btn-accion btn-ver" onclick="verDetallesTramite(<?= $tramite['SolicitudID'] ?>)">
                                        <i class="fa-solid fa-eye"></i> Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <!-- FIN DEL CÓDIGO QUE ME DISTE -->
                </div>
            </div>
        <?php elseif ($esCoordinadorOMas): ?>
            <div class="notificaciones-seccion tramites-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-check-circle"></i>
                    No hay trámites pendientes de validación
                </h3>
                <div class="mensaje-vacio">
                    <i class="fa-solid fa-file-check"></i>
                    <p>Todos los trámites han sido procesados.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- SECCIÓN DE DOCUMENTOS/EXPEDIENTES PENDIENTES -->
        <!-- ============================================================ -->
        <?php if ($esCoordinadorOMas && $totalExpedientes > 0): ?>
            <div class="notificaciones-seccion expedientes-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-file-invoice"></i> Documentos Pendientes de Aprobación
                    <span class="badge-pendientes badge-expedientes"><?= $totalExpedientes ?></span>
                </h3>

                <div class="notificaciones-lista">
                    <?php foreach ($expedientesPendientes as $doc): ?>
                        <div class="notificacion-card documento-pendiente">
                            <div class="notificacion-icono documento-icono">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <div class="notificacion-contenido">
                                <div class="notificacion-header">
                                    <h4><?= htmlspecialchars($doc['nombre'] ?? 'Sin nombre') ?></h4>
                                    <span class="notificacion-tiempo">
                                        <i class="fa-regular fa-clock"></i> Pendiente
                                    </span>
                                </div>
                                <p class="notificacion-mensaje">
                                    Documento: <strong><?= htmlspecialchars($doc['documento'] ?? 'Sin nombre') ?></strong>
                                </p>
                                <div class="notificacion-detalles">
                                    <?php if (!empty($doc['curp'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-id-card"></i>
                                            <?= htmlspecialchars($doc['curp']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($doc['rol'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-user-tag"></i>
                                            <?= htmlspecialchars($doc['rol']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($doc['fecha'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-calendar"></i>
                                            <?= date('d/m/Y', strtotime($doc['fecha'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="notificacion-acciones">
                                    <?php if (!empty($doc['ruta'])): ?>
                                        <a href="<?= htmlspecialchars($doc['ruta']) ?>"
                                            target="_blank"
                                            class="btn-accion btn-descargar">
                                            <i class="fa-solid fa-download"></i> Descargar
                                        </a>
                                    <?php endif; ?>
                                    <a href="index.php?controller=home&action=documentacion"
                                        class="btn-accion btn-ver">
                                        <i class="fa-solid fa-folder-open"></i> Ir a Documentación
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($esCoordinadorOMas): ?>
            <div class="notificaciones-seccion expedientes-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-check-circle"></i>
                    No hay documentos pendientes de aprobación
                </h3>
                <div class="mensaje-vacio">
                    <i class="fa-solid fa-file-check"></i>
                    <p>Todos los documentos han sido revisados.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- SECCIÓN DE ESPECIALIDADES PENDIENTES -->
        <!-- ============================================================ -->
        <?php if ($esCoordinadorOMas && $totalEspecialidades > 0): ?>
            <div class="notificaciones-seccion especialidades-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-user-graduate"></i> Especialidades Pendientes de Validación
                    <span class="badge-pendientes badge-especialidades"><?= $totalEspecialidades ?></span>
                </h3>

                <div class="notificaciones-lista">
                    <?php foreach ($especialidadesPendientes as $esp): ?>
                        <div class="notificacion-card especialidad-pendiente">
                            <div class="notificacion-icono especialidad-icono">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <div class="notificacion-contenido">
                                <div class="notificacion-header">
                                    <h4><?= htmlspecialchars($esp['nombre']) ?></h4>
                                    <span class="notificacion-tiempo">
                                        <i class="fa-regular fa-clock"></i> Pendiente
                                    </span>
                                </div>
                                <p class="notificacion-mensaje">
                                    <strong>Especialidad:</strong> <?= htmlspecialchars($esp['documento']) ?>
                                </p>
                                <div class="notificacion-detalles">
                                    <?php if (!empty($esp['curp'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-id-card"></i>
                                            <?= htmlspecialchars($esp['curp']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($esp['area'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-briefcase"></i>
                                            <?= htmlspecialchars($esp['area']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($esp['fecha'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-calendar"></i>
                                            <?= date('d/m/Y', strtotime($esp['fecha'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($esp['archivo'])): ?>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-file"></i>
                                            <?= htmlspecialchars($esp['archivo']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="notificacion-acciones">
                                    <button class="btn-accion btn-aprobar" onclick="aprobarEspecialidad(<?= $esp['voluntario_documento_id'] ?>)">
                                        <i class="fa-solid fa-check"></i> Aprobar
                                    </button>
                                    <button class="btn-accion btn-rechazar" onclick="rechazarEspecialidad(<?= $esp['voluntario_documento_id'] ?>)">
                                        <i class="fa-solid fa-times"></i> Rechazar
                                    </button>
                                    <a href="index.php?controller=home&action=especialidades&curp=<?= urlencode($esp['curp']) ?>"
                                        class="btn-accion btn-ver">
                                        <i class="fa-solid fa-graduation-cap"></i> Ir a Especialidades
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($esCoordinadorOMas): ?>
            <div class="notificaciones-seccion especialidades-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-check-circle"></i>
                    No hay especialidades pendientes de validación
                </h3>
                <div class="mensaje-vacio">
                    <i class="fa-solid fa-user-check"></i>
                    <p>Todas las especialidades han sido revisadas.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- SECCIÓN DE NOTIFICACIONES GENERALES -->
        <!-- ============================================================ -->
        <?php if (!empty($notificacionesGenerales)): ?>
            <div class="notificaciones-seccion generales-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-inbox"></i> Notificaciones Recientes
                </h3>

                <div class="notificaciones-lista">
                    <?php foreach ($notificacionesGenerales as $notif): ?>
                        <div class="notificacion-card <?= htmlspecialchars($notif['tipo']) ?>">
                            <div class="notificacion-icono <?= htmlspecialchars($notif['tipo']) ?>-icono">
                                <i class="fa-solid <?= htmlspecialchars($notif['icono']) ?>"></i>
                            </div>
                            <div class="notificacion-contenido">
                                <div class="notificacion-header">
                                    <h4><?= htmlspecialchars($notif['titulo']) ?></h4>
                                    <span class="notificacion-tiempo">
                                        <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($notif['tiempo']) ?>
                                    </span>
                                </div>
                                <p class="notificacion-mensaje">
                                    <?= htmlspecialchars($notif['mensaje']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Mensaje cuando no hay más notificaciones -->
                    <div class="notificacion-card vacia">
                        <div class="notificacion-icono vacia-icono">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <div class="notificacion-contenido">
                            <p class="notificacion-mensaje">
                                ¡Estás al día! No tienes más notificaciones pendientes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>
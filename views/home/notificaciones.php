<?php

// Verificar que las variables existen (seguridad adicional)
if (!isset($esCoordinadorOMas)) {
    $esCoordinadorOMas = false;
}
if (!isset($voluntariosPendientesArray)) {
    $voluntariosPendientesArray = [];
}
if (!isset($expedientesPendientesArray)) {
    $expedientesPendientesArray = [];
}
if (!isset($totalVoluntariosPendientes)) {
    $totalVoluntariosPendientes = 0;
}
if (!isset($totalExpedientesPendientes)) {
    $totalExpedientesPendientes = 0;
}
if (!isset($notificacionesGenerales)) {
    $notificacionesGenerales = [];
}

// Total combinado para el badge
$totalPendientes = $totalVoluntariosPendientes + $totalExpedientesPendientes;
?>

<!-- El CSS ya se carga desde el header.php con n.style.css -->

<section class="notificaciones-container" id="notificaciones-container">
    <div class="notificaciones-header">
        <h2><i class="fa-solid fa-bell"></i> Notificaciones</h2>
        <div class="notificaciones-filtros">
            <button class="filtro-btn activo" data-filtro="todas">
                <i class="fa-solid fa-list"></i> Todas
            </button>
            <?php if ($esCoordinadorOMas): ?>
                <button class="filtro-btn" data-filtro="pendientes">
                    <i class="fa-solid fa-user-clock"></i> Pendientes
                    <?php if ($totalPendientes > 0): ?>
                        <span class="badge-contador"><?= $totalPendientes ?></span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
            <button class="filtro-btn" data-filtro="leidas">
                <i class="fa-solid fa-check"></i> Leídas
            </button>
        </div>
    </div>

    <div class="notificaciones-contenido">

        <?php if ($esCoordinadorOMas): ?>
            <!-- Sección de Voluntarios Pendientes -->
            <?php if ($totalVoluntariosPendientes > 0): ?>
                <div class="notificaciones-seccion pendientes-seccion">
                    <h3 class="seccion-titulo">
                        <i class="fa-solid fa-user-plus"></i> Voluntarios Pendientes de Aprobación
                        <span class="badge-pendientes"><?= $totalVoluntariosPendientes ?></span>
                    </h3>

                    <div class="notificaciones-lista">
                        <?php foreach ($voluntariosPendientesArray as $notif): ?>
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
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-map-marker-alt"></i>
                                            <?= isset($notif['delegacion']) && $notif['delegacion'] ? htmlspecialchars($notif['delegacion']) : 'Sin asignar' ?>
                                        </span>
                                        <span class="detalle-item">
                                            <i class="fa-solid fa-briefcase"></i>
                                            <?= isset($notif['area']) && $notif['area'] ? htmlspecialchars($notif['area']) : 'Sin asignar' ?>
                                        </span>
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
            <?php endif; ?>

            <!-- Sección de Expedientes Pendientes -->
            <?php if ($totalExpedientesPendientes > 0): ?>
                <div class="notificaciones-seccion pendientes-seccion expedientes-seccion">
                    <h3 class="seccion-titulo">
                        <i class="fa-solid fa-file-invoice"></i> Documentos Pendientes de Aprobación
                        <span class="badge-pendientes"><?= $totalExpedientesPendientes ?></span>
                    </h3>

                    <div class="notificaciones-lista">
                        <?php foreach ($expedientesPendientesArray as $notif): ?>
                            <div class="notificacion-card pendiente expediente" data-tipo="expediente">
                                <div class="notificacion-icono pendiente-icono">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div class="notificacion-contenido">
                                    <div class="notificacion-header">
                                        <h4><?= htmlspecialchars($notif['nombre']) ?></h4>
                                        <span class="notificacion-tiempo">
                                            <i class="fa-regular fa-clock"></i> Pendiente
                                        </span>
                                    </div>
                                    <p class="notificacion-mensaje">
                                        Documento subido: <strong><?= htmlspecialchars($notif['documento']) ?></strong>
                                    </p>
                                    <div class="notificacion-detalles">
                                        <?php if (!empty($notif['curp'])): ?>
                                            <span class="detalle-item">
                                                <i class="fa-solid fa-id-card"></i>
                                                <?= htmlspecialchars($notif['curp']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($notif['rol'])): ?>
                                            <span class="detalle-item">
                                                <i class="fa-solid fa-briefcase"></i>
                                                <?= htmlspecialchars($notif['rol']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notificacion-acciones">
                                        <?php
                                        // Construir URL con filtro de voluntario si existe el ID
                                        $urlDocumentacion = 'index.php?controller=home&action=documentacion';
                                        if (!empty($notif['voluntario_id'])) {
                                            $urlDocumentacion .= '&voluntarioId=' . htmlspecialchars($notif['voluntario_id']);
                                        }
                                        ?>
                                        <a href="<?= $urlDocumentacion ?>" class="btn-accion btn-ver">
                                            <i class="fa-solid fa-folder-open"></i> Ver Expediente
                                        </a>
                                        <?php if (!empty($notif['ruta'])): ?>
                                            <a href="<?= htmlspecialchars($notif['ruta']) ?>" target="_blank" class="btn-accion btn-descargar">
                                                <i class="fa-solid fa-download"></i> Descargar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Mensaje cuando no hay pendientes -->
            <?php if ($totalVoluntariosPendientes == 0 && $totalExpedientesPendientes == 0): ?>
                <div class="notificaciones-seccion pendientes-seccion">
                    <h3 class="seccion-titulo">
                        <i class="fa-solid fa-check-circle"></i>
                        No hay solicitudes pendientes de aprobación
                    </h3>
                    <div class="mensaje-vacio">
                        <i class="fa-solid fa-user-check"></i>
                        <p>Todos los voluntarios y documentos han sido revisados.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Sección de Notificaciones Generales -->
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
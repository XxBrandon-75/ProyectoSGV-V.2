<?php
// $base_url se define en header.php
$rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
$esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

// Datos de ejemplo de voluntarios pendientes de aprobación
$notificacionesPendientes = [
    [
        'id' => 1,
        'nombre' => 'María González López',
        'delegacion' => 'Delegación Tuxtla Gutiérrez',
        'area' => 'Educación',
        'fecha_registro' => '2025-10-22 14:30:00',
        'tiempo' => 'Hace 1 día'
    ],
    [
        'id' => 2,
        'nombre' => 'Carlos Hernández Pérez',
        'delegacion' => 'Delegación San Cristóbal',
        'area' => 'Salud',
        'fecha_registro' => '2025-10-23 09:15:00',
        'tiempo' => 'Hace 3 horas'
    ],
    [
        'id' => 3,
        'nombre' => 'Ana Patricia Ruiz',
        'delegacion' => 'Delegación Comitán',
        'area' => 'Cultura',
        'fecha_registro' => '2025-10-23 11:45:00',
        'tiempo' => 'Hace 45 minutos'
    ],
    [
        'id' => 4,
        'nombre' => 'José Luis Martínez',
        'delegacion' => 'Delegación Tapachula',
        'area' => 'Deportes',
        'fecha_registro' => '2025-10-21 16:20:00',
        'tiempo' => 'Hace 2 días'
    ]
];

// Notificaciones generales para todos
$notificacionesGenerales = [
    [
        'tipo' => 'info',
        'titulo' => 'Actualización del sistema',
        'mensaje' => 'Se han implementado mejoras en el sistema de gestión de voluntarios.',
        'tiempo' => 'Hace 2 horas',
        'icono' => 'fa-circle-info'
    ],
    [
        'tipo' => 'evento',
        'titulo' => 'Próxima reunión de coordinadores',
        'mensaje' => 'La reunión mensual de coordinadores será el próximo viernes a las 10:00 AM.',
        'tiempo' => 'Hace 5 horas',
        'icono' => 'fa-calendar'
    ]
];
?>

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
                    <span class="badge-contador"><?= count($notificacionesPendientes) ?></span>
                </button>
            <?php endif; ?>
            <button class="filtro-btn" data-filtro="leidas">
                <i class="fa-solid fa-check"></i> Leídas
            </button>
        </div>
    </div>

    <div class="notificaciones-contenido">

        <?php if ($esCoordinadorOMas && count($notificacionesPendientes) > 0): ?>
            <!-- Sección de Aprobaciones Pendientes -->
            <div class="notificaciones-seccion pendientes-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa-solid fa-user-plus"></i> Voluntarios Pendientes de Aprobación
                    <span class="badge-pendientes"><?= count($notificacionesPendientes) ?></span>
                </h3>

                <div class="notificaciones-lista">
                    <?php foreach ($notificacionesPendientes as $notif): ?>
                        <div class="notificacion-card pendiente" data-id="<?= $notif['id'] ?>">
                            <div class="notificacion-icono pendiente-icono">
                                <i class="fa-solid fa-user-clock"></i>
                            </div>
                            <div class="notificacion-contenido">
                                <div class="notificacion-header">
                                    <h4><?= htmlspecialchars($notif['nombre']) ?></h4>
                                    <span class="notificacion-tiempo">
                                        <i class="fa-regular fa-clock"></i> <?= $notif['tiempo'] ?>
                                    </span>
                                </div>
                                <p class="notificacion-mensaje">
                                    Nuevo registro de voluntario solicitando aprobación
                                </p>
                                <div class="notificacion-detalles">
                                    <span class="detalle-item">
                                        <i class="fa-solid fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($notif['delegacion']) ?>
                                    </span>
                                    <span class="detalle-item">
                                        <i class="fa-solid fa-briefcase"></i>
                                        <?= htmlspecialchars($notif['area']) ?>
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

        <!-- Sección de Notificaciones Generales -->
        <div class="notificaciones-seccion generales-seccion">
            <h3 class="seccion-titulo">
                <i class="fa-solid fa-inbox"></i> Notificaciones Recientes
            </h3>

            <div class="notificaciones-lista">
                <?php foreach ($notificacionesGenerales as $notif): ?>
                    <div class="notificacion-card <?= $notif['tipo'] ?>">
                        <div class="notificacion-icono <?= $notif['tipo'] ?>-icono">
                            <i class="fa-solid <?= $notif['icono'] ?>"></i>
                        </div>
                        <div class="notificacion-contenido">
                            <div class="notificacion-header">
                                <h4><?= htmlspecialchars($notif['titulo']) ?></h4>
                                <span class="notificacion-tiempo">
                                    <i class="fa-regular fa-clock"></i> <?= $notif['tiempo'] ?>
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

    </div>
</section>

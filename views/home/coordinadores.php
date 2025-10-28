<?php
// Definir página actual para el menú
$paginaActual = 'coordinadores';
// Incluir menú lateral
require_once __DIR__ . '/../layout/perfil-menu.php';
?>

<section class="perfil-contenido" id="perfil-contenido">
    <div class="seccion-contenido activa">
        <h2>Gestión de Coordinadores</h2>
        <p class="seccion-descripcion">Listado de todos los coordinadores del sistema.</p>

        <?php if (empty($coordinadores)): ?>
            <div class="mensaje-vacio">
                <i class="fa-solid fa-user-tie-slash"></i>
                <p>No hay coordinadores registrados actualmente.</p>
            </div>
        <?php else: ?>
            <div class="tabla-container">
                <table class="tabla-voluntarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre completo</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Área</th>
                            <th>Delegación</th>
                            <th>Rol</th>
                            <th>Estatus</th>
                            <th>Fecha registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coordinadores as $coord): ?>
                            <tr>
                                <td><?= htmlspecialchars($coord['VoluntarioID']) ?></td>
                                <td><?= htmlspecialchars(trim(($coord['Nombres'] ?? '') . ' ' . ($coord['ApellidoPaterno'] ?? '') . ' ' . ($coord['ApellidoMaterno'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars($coord['Email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($coord['TelefonoCelular'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($coord['AreaNombre'] ?? 'Sin área') ?></td>
                                <td><?= htmlspecialchars($coord['DelegacionNombre'] ?? 'Sin delegación') ?></td>
                                <td><span class="badge-rol badge-coordinador"><?= htmlspecialchars($coord['RolNombre'] ?? 'Coordinador') ?></span></td>
                                <td><span class="badge-estatus estatus-<?= strtolower($coord['EstatusNombre'] ?? 'inactivo') ?>"><?= htmlspecialchars($coord['EstatusNombre'] ?? 'Inactivo') ?></span></td>
                                <td><?= isset($coord['FechaRegistro']) ? date('d/m/Y', strtotime($coord['FechaRegistro'])) : 'N/A' ?></td>
                                <td>
                                    <div class="acciones-container">
                                        <button class="btn-accion btn-ver" onclick="verDetallesCoordinador(<?= $coord['VoluntarioID'] ?>)" title="Ver detalles">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>Ver</span>
                                        </button>
                                        <a href="<?= $base_url ?>index.php?controller=home&action=perfil&id=<?= $coord['VoluntarioID'] ?>" class="btn-editar" title="Editar perfil">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tabla-info">
                <p>Total de coordinadores: <strong><?= count($coordinadores) ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal para ver/editar detalles del coordinador -->
<div id="modal-detalles" class="modal">
    <div class="modal-content modal-grande">
        <span class="cerrar-modal" onclick="cerrarModalDetalles()">&times;</span>
        <h2 id="modal-titulo-detalles">Detalles del Coordinador</h2>
        <div id="contenido-detalles" class="loading">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Cargando información...</p>
        </div>
    </div>
</div>
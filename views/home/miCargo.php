<?php
$paginaActual = 'miCargo';
require_once __DIR__ . '/../layout/perfil-menu.php';
?>

<section class="perfil-contenido" id="perfil-contenido">
    <div class="seccion-contenido activa">
        <h2>Voluntarios a mi cargo</h2>
        <p class="seccion-descripcion">Delegación: <strong><?= htmlspecialchars($datosCoordinador['DelegacionNombre'] ?? 'No asignada') ?></strong></p>

        <?php if (empty($voluntariosCargo)): ?>
            <div class="mensaje-vacio">
                <i class="fa-solid fa-users-slash"></i>
                <p>No hay voluntarios asignados a tu delegación actualmente.</p>
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
                            <th>Rol</th>
                            <th>Estatus</th>
                            <th>Fecha registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($voluntariosCargo as $vol): ?>
                            <tr>
                                <td><?= htmlspecialchars($vol['VoluntarioID']) ?></td>
                                <td><?= htmlspecialchars(trim(($vol['Nombres'] ?? '') . ' ' . ($vol['ApellidoPaterno'] ?? '') . ' ' . ($vol['ApellidoMaterno'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars($vol['Email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($vol['TelefonoCelular'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($vol['AreaNombre'] ?? 'Sin área') ?></td>
                                <td><span class="badge-rol"><?= htmlspecialchars($vol['RolNombre'] ?? 'Voluntario') ?></span></td>
                                <td><span class="badge-estatus estatus-<?= strtolower($vol['EstatusNombre'] ?? 'inactivo') ?>"><?= htmlspecialchars($vol['EstatusNombre'] ?? 'Inactivo') ?></span></td>
                                <td><?= isset($vol['FechaRegistro']) ? date('d/m/Y', strtotime($vol['FechaRegistro'])) : 'N/A' ?></td>
                                <td>
                                    <div class="acciones-container">
                                        <button class="btn-accion btn-ver" onclick="verDetallesVoluntario(<?= $vol['VoluntarioID'] ?>)" title="Ver detalles">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>Ver</span>
                                        </button>
                                        <a href="<?= $base_url ?>index.php?controller=home&action=perfil&id=<?= $vol['VoluntarioID'] ?>" class="btn-editar" title="Editar perfil">
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
                <p>Total de voluntarios: <strong><?= count($voluntariosCargo) ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal para ver detalles del voluntario -->
<div id="modal-detalles" class="modal">
    <div class="modal-content modal-grande">
        <span class="cerrar-modal" onclick="cerrarModalDetalles()">&times;</span>
        <h2 id="modal-titulo-detalles">Detalles del Voluntario</h2>
        <div id="contenido-detalles" class="loading">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Cargando información...</p>
        </div>
    </div>
</div>
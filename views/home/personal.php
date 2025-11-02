<?php
$paginaActual = 'personal';
require_once __DIR__ . '/../layout/perfil-menu.php';
?>

<section class="perfil-contenido" id="perfil-contenido"
    data-perfil-config='<?= json_encode([
                            "camposEditables" => ["FotoPerfil"],
                            "puedeModificar" => true,
                            "esAdmin" => $puedeEditarRol,
                            "esPropioUsuario" => true,
                            "datosUsuario" => [
                                "VoluntarioID" => $datosCoordinador['VoluntarioID'] ?? null,
                                "Nombres" => $datosCoordinador['Nombres'] ?? '',
                                "ApellidoPaterno" => $datosCoordinador['ApellidoPaterno'] ?? '',
                                "ApellidoMaterno" => $datosCoordinador['ApellidoMaterno'] ?? '',
                                "FotoPerfil" => $datosCoordinador['FotoPerfil'] ?? '',
                                "Email" => $datosCoordinador['Email'] ?? ''
                            ],
                            "rolUsuarioActual" => $_SESSION['user']['rol_id'] ?? 1,
                            "idUsuarioActual" => (int)$_SESSION['user']['id'],
                            "catCiudades" => $catCiudades ?? [],
                            "catEstados" => $catEstados ?? []
                        ]) ?>'>
    <div class="seccion-contenido activa">
        <h2>Voluntarios Personal</h2>
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
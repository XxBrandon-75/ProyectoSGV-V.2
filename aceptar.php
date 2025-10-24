<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobación de Voluntarios</title>
    <style>
        /* (Pega aquí los estilos del primer diseño que te di) */
        body { font-family: sans-serif; margin: 40px; background-color: #f8f9fa; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #0056b3; }
        .voluntario-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; }
        .voluntario-info { flex-grow: 1; }
        .voluntario-acciones { display: flex; flex-direction: column; gap: 15px; min-width: 320px; }
        form { display: flex; gap: 10px; align-items: center; }
        .motivo-textarea { flex-grow: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
        .btn-aprobar { background-color: #28a745; }
        .btn-rechazar { background-color: #dc3545; }
        .notificacion { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .notificacion-exito { background-color: #d4edda; color: #155724; }
        .notificacion-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-users-cog"></i> Gestión de Voluntarios Pendientes</h1>

    <?php
    // 1. INCLUIMOS EL CONTROLADOR
    include_once 'controllers/voluntariocontroller.php';
    $voluntarioController = new voluntariocontroller();

    // Asumimos que el ID del administrador se obtiene de una sesión
    $id_admin_actual = 5; 

    // 2. PROCESAMOS LAS ACCIONES (APROBAR/RECHAZAR) SI SE ENVIÓ UN FORMULARIO
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_voluntario_accion = $_POST['voluntario_id'];

        if (isset($_POST['aprobar'])) {
            // Futuro: $voluntarioController->aprobar($id_voluntario_accion, $id_admin_actual);
            
            echo "<div class='notificacion notificacion-exito'>Voluntario con ID $id_voluntario_accion ha sido APROBADO (simulación).</div>";
        
        } elseif (isset($_POST['rechazar'])) {
            $motivo = $_POST['motivo_rechazo'];
            if (!empty($motivo)) {
                // Futuro: $voluntarioController->rechazar($id_voluntario_accion, $id_admin_actual, $motivo);
                echo "<div class='notificacion notificacion-error'>Voluntario con ID $id_voluntario_accion ha sido RECHAZADO (simulación). Motivo: $motivo</div>";
            } else {
                 echo "<div class='notificacion notificacion-error'>El motivo de rechazo es obligatorio.</div>";
            }
        }
    }

    // 3. OBTENEMOS LA LISTA DE VOLUNTARIOS PENDIENTES DESDE EL CONTROLADOR
    $respuesta = $voluntarioController->pendienteAprobacion();

    // 4. MOSTRAMOS LOS DATOS BASADO EN LA RESPUESTA
    if ($respuesta['success']) {
        
        // --- Bucle para mostrar cada voluntario pendiente ---
        foreach ($respuesta['voluntarios'] as $voluntario) {
    ?>
            <div class="voluntario-card">
                <div class="voluntario-info">
                    <h4><?php echo htmlspecialchars($voluntario['Nombres'] . ' ' . $voluntario['ApellidoPaterno']); ?></h4>
                    <p>
                        <strong>ID:</strong> <?php echo $voluntario['VoluntarioID']; ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($voluntario['Email']); ?>
                    </p>
                </div>
                
                <div class="voluntario-acciones">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="voluntario_id" value="<?php echo $voluntario['VoluntarioID']; ?>">
                        <button type="submit" name="aprobar" class="btn btn-aprobar">Aprobar</button>
                    </form>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="voluntario_id" value="<?php echo $voluntario['VoluntarioID']; ?>">
                        <textarea name="motivo_rechazo" class="motivo-textarea" placeholder="Explica el motivo del rechazo..." required></textarea>
                        <button type="submit" name="rechazar" class="btn btn-rechazar">Rechazar</button>
                    </form>
                </div>
            </div>
    <?php
        } // Fin del bucle
    } else {
        // Si no hay voluntarios, mostramos el mensaje del controlador
        echo "<p>" . htmlspecialchars($respuesta['message']) . "</p>";
    }
    ?>
</div>

</body>
</html>
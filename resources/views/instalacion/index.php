<?php
/**
 * EduAlert JJP - resources/views/instalacion/index.php
 * Asistente de instalación en 2 pasos
 */
?>
<div class="auth-card" style="max-width:560px">
    <div class="auth-body">
        <div class="auth-logo">
            <div class="brand-icon"><i class="bi bi-magic"></i></div>
        </div>
        <div class="auth-title" style="font-size:1.25rem">Instalación de <span>EduAlert JJP</span></div>
        <div class="auth-subtitle mb-3">Sistema de Alerta Temprana Académica · I.E. Juan Jiménez Pimentel</div>

        <div class="progress mb-4" style="height:8px">
            <div class="progress-bar bg-success" style="width:<?= empty($pasos['base']) && empty($pasos['demo']) ? '15' : ($pasos['base'] && $pasos['demo'] ? '100' : '55') ?>%"></div>
        </div>

        <?php if (!$pasos['base']): ?>
            <div class="card border mb-3">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-database me-2 text-accent"></i>Paso 1: Crear base de datos</h6>
                    <p class="fs-7 text-muted mb-3">
                        Se creará la base <b>edualert_jjp</b> con todas sus tablas (25), roles, permisos,
                        usuarios demo, grados, secciones, periodos y cursos.
                    </p>
                    <form method="post" action="<?= url('instalacion/crear-bd') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-accent w-100">
                            <i class="bi bi-database-add me-2"></i>Crear base de datos y estructura
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><b>Base de datos creada.</b> Estructura y datos base instalados correctamente.
            </div>
        <?php endif; ?>

        <?php if ($pasos['base'] && !$pasos['demo']): ?>
            <div class="card border mb-3">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-stars me-2 text-warning"></i>Paso 2: Cargar datos de demostración</h6>
                    <p class="fs-7 text-muted mb-3">
                        Genera <b>200 estudiantes</b> peruanos con matrículas, calificaciones (4 periodos),
                        asistencias, docentes y ejecuta el <b>motor de alertas</b> automáticamente.
                        Ideal para la sustentación de tu tesis. Los datos son 100% demo.
                    </p>
                    <form method="post" action="<?= url('instalacion/datos-demo') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="bi bi-lightning-charge-fill me-2"></i>Generar datos demo (200 estudiantes)
                        </button>
                    </form>
                </div>
            </div>
        <?php elseif ($pasos['demo']): ?>
            <div class="alert alert-warning">
                <i class="bi bi-check2-circle me-2"></i><b>Datos demo cargados.</b> 200 estudiantes con alertas generadas.
                Puedes eliminarlos después desde <b>Configuración → Limpiar datos demo</b>.
            </div>
        <?php endif; ?>

        <?php if ($pasos['base']): ?>
            <a href="<?= url('login') ?>" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ir a Iniciar sesión
            </a>
        <?php endif; ?>
    </div>
    <div class="auth-footer">
        Usuarios demo: admin@edualert.test / director@edualert.test / docente@edualert.test · Contraseña: 12345678
    </div>
</div>
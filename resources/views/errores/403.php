<?php
/** EduAlert JJP - Acceso denegado (403) */
?>
<div class="row justify-content-center py-5">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-5 px-4">
                <div class="display-1 fw-bold text-danger mb-3">403</div>
                <div class="empty-state">
                    <i class="bi bi-shield-lock"></i>
                    <h4 class="fw-bold text-primary">Acceso denegado</h4>
                    <p class="mb-4">No tiene permisos suficientes para acceder a este módulo.<br>Si considera que esto es un error, contacte al administrador del sistema.</p>
                    <a href="<?= url('dashboard') ?>" class="btn btn-accent">
                        <i class="bi bi-arrow-left me-2"></i>Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
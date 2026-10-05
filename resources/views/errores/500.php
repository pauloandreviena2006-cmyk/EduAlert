<?php
/** EduAlert JJP - Error interno (500) */
?>
<div class="guest-body" style="min-height:100vh">
    <div class="d-flex align-items-center justify-content-center flex-column text-center" style="min-height:100vh; padding:24px">
        <div class="auth-logo mb-3">
            <div class="brand-icon"><i class="bi bi-bell-fill"></i></div>
        </div>
        <div class="display-1 fw-bold" style="color:#e11d48">500</div>
        <h4 class="fw-bold text-white mt-2">Ocurrió un error interno</h4>
        <p class="text-white-50 mb-4">Se ha registrado el incidente. Intente nuevamente en unos instantes.<br>Si el problema persiste, contacte al administrador.</p>
        <a href="<?= url('login') ?>" class="btn btn-accent"><i class="bi bi-arrow-clockwise me-2"></i>Volver a intentar</a>
    </div>
</div>
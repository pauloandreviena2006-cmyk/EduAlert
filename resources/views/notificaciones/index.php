<?php
/**
 * EduAlert JJP - recursos/views/notificaciones/index.php
 */
$tipos = [
    'alerta' => 'bg-danger',
    'seguimiento' => 'bg-primary',
    'info' => 'bg-secondary',
    'sistema' => 'bg-accent',
];
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-bell me-2 text-warning"></i>Notificaciones</h2>
        <p class="text-muted mb-0 fs-7"><?= (int)$no_leidas > 0 ? 'Tiene <b class="text-danger">' . (int)$no_leidas . ' sin leer</b>' : 'No tiene notificaciones sin leer' ?></p>
    </div>
    <div class="col-auto">
        <?php if ($no_leidas > 0): ?>
            <form method="post" action="<?= url('notificaciones/marcar-todas') ?>" data-confirm='{"titulo":"Marcar todas como leídas","texto":"¿Desea marcar todas las notificaciones como leídas?"}' class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-accent"><i class="bi bi-check2-all me-1"></i>Marcar todas</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="col-12 col-lg-9 mx-auto">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($notificaciones)): ?>
                <div class="empty-state p-5"><i class="bi bi-bell-slash"></i>No tiene notificaciones todavía.<br><span class="fs-8 text-muted">Recibirá avisos cuando se generen alertas y seguimientos.</span></div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($notificaciones as $n): ?>
                        <li class="list-group-item px-4 py-3 notif-item <?= (int)$n['leido'] === 0 ? 'bg-accent-subtle' : '' ?>" data-id="<?= (int)$n['id'] ?>">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;background:<?= in_array($n['tipo'], ['alerta', 'seguimiento', 'info', 'sistema'], true) ? 'var(--accent-color)' : 'var(--primary-color)' ?>">
                                    <i class="bi bi-<?= $n['tipo'] === 'alerta' ? 'exclamation-triangle' : ($n['tipo'] === 'seguimiento' ? 'clipboard2-pulse' : 'info-circle') ?>"></i>
                                </span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span class="fw-semibold"><?= e($n['titulo']) ?></span>
                                        <span class="text-muted fs-8"><?= f_fecha_hora($n['created_at']) ?></span>
                                    </div>
                                    <div class="text-muted fs-7"><?= e($n['mensaje']) ?></div>
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="<?= url($n['url'] ?? 'notificaciones') ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-arrow-right me-1"></i>Ver</a>
                                        <?php if ((int)$n['leido'] === 0): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-marcar-leida"><i class="bi bi-check2 me-1"></i>Marcar leída</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ((int)$n['leido'] === 0): ?>
                                    <span class="badge bg-danger rounded-pill flex-shrink-0">nueva</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(function () {
    // Marcar leída vía AJAX
    $('.btn-marcar-leida').on('click', function () {
        const $btn = $(this);
        const $item = $btn.closest('.notif-item');
        const id = $item.data('id');
        $.post(window.BASE_URL + '/notificaciones/marcar/' + id, { _token: '<?= csrf_token() ?>' })
            .done(function () {
                $item.removeClass('bg-accent-subtle');
                $item.find('.badge').remove();
                $btn.remove();
            });
    });
});
</script>
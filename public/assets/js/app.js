/* ============================================================
   EduAlert JJP - app.js
   Comportamiento global: datatables, confirmaciones, ajax, gráficos
   ============================================================ */

$(function () {
    'use strict';

    // ---------- Sidebar responsive ----------
    const $sidebar = $('#sidebar');
    const $backdrop = $('#sidebarBackdrop');
    $('#sidebarToggle').on('click', function () {
        $sidebar.toggleClass('open');
        $backdrop.toggleClass('show');
    });
    $('#sidebarClose').on('click', function () {
        $sidebar.removeClass('open');
        $backdrop.removeClass('show');
    });
    $backdrop.on('click', function () {
        $sidebar.removeClass('open');
        $backdrop.removeClass('show');
    });

    // ---------- DataTables ----------
    $('.table-datatable:not(.no-init)').each(function () {
        const config = {
            language: {
                url: undefined,
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                zeroRecords: 'No se encontraron registros',
                paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                emptyTable: 'No hay datos disponibles'
            },
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
            responsive: $(this).hasClass('table-responsive-force'),
            order: [],
            columnDefs: [{ orderable: false, targets: 'no-sort' }]
        };
        if ($(this).data('paginado') !== undefined) config.paging = false;
        $(this).DataTable(config);
    });

    // ---------- Confirmaciones con SweetAlert2 ----------
    $(document).on('submit', 'form[data-confirm]', function (e) {
        e.preventDefault();
        const form = this;
        const config = $(form).data('confirm');
        const titulo = config.titulo || '¿Confirmar acción?';
        const texto = config.texto || 'Esta acción no se puede deshacer.';
        const btnColor = config.color || '#e11d48';
        Swal.fire({
            title: titulo,
            text: texto,
            icon: config.icono || 'warning',
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: config.confirmar || 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // ---------- Botones de acción con confirmación ----------
    $(document).on('click', 'button[data-confirm-action]', function (e) {
        e.preventDefault();
        const btn = this;
        const config = $(btn).data('confirm-action');
        Swal.fire({
            title: config.titulo || '¿Confirmar acción?',
            text: config.texto || 'Esta acción no se puede deshacer.',
            icon: config.icono || 'warning',
            showCancelButton: true,
            confirmButtonColor: config.color || '#e11d48',
            cancelButtonColor: '#6c757d',
            confirmButtonText: config.confirmar || 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = btn.dataset.url;
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = window.EA_CSRF || '';
                form.appendChild(token);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // ---------- Notificaciones: marcar leída (AJAX) ----------
    $(document).on('click', '.notif-marcar', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $item = $(this).closest('.notif-item');
        $.post(url, { _token: window.EA_CSRF || '' })
            .done(function () {
                $item.removeClass('unread');
                // Actualizar contador
                const contador = $('.notif-dot');
                const actual = parseInt(contador.text(), 10) || 0;
                if (actual > 1) contador.text(actual - 1);
                else contador.remove();
            });
    });

    // ---------- Cargar secciones según grado (AJAX ligero) ----------
    $(document).on('change', 'select[data-secciones-url]', function () {
        const gradoId = $(this).val();
        const $target = $($(this).data('target'));
        if (!gradoId) { $target.html('<option value="">Primero seleccione un grado</option>'); return; }
        const url = $(this).data('secciones-url').replace('__GRADO__', gradoId);
        $.getJSON(url, function (data) {
            let html = '<option value="">-- Seleccionar sección --</option>';
            data.forEach(function (s) {
                html += '<option value="' + s.id + '">' + s.nombre + ' (' + s.turno + ')</option>';
            });
            $target.html(html);
        });
    });

    // ---------- Auto-ocultar mensajes flash ----------
    setTimeout(function () {
        $('.flash-alert').fadeOut(400, function () { $(this).remove(); });
    }, 6000);

    // ---------- Mostrar/ocultar contraseña ----------
    $(document).on('click', '.password-toggle', function () {
        const input = $($(this).data('target'));
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).toggleClass('bi-eye bi-eye-slash');
    });

    // ---------- Solo números en DNI ----------
    $(document).on('input', 'input[data-digits]', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, $(this).data('digits'));
    });
});

// CSRF global para peticiones AJAX
window.EA_CSRF = window.EA_CSRF || (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '');

// Autollenado rápido de cuentas demo en el login
function llenarDemo(rol) {
    const cuentas = {
        admin: 'admin@edualert.test',
        director: 'director@edualert.test',
        docente: 'docente@edualert.test',
        tutor: 'tutor@edualert.test',
        auxiliar: 'auxiliar@edualert.test'
    };
    if (cuentas[rol]) {
        document.getElementById('email').value = cuentas[rol];
        document.getElementById('password').value = '12345678';
        document.getElementById('email').focus();
    }
}
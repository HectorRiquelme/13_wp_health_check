/**
 * WP Health Check - Script de administración
 */
(function ($) {
    'use strict';

    var currentResults = [];

    /**
     * Muestra una notificación temporal.
     */
    function showNotice(message, type) {
        var $notice = $('#wphc-notice');
        $notice
            .removeClass('success error info')
            .addClass(type)
            .html(message)
            .fadeIn(300);

        setTimeout(function () {
            $notice.fadeOut(300);
        }, 5000);
    }

    /**
     * Renderiza la lista de resultados.
     */
    function renderResults(data) {
        var results = data.results;
        currentResults = results;
        var $list = $('#wphc-results-list');
        $list.empty();

        $('#wphc-results-date').text(data.date || '');
        $('#wphc-results').fadeIn(300);

        $.each(results, function (i, result) {
            var statusLabel = {
                pass: 'Correcto',
                warning: 'Advertencia',
                fail: 'Error'
            };

            var card = '<div class="wphc-result-card status-' + result.status + '">';
            card += '<div class="wphc-result-header">';
            card += '<div class="wphc-result-title">';
            card += '<span class="wphc-semaphore ' + result.status + '" title="' + statusLabel[result.status] + '"></span>';
            card += '<span>' + escapeHtml(result.name) + '</span>';
            card += '</div>';
            card += '<div class="wphc-result-actions">';
            card += '<button class="wphc-btn-details" data-index="' + i + '">Detalles</button>';

            if (result.fixable && result.status !== 'pass' && result.fix_type) {
                card += '<button class="wphc-btn-fix" data-fix-type="' + result.fix_type + '">Aplicar fix</button>';
            }

            card += '</div></div>';
            card += '<p class="wphc-result-message">' + escapeHtml(result.message) + '</p>';
            card += '<div class="wphc-result-details" id="wphc-details-' + i + '">';
            card += formatDetails(result.details);
            card += '</div>';
            card += '</div>';

            $list.append(card);
        });
    }

    /**
     * Formatea los detalles para mostrar.
     */
    function formatDetails(details) {
        if (!details) return '';
        return JSON.stringify(details, null, 2);
    }

    /**
     * Escapa HTML para prevenir XSS.
     */
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    /**
     * Ejecuta los checks vía AJAX.
     */
    function runChecks() {
        var $btn = $('#wphc-run-checks');
        var $progress = $('#wphc-progress-wrap');
        var $fill = $('#wphc-progress-fill');
        var $text = $('#wphc-progress-text');

        $btn.prop('disabled', true);
        $('#wphc-results').hide();
        $('#wphc-history').hide();
        $progress.fadeIn(200);
        $fill.css('width', '0%');
        $text.text(wphcData.strings.running);

        // Simular progreso gradual.
        var progress = 0;
        var total = wphcData.totalChecks || 6;
        var interval = setInterval(function () {
            progress += (100 / total);
            if (progress > 90) {
                progress = 90;
                clearInterval(interval);
            }
            $fill.css('width', progress + '%');
        }, 400);

        $.ajax({
            url: wphcData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wphc_run_checks',
                nonce: wphcData.nonce
            },
            success: function (response) {
                clearInterval(interval);
                $fill.css('width', '100%');
                $text.text(wphcData.strings.complete);

                setTimeout(function () {
                    $progress.fadeOut(200);
                }, 1000);

                if (response.success) {
                    renderResults(response.data);
                    showNotice(wphcData.strings.complete, 'success');
                } else {
                    showNotice(response.data || wphcData.strings.error, 'error');
                }
            },
            error: function () {
                clearInterval(interval);
                $progress.fadeOut(200);
                showNotice(wphcData.strings.error, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    }

    /**
     * Aplica un fix automático.
     */
    function applyFix(fixType, $btn) {
        $btn.prop('disabled', true).text(wphcData.strings.fixing);

        $.ajax({
            url: wphcData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wphc_apply_fix',
                nonce: wphcData.nonce,
                fix_type: fixType
            },
            success: function (response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    $btn.text(wphcData.strings.fixDone).css('background', '#888');
                } else {
                    showNotice(response.data || wphcData.strings.fixError, 'error');
                    $btn.prop('disabled', false).text('Aplicar fix');
                }
            },
            error: function () {
                showNotice(wphcData.strings.fixError, 'error');
                $btn.prop('disabled', false).text('Aplicar fix');
            }
        });
    }

    /**
     * Exporta reporte en JSON.
     */
    function exportJSON() {
        $.ajax({
            url: wphcData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wphc_export_json',
                nonce: wphcData.nonce
            },
            success: function (response) {
                if (response.success) {
                    var json = JSON.stringify(response.data, null, 2);
                    var blob = new Blob([json], { type: 'application/json' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'wp-health-check-report.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    showNotice('Reporte exportado correctamente.', 'success');
                } else {
                    showNotice(response.data || wphcData.strings.noResults, 'error');
                }
            },
            error: function () {
                showNotice(wphcData.strings.error, 'error');
            }
        });
    }

    /**
     * Muestra el historial de checks.
     */
    function showHistory() {
        var $history = $('#wphc-history');
        var $list = $('#wphc-history-list');

        $('#wphc-results').hide();

        $.ajax({
            url: wphcData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wphc_get_history',
                nonce: wphcData.nonce
            },
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    $list.empty();

                    $.each(response.data, function (i, entry) {
                        var passCount = 0, warnCount = 0, failCount = 0;

                        $.each(entry.results, function (j, r) {
                            if (r.status === 'pass') passCount++;
                            else if (r.status === 'warning') warnCount++;
                            else failCount++;
                        });

                        var html = '<div class="wphc-history-entry">';
                        html += '<div class="wphc-history-date">' + escapeHtml(entry.date) + '</div>';
                        html += '<div class="wphc-history-summary">';
                        html += '<span class="wphc-history-badge"><span class="wphc-semaphore pass"></span> ' + passCount + '</span>';
                        html += '<span class="wphc-history-badge"><span class="wphc-semaphore warning"></span> ' + warnCount + '</span>';
                        html += '<span class="wphc-history-badge"><span class="wphc-semaphore fail"></span> ' + failCount + '</span>';
                        html += '</div></div>';

                        $list.append(html);
                    });

                    $history.fadeIn(300);
                } else {
                    showNotice('No hay historial disponible.', 'info');
                }
            },
            error: function () {
                showNotice(wphcData.strings.error, 'error');
            }
        });
    }

    // Event listeners.
    $(document).ready(function () {
        $('#wphc-run-checks').on('click', runChecks);
        $('#wphc-export-json').on('click', exportJSON);
        $('#wphc-show-history').on('click', showHistory);

        // Toggle detalles.
        $(document).on('click', '.wphc-btn-details', function () {
            var index = $(this).data('index');
            $('#wphc-details-' + index).slideToggle(200);
        });

        // Aplicar fix.
        $(document).on('click', '.wphc-btn-fix', function () {
            var fixType = $(this).data('fix-type');
            applyFix(fixType, $(this));
        });
    });

})(jQuery);

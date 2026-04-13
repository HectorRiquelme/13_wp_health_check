<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap wphc-wrap">
    <h1><?php esc_html_e( 'WP Health Check', 'wp-health-check' ); ?></h1>
    <p class="wphc-description">
        <?php esc_html_e( 'Diagnóstico automático de problemas comunes en tu sitio WordPress.', 'wp-health-check' ); ?>
    </p>

    <div class="wphc-actions">
        <button id="wphc-run-checks" class="button button-primary button-hero">
            <span class="dashicons dashicons-heart"></span>
            <?php esc_html_e( 'Ejecutar diagnóstico', 'wp-health-check' ); ?>
        </button>
        <button id="wphc-export-json" class="button button-secondary">
            <span class="dashicons dashicons-download"></span>
            <?php esc_html_e( 'Exportar JSON', 'wp-health-check' ); ?>
        </button>
        <button id="wphc-show-history" class="button button-secondary">
            <span class="dashicons dashicons-backup"></span>
            <?php esc_html_e( 'Ver historial', 'wp-health-check' ); ?>
        </button>
    </div>

    <!-- Barra de progreso -->
    <div id="wphc-progress-wrap" class="wphc-progress-wrap" style="display:none;">
        <div class="wphc-progress-bar">
            <div id="wphc-progress-fill" class="wphc-progress-fill"></div>
        </div>
        <p id="wphc-progress-text" class="wphc-progress-text"></p>
    </div>

    <!-- Resultados -->
    <div id="wphc-results" class="wphc-results" style="display:none;">
        <h2><?php esc_html_e( 'Resultados del diagnóstico', 'wp-health-check' ); ?></h2>
        <p id="wphc-results-date" class="wphc-results-date"></p>
        <div id="wphc-results-list" class="wphc-results-list"></div>
    </div>

    <!-- Historial -->
    <div id="wphc-history" class="wphc-history" style="display:none;">
        <h2><?php esc_html_e( 'Historial de diagnósticos', 'wp-health-check' ); ?></h2>
        <div id="wphc-history-list" class="wphc-history-list"></div>
    </div>

    <!-- Notificaciones -->
    <div id="wphc-notice" class="wphc-notice" style="display:none;"></div>
</div>

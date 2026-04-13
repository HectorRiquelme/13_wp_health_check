<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Maneja la página de administración y endpoints AJAX.
 */
class WPHC_Health_Check_Admin {

    /**
     * Instancia singleton.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Runner de checks.
     *
     * @var WPHC_Health_Check_Runner
     */
    private $runner;

    /**
     * Obtiene la instancia singleton.
     *
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->runner = new WPHC_Health_Check_Runner();

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // AJAX handlers.
        add_action( 'wp_ajax_wphc_run_checks', array( $this, 'ajax_run_checks' ) );
        add_action( 'wp_ajax_wphc_apply_fix', array( $this, 'ajax_apply_fix' ) );
        add_action( 'wp_ajax_wphc_export_json', array( $this, 'ajax_export_json' ) );
        add_action( 'wp_ajax_wphc_get_history', array( $this, 'ajax_get_history' ) );
    }

    /**
     * Agrega menú en Herramientas.
     */
    public function add_admin_menu() {
        add_management_page(
            __( 'Health Check', 'wp-health-check' ),
            __( 'Health Check', 'wp-health-check' ),
            'manage_options',
            'wp-health-check',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Carga CSS y JS en la página del plugin.
     *
     * @param string $hook Hook de la página actual.
     */
    public function enqueue_assets( $hook ) {
        if ( 'tools_page_wp-health-check' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'wphc-admin-style',
            WPHC_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            WPHC_VERSION
        );

        wp_enqueue_script(
            'wphc-admin-script',
            WPHC_PLUGIN_URL . 'admin/js/admin-script.js',
            array( 'jquery' ),
            WPHC_VERSION,
            true
        );

        wp_localize_script( 'wphc-admin-script', 'wphcData', array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'wphc_nonce' ),
            'totalChecks'  => $this->runner->get_count(),
            'strings'      => array(
                'running'    => __( 'Ejecutando diagnóstico...', 'wp-health-check' ),
                'complete'   => __( 'Diagnóstico completado.', 'wp-health-check' ),
                'error'      => __( 'Error al ejecutar el diagnóstico.', 'wp-health-check' ),
                'fixing'     => __( 'Aplicando corrección...', 'wp-health-check' ),
                'fixDone'    => __( 'Corrección aplicada.', 'wp-health-check' ),
                'fixError'   => __( 'Error al aplicar la corrección.', 'wp-health-check' ),
                'exporting'  => __( 'Exportando reporte...', 'wp-health-check' ),
                'noResults'  => __( 'No hay resultados. Ejecuta un diagnóstico primero.', 'wp-health-check' ),
            ),
        ) );
    }

    /**
     * Renderiza la página de administración.
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No tienes permisos para acceder a esta página.', 'wp-health-check' ) );
        }

        include WPHC_PLUGIN_DIR . 'admin/views/admin-page.php';
    }

    /**
     * AJAX: Ejecuta todos los checks.
     */
    public function ajax_run_checks() {
        check_ajax_referer( 'wphc_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'wp-health-check' ) );
        }

        $results = $this->runner->run_all();
        $this->runner->save_history( $results );

        wp_send_json_success( array(
            'results' => $results,
            'date'    => current_time( 'Y-m-d H:i:s' ),
        ) );
    }

    /**
     * AJAX: Aplica una corrección automática.
     */
    public function ajax_apply_fix() {
        check_ajax_referer( 'wphc_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'wp-health-check' ) );
        }

        $fix_type = isset( $_POST['fix_type'] ) ? sanitize_text_field( wp_unslash( $_POST['fix_type'] ) ) : '';

        if ( empty( $fix_type ) ) {
            wp_send_json_error( __( 'Tipo de corrección no especificado.', 'wp-health-check' ) );
        }

        $result = WPHC_Health_Check_Fixer::apply_fix( $fix_type );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    /**
     * AJAX: Exporta el último reporte en JSON.
     */
    public function ajax_export_json() {
        check_ajax_referer( 'wphc_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'wp-health-check' ) );
        }

        $history = $this->runner->get_history();

        if ( empty( $history ) ) {
            wp_send_json_error( __( 'No hay reportes disponibles.', 'wp-health-check' ) );
        }

        $report = array(
            'site_url'   => get_site_url(),
            'site_name'  => get_bloginfo( 'name' ),
            'generated'  => $history[0]['date'],
            'results'    => $history[0]['results'],
        );

        wp_send_json_success( $report );
    }

    /**
     * AJAX: Obtiene el historial de checks.
     */
    public function ajax_get_history() {
        check_ajax_referer( 'wphc_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permisos insuficientes.', 'wp-health-check' ) );
        }

        $history = $this->runner->get_history();
        wp_send_json_success( $history );
    }
}

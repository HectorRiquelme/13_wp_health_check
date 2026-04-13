<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verifica versión de PHP y WordPress actualizada.
 */
class WPHC_Check_Updates {

    /**
     * Versión mínima recomendada de PHP.
     */
    const MIN_PHP_VERSION = '8.0';

    /**
     * Ejecuta el check.
     *
     * @return array
     */
    public function run() {
        $checks  = array();
        $overall = 'pass';

        // 1. Versión de PHP.
        $php_version = phpversion();
        $php_ok      = version_compare( $php_version, self::MIN_PHP_VERSION, '>=' );

        $checks['php_version'] = array(
            'label'  => __( 'Versión de PHP', 'wp-health-check' ),
            'status' => $php_ok ? 'pass' : 'warning',
            'info'   => sprintf(
                /* translators: %1$s: versión actual, %2$s: versión recomendada */
                __( 'Versión actual: %1$s. Recomendada: %2$s o superior.', 'wp-health-check' ),
                $php_version,
                self::MIN_PHP_VERSION
            ),
        );

        // 2. Versión de WordPress actualizada.
        global $wp_version;
        $update_data = get_site_transient( 'update_core' );
        $wp_updated  = true;

        if ( is_object( $update_data ) && ! empty( $update_data->updates ) ) {
            $latest = $update_data->updates[0];
            if ( isset( $latest->response ) && 'upgrade' === $latest->response ) {
                $wp_updated = false;
            }
        }

        $checks['wp_version'] = array(
            'label'  => __( 'Versión de WordPress', 'wp-health-check' ),
            'status' => $wp_updated ? 'pass' : 'warning',
            'info'   => $wp_updated
                ? sprintf(
                    /* translators: %s: versión actual */
                    __( 'WordPress %s está actualizado.', 'wp-health-check' ),
                    $wp_version
                )
                : sprintf(
                    /* translators: %s: versión actual */
                    __( 'WordPress %s tiene actualizaciones disponibles.', 'wp-health-check' ),
                    $wp_version
                ),
        );

        // Determinar estado general.
        foreach ( $checks as $check ) {
            if ( 'fail' === $check['status'] ) {
                $overall = 'fail';
                break;
            }
            if ( 'warning' === $check['status'] ) {
                $overall = 'warning';
            }
        }

        $messages = array(
            'pass'    => __( 'PHP y WordPress están actualizados.', 'wp-health-check' ),
            'warning' => __( 'Hay actualizaciones pendientes.', 'wp-health-check' ),
            'fail'    => __( 'Versiones desactualizadas detectadas.', 'wp-health-check' ),
        );

        return array(
            'name'    => __( 'Actualizaciones', 'wp-health-check' ),
            'status'  => $overall,
            'message' => $messages[ $overall ],
            'details' => array(
                'checks' => $checks,
            ),
            'fixable' => false,
        );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Checks de seguridad: HTTPS, debug mode, permisos .htaccess.
 */
class WPHC_Check_Security {

    /**
     * Ejecuta el check.
     *
     * @return array
     */
    public function run() {
        $checks  = array();
        $overall = 'pass';

        // 1. HTTPS activo.
        $is_ssl = is_ssl();
        $checks['https'] = array(
            'label'  => __( 'HTTPS activo', 'wp-health-check' ),
            'status' => $is_ssl ? 'pass' : 'fail',
            'info'   => $is_ssl
                ? __( 'El sitio usa HTTPS.', 'wp-health-check' )
                : __( 'El sitio NO usa HTTPS. Se recomienda activar SSL.', 'wp-health-check' ),
        );

        // 2. Debug mode desactivado en producción.
        $debug_on = defined( 'WP_DEBUG' ) && WP_DEBUG;
        $checks['debug'] = array(
            'label'  => __( 'Modo debug', 'wp-health-check' ),
            'status' => $debug_on ? 'warning' : 'pass',
            'info'   => $debug_on
                ? __( 'WP_DEBUG está activado. No recomendado en producción.', 'wp-health-check' )
                : __( 'WP_DEBUG está desactivado.', 'wp-health-check' ),
        );

        // 3. Permisos de .htaccess.
        $htaccess = ABSPATH . '.htaccess';
        if ( file_exists( $htaccess ) ) {
            $perms = fileperms( $htaccess );
            $perms_octal = substr( decoct( $perms ), -3 );
            $is_safe = in_array( $perms_octal, array( '644', '444' ), true );

            $checks['htaccess'] = array(
                'label'  => __( 'Permisos .htaccess', 'wp-health-check' ),
                'status' => $is_safe ? 'pass' : 'warning',
                'info'   => sprintf(
                    /* translators: %s: permisos actuales */
                    __( 'Permisos actuales: %s. Recomendado: 644 o 444.', 'wp-health-check' ),
                    $perms_octal
                ),
            );
        } else {
            $checks['htaccess'] = array(
                'label'  => __( 'Permisos .htaccess', 'wp-health-check' ),
                'status' => 'warning',
                'info'   => __( 'No se encontró archivo .htaccess (puede ser servidor Nginx).', 'wp-health-check' ),
            );
        }

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
            'pass'    => __( 'Todos los checks de seguridad pasaron correctamente.', 'wp-health-check' ),
            'warning' => __( 'Algunos aspectos de seguridad necesitan revisión.', 'wp-health-check' ),
            'fail'    => __( 'Se detectaron problemas de seguridad importantes.', 'wp-health-check' ),
        );

        return array(
            'name'    => __( 'Seguridad', 'wp-health-check' ),
            'status'  => $overall,
            'message' => $messages[ $overall ],
            'details' => array(
                'checks' => $checks,
            ),
            'fixable' => false,
        );
    }
}

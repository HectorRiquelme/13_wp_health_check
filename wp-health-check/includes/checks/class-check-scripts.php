<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Detecta plugins que cargan scripts bloqueantes en el header.
 */
class WPHC_Check_Scripts {

    /**
     * Ejecuta el check.
     *
     * @return array
     */
    public function run() {
        global $wp_scripts;

        $blocking_scripts = array();

        if ( $wp_scripts instanceof WP_Scripts ) {
            foreach ( $wp_scripts->registered as $handle => $script ) {
                // Verificar si el script está en el header (group = 0 o no definido).
                $in_footer = isset( $script->extra['group'] ) && 1 === (int) $script->extra['group'];

                if ( $in_footer ) {
                    continue;
                }

                // Ignorar scripts del core de WordPress.
                if ( empty( $script->src ) ) {
                    continue;
                }

                $src = $script->src;
                if ( strpos( $src, '/wp-includes/' ) !== false || strpos( $src, '/wp-admin/' ) !== false ) {
                    continue;
                }

                // Verificar si tiene defer o async.
                $has_defer = false;
                $has_async = false;

                if ( ! empty( $script->extra['strategy'] ) ) {
                    $has_defer = 'defer' === $script->extra['strategy'];
                    $has_async = 'async' === $script->extra['strategy'];
                }

                if ( ! $has_defer && ! $has_async ) {
                    $blocking_scripts[] = array(
                        'handle' => $handle,
                        'src'    => $src,
                    );
                }
            }
        }

        if ( empty( $blocking_scripts ) ) {
            $status  = 'pass';
            $message = __( 'No se detectaron scripts bloqueantes en el header.', 'wp-health-check' );
        } elseif ( count( $blocking_scripts ) <= 2 ) {
            $status  = 'warning';
            $message = sprintf(
                /* translators: %d: número de scripts */
                __( '%d script(s) bloqueante(s) detectado(s) en el header.', 'wp-health-check' ),
                count( $blocking_scripts )
            );
        } else {
            $status  = 'fail';
            $message = sprintf(
                /* translators: %d: número de scripts */
                __( '%d scripts bloqueantes en el header. Esto afecta el rendimiento.', 'wp-health-check' ),
                count( $blocking_scripts )
            );
        }

        return array(
            'name'     => __( 'Scripts bloqueantes', 'wp-health-check' ),
            'status'   => $status,
            'message'  => $message,
            'details'  => array(
                'blocking_scripts' => $blocking_scripts,
            ),
            'fixable'  => ! empty( $blocking_scripts ),
            'fix_type' => 'move_scripts_footer',
        );
    }
}

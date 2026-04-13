<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Detecta conflictos de jQuery: múltiples versiones cargadas.
 */
class WPHC_Check_jQuery {

    /**
     * Ejecuta el check.
     *
     * @return array
     */
    public function run() {
        global $wp_scripts;

        $duplicates = array();
        $jquery_handles = array();

        if ( $wp_scripts instanceof WP_Scripts ) {
            foreach ( $wp_scripts->registered as $handle => $script ) {
                if ( false !== strpos( $handle, 'jquery' ) ) {
                    $jquery_handles[] = array(
                        'handle' => $handle,
                        'src'    => $script->src,
                        'ver'    => $script->ver,
                    );
                }
            }

            // Buscar fuentes de jQuery externas (CDN u otras).
            $seen_sources = array();
            foreach ( $jquery_handles as $jq ) {
                if ( empty( $jq['src'] ) ) {
                    continue;
                }
                $normalized = preg_replace( '#^https?:#', '', $jq['src'] );
                if ( in_array( $normalized, $seen_sources, true ) ) {
                    $duplicates[] = $jq['handle'];
                }
                $seen_sources[] = $normalized;
            }

            // Verificar si hay múltiples handles que cargan jQuery core.
            $core_handles = array_filter( $jquery_handles, function ( $jq ) {
                return preg_match( '/jquery(\.min)?\.js/', $jq['src'] ?? '' );
            } );

            if ( count( $core_handles ) > 2 ) {
                // WordPress carga jquery + jquery-core + jquery-migrate normalmente.
                $status = 'fail';
            } elseif ( ! empty( $duplicates ) ) {
                $status = 'warning';
            } else {
                $status = 'pass';
            }
        } else {
            $status = 'pass';
            $jquery_handles = array();
        }

        $messages = array(
            'pass'    => __( 'No se detectaron conflictos de jQuery.', 'wp-health-check' ),
            'warning' => __( 'Se detectaron posibles duplicados de jQuery.', 'wp-health-check' ),
            'fail'    => __( 'Múltiples versiones de jQuery detectadas. Esto puede causar conflictos.', 'wp-health-check' ),
        );

        return array(
            'name'    => __( 'Conflictos de jQuery', 'wp-health-check' ),
            'status'  => $status,
            'message' => $messages[ $status ],
            'details' => array(
                'jquery_handles' => $jquery_handles,
                'duplicates'     => $duplicates,
            ),
            'fixable' => false,
        );
    }
}

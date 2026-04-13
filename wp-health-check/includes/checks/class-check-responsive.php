<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verifica compatibilidad responsive: viewport meta tag en el theme.
 */
class WPHC_Check_Responsive {

    /**
     * Ejecuta el check.
     *
     * @return array
     */
    public function run() {
        $theme_dir     = get_template_directory();
        $header_file   = $theme_dir . '/header.php';
        $has_viewport  = false;
        $viewport_info = '';

        if ( file_exists( $header_file ) ) {
            $content = file_get_contents( $header_file );

            if ( preg_match( '/meta\s+name=["\']viewport["\']/i', $content ) ) {
                $has_viewport = true;

                if ( preg_match( '/meta\s+name=["\']viewport["\'][^>]*content=["\']([^"\']+)["\']/i', $content, $matches ) ) {
                    $viewport_info = $matches[1];
                }
            }
        }

        // También verificar en functions.php por wp_head hooks.
        $functions_file = $theme_dir . '/functions.php';
        if ( ! $has_viewport && file_exists( $functions_file ) ) {
            $func_content = file_get_contents( $functions_file );
            if ( strpos( $func_content, 'viewport' ) !== false ) {
                $has_viewport = true;
                $viewport_info = __( 'Definido dinámicamente en functions.php', 'wp-health-check' );
            }
        }

        if ( $has_viewport ) {
            $status  = 'pass';
            $message = __( 'Meta tag viewport presente en el theme.', 'wp-health-check' );
        } else {
            $status  = 'fail';
            $message = __( 'No se encontró meta tag viewport. El sitio puede no ser responsive.', 'wp-health-check' );
        }

        return array(
            'name'    => __( 'Compatibilidad responsive', 'wp-health-check' ),
            'status'  => $status,
            'message' => $message,
            'details' => array(
                'has_viewport'  => $has_viewport,
                'viewport_info' => $viewport_info,
                'theme'         => wp_get_theme()->get( 'Name' ),
            ),
            'fixable' => false,
        );
    }
}

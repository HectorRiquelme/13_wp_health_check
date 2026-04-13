<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Aplica correcciones automáticas para problemas detectados.
 */
class WPHC_Health_Check_Fixer {

    /**
     * Aplica un fix según el tipo indicado.
     *
     * @param string $fix_type Tipo de corrección.
     * @param array  $params   Parámetros adicionales.
     * @return array Resultado de la corrección.
     */
    public static function apply_fix( $fix_type, $params = array() ) {
        switch ( $fix_type ) {
            case 'add_alt_text':
                return self::fix_alt_text( $params );

            case 'move_scripts_footer':
                return self::fix_move_scripts_footer();

            case 'add_defer':
                return self::fix_add_defer();

            default:
                return array(
                    'success' => false,
                    'message' => __( 'Tipo de corrección no reconocido.', 'wp-health-check' ),
                );
        }
    }

    /**
     * Agrega alt text a imágenes que no lo tienen.
     *
     * @param array $params Parámetros con IDs de imágenes.
     * @return array
     */
    private static function fix_alt_text( $params ) {
        $fixed = 0;

        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'post_status'    => 'inherit',
        );

        $images = get_posts( $args );

        foreach ( $images as $image ) {
            $alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
            if ( empty( $alt ) ) {
                update_post_meta(
                    $image->ID,
                    '_wp_attachment_image_alt',
                    __( 'Imagen sin descripción', 'wp-health-check' )
                );
                $fixed++;
            }
        }

        return array(
            'success' => true,
            'message' => sprintf(
                /* translators: %d: número de imágenes corregidas */
                __( 'Se agregó alt text a %d imagen(es).', 'wp-health-check' ),
                $fixed
            ),
        );
    }

    /**
     * Registra hook para mover scripts al footer.
     *
     * @return array
     */
    private static function fix_move_scripts_footer() {
        $option_key = 'wphc_move_scripts_footer';
        update_option( $option_key, true );

        // Registrar el hook persistente.
        if ( ! has_action( 'wp_enqueue_scripts', array( __CLASS__, 'enforce_footer_scripts' ) ) ) {
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enforce_footer_scripts' ), 999 );
        }

        return array(
            'success' => true,
            'message' => __( 'Los scripts serán movidos al footer en las próximas cargas de página.', 'wp-health-check' ),
        );
    }

    /**
     * Registra hook para agregar defer a scripts.
     *
     * @return array
     */
    private static function fix_add_defer() {
        update_option( 'wphc_add_defer_scripts', true );

        return array(
            'success' => true,
            'message' => __( 'Se aplicará defer a los scripts en las próximas cargas de página.', 'wp-health-check' ),
        );
    }

    /**
     * Mueve scripts del header al footer.
     */
    public static function enforce_footer_scripts() {
        global $wp_scripts;

        if ( ! ( $wp_scripts instanceof WP_Scripts ) ) {
            return;
        }

        $exclude = array( 'jquery', 'jquery-core', 'jquery-migrate' );

        foreach ( $wp_scripts->registered as $handle => $script ) {
            if ( in_array( $handle, $exclude, true ) ) {
                continue;
            }
            if ( isset( $script->extra['group'] ) && 0 === $script->extra['group'] ) {
                continue;
            }
            $wp_scripts->add_data( $handle, 'group', 1 );
        }
    }

    /**
     * Agrega defer a los script tags.
     *
     * @param string $tag    Tag HTML del script.
     * @param string $handle Handle del script.
     * @return string
     */
    public static function add_defer_attribute( $tag, $handle ) {
        $exclude = array( 'jquery', 'jquery-core', 'jquery-migrate' );

        if ( in_array( $handle, $exclude, true ) ) {
            return $tag;
        }

        if ( strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
            return $tag;
        }

        return str_replace( ' src', ' defer src', $tag );
    }
}

// Aplicar fixes persistentes si están activados.
add_action( 'init', function () {
    if ( get_option( 'wphc_move_scripts_footer' ) ) {
        add_action( 'wp_enqueue_scripts', array( 'WPHC_Health_Check_Fixer', 'enforce_footer_scripts' ), 999 );
    }

    if ( get_option( 'wphc_add_defer_scripts' ) ) {
        add_filter( 'script_loader_tag', array( 'WPHC_Health_Check_Fixer', 'add_defer_attribute' ), 10, 2 );
    }
} );

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verifica imágenes del media library: alt text y tamaño optimizado.
 */
class WPHC_Check_Images {

    /**
     * Tamaño máximo permitido en bytes (500 KB).
     */
    const MAX_SIZE = 512000;

    /**
     * Ejecuta el check.
     *
     * @return array
     */
    public function run() {
        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 100,
            'post_status'    => 'inherit',
        );

        $images = get_posts( $args );

        $missing_alt   = array();
        $oversized     = array();
        $total_checked = count( $images );

        foreach ( $images as $image ) {
            $alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
            if ( empty( trim( $alt ) ) ) {
                $missing_alt[] = array(
                    'id'    => $image->ID,
                    'title' => $image->post_title,
                    'url'   => wp_get_attachment_url( $image->ID ),
                );
            }

            $file_path = get_attached_file( $image->ID );
            if ( $file_path && file_exists( $file_path ) ) {
                $size = filesize( $file_path );
                if ( $size > self::MAX_SIZE ) {
                    $oversized[] = array(
                        'id'    => $image->ID,
                        'title' => $image->post_title,
                        'size'  => size_format( $size ),
                        'url'   => wp_get_attachment_url( $image->ID ),
                    );
                }
            }
        }

        $issues = count( $missing_alt ) + count( $oversized );

        if ( 0 === $issues ) {
            $status  = 'pass';
            $message = __( 'Todas las imágenes tienen alt text y están optimizadas.', 'wp-health-check' );
        } elseif ( $issues <= 5 ) {
            $status  = 'warning';
            $message = sprintf(
                /* translators: %1$d: sin alt, %2$d: sobredimensionadas */
                __( '%1$d imagen(es) sin alt text, %2$d sobredimensionada(s).', 'wp-health-check' ),
                count( $missing_alt ),
                count( $oversized )
            );
        } else {
            $status  = 'fail';
            $message = sprintf(
                /* translators: %1$d: sin alt, %2$d: sobredimensionadas */
                __( '%1$d imagen(es) sin alt text, %2$d sobredimensionada(s). Requiere atención.', 'wp-health-check' ),
                count( $missing_alt ),
                count( $oversized )
            );
        }

        return array(
            'name'     => __( 'Imágenes del Media Library', 'wp-health-check' ),
            'status'   => $status,
            'message'  => $message,
            'details'  => array(
                'total_checked' => $total_checked,
                'missing_alt'   => $missing_alt,
                'oversized'     => $oversized,
            ),
            'fixable'  => ! empty( $missing_alt ),
            'fix_type' => 'add_alt_text',
        );
    }
}

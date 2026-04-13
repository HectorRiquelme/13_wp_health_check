<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Ejecuta todos los checks de salud registrados.
 */
class WPHC_Health_Check_Runner {

    /**
     * Lista de clases de checks.
     *
     * @var array
     */
    private $checks = array();

    public function __construct() {
        $this->checks = array(
            new WPHC_Check_jQuery(),
            new WPHC_Check_Scripts(),
            new WPHC_Check_Images(),
            new WPHC_Check_Responsive(),
            new WPHC_Check_Security(),
            new WPHC_Check_Updates(),
        );
    }

    /**
     * Ejecuta todos los checks y devuelve resultados.
     *
     * @return array
     */
    public function run_all() {
        $results = array();

        foreach ( $this->checks as $check ) {
            $results[] = $check->run();
        }

        return $results;
    }

    /**
     * Ejecuta un check individual por índice.
     *
     * @param int $index Índice del check.
     * @return array|null
     */
    public function run_single( $index ) {
        if ( isset( $this->checks[ $index ] ) ) {
            return $this->checks[ $index ]->run();
        }
        return null;
    }

    /**
     * Devuelve la cantidad de checks.
     *
     * @return int
     */
    public function get_count() {
        return count( $this->checks );
    }

    /**
     * Guarda resultados en el historial.
     *
     * @param array $results Resultados de los checks.
     */
    public function save_history( $results ) {
        $history = get_option( 'wphc_check_history', array() );

        $entry = array(
            'date'    => current_time( 'mysql' ),
            'results' => $results,
        );

        array_unshift( $history, $entry );

        // Mantener máximo 50 entradas.
        if ( count( $history ) > 50 ) {
            $history = array_slice( $history, 0, 50 );
        }

        update_option( 'wphc_check_history', $history );
    }

    /**
     * Obtiene el historial de checks.
     *
     * @return array
     */
    public function get_history() {
        return get_option( 'wphc_check_history', array() );
    }
}

<?php
/**
 * Kezdőlap-modul: MIÉRT MI?
 * A markup a korábbi templates/front-page.php-ból byte-hűen kiemelve.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_pontok = apply_filters( 'tpk_miert_mi_pontok', $tpk_b['pontok'] );
?>
    <!-- MIÉRT MI? -->
    <section class="tpk-section<?php echo esc_attr( tpk_megj_osztalyok( 'miert_mi', $tpk_b ) ); ?>"<?php echo tpk_megj_stilus( $tpk_b ); ?>>
        <div class="tpk-why-head">
            <p class="tpk-eyebrow"><?php echo esc_html( $tpk_b['eyebrow'] ); ?></p>
            <h2 class="tpk-section-title"><?php echo esc_html( $tpk_b['cim'] ); ?></h2>
        </div>
        <div class="tpk-grid-4">
            <?php foreach ( $tpk_pontok as $tpk_pont ) : ?>
                <div class="tpk-reason">
                    <div class="tpk-reason-icon-wrap"><?php echo tpk_miert_mi_ikon_html( $tpk_pont['ikon'] ); ?></div>
                    <h3 class="tpk-reason-title"><?php echo esc_html( $tpk_pont['title'] ); ?></h3>
                    <p class="tpk-reason-text"><?php echo esc_html( $tpk_pont['text'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

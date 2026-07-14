<?php
/**
 * Kezdőlap-modul: ZÁRÓ CTA
 * A markup a korábbi templates/front-page.php-ból byte-hűen kiemelve.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_url_ajanlatok = tpk_ajanlatok_url();
?>
    <!-- ZÁRÓ CTA -->
    <section class="tpk-cta">
        <div>
            <h2 class="tpk-cta-title"><?php echo esc_html( apply_filters( 'tpk_zaro_cta_cim', $tpk_b['cim'] ) ); ?></h2>
            <p class="tpk-cta-subtitle"><?php echo esc_html( apply_filters( 'tpk_zaro_cta_alcim', $tpk_b['alcim'] ) ); ?></p>
        </div>
        <div class="tpk-cta-actions">
            <div class="tpk-social">
                <?php $tpk_social = apply_filters( 'tpk_kozossegi_linkek', array( 'instagram' => $tpk_b['instagram_link'], 'facebook' => $tpk_b['facebook_link'] ) ); ?>
                <?php if ( ! empty( $tpk_social['instagram'] ) ) : ?>
                    <a class="tpk-social-btn" href="<?php echo esc_url( $tpk_social['instagram'] ); ?>" aria-label="Instagram"><span class="tpk-social-icon-instagram"></span></a>
                <?php endif; ?>
                <?php if ( ! empty( $tpk_social['facebook'] ) ) : ?>
                    <a class="tpk-social-btn" href="<?php echo esc_url( $tpk_social['facebook'] ); ?>" aria-label="Facebook"><span class="tpk-social-icon-facebook"></span></a>
                <?php endif; ?>
            </div>
            <a class="tpk-btn-accent tpk-btn-accent--cta" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>"><?php echo esc_html( apply_filters( 'tpk_zaro_cta_gomb_szoveg', $tpk_b['gomb_szoveg'] ) ); ?></a>
        </div>
    </section>

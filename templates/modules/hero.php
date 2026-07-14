<?php
/**
 * Kezdőlap-modul: HERO
 * A markup a korábbi templates/front-page.php-ból byte-hűen kiemelve,
 * a szöveg-források a modul-beállításokból ($tpk_b) jönnek.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_url_ajanlatok = tpk_ajanlatok_url();
$tpk_hero_kep_url  = ! empty( $tpk_b['hero_kep_id'] ) ? wp_get_attachment_image_url( (int) $tpk_b['hero_kep_id'], 'large' ) : '';
?>
    <!-- HERO -->
    <section class="tpk-hero<?php echo esc_attr( tpk_megj_osztalyok( 'hero', $tpk_b ) ); ?>"<?php echo tpk_megj_stilus( $tpk_b ); ?>>
        <div class="tpk-hero-copy">
            <div class="tpk-hero-badge">
                <span class="tpk-hero-badge-dot"></span>
                <?php echo esc_html( apply_filters( 'tpk_hero_badge_szoveg', $tpk_b['badge_szoveg'] ) ); ?>
            </div>
            <h1 class="tpk-hero-title">
                <?php echo wp_kses_post( apply_filters( 'tpk_hero_cim', $tpk_b['cim'] ) ); ?>
            </h1>
            <p class="tpk-hero-subtitle">
                <?php echo esc_html( apply_filters( 'tpk_hero_alcim', $tpk_b['alcim'] ) ); ?>
            </p>
            <div class="tpk-hero-actions">
                <a class="tpk-btn-dark" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>"><?php echo esc_html( apply_filters( 'tpk_hero_cta_szoveg', $tpk_b['cta_szoveg'] ) ); ?></a>
                <span class="tpk-hero-note"><?php echo esc_html( $tpk_b['megjegyzes'] ); ?></span>
            </div>
        </div>
        <div class="tpk-hero-visual<?php echo $tpk_hero_kep_url ? '' : ' tpk-hero-visual--placeholder'; ?>"
             <?php if ( $tpk_hero_kep_url ) : ?>style="background-image:url('<?php echo esc_url( $tpk_hero_kep_url ); ?>');"<?php endif; ?>>
            <?php if ( ! $tpk_hero_kep_url ) : ?><span class="tpk-placeholder-label">úti cél / hero fotó helye</span><?php endif; ?>
        </div>
    </section>

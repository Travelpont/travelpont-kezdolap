<?php
/**
 * Travelpont Kezdőoldal – megosztott navigáció + footer
 *
 * A `templates/front-page.php` és a `templates/page-wrapper.php` is ezt
 * hívja, hogy a Kezdőlapon KÍVÜLI oldalak (Ajánlatok, Úticélok, Rólunk,
 * Kapcsolat, Útikalauz, egyedi Ajánlat/Úticél/blogcikk) is ugyanazt a
 * brandelt fejlécet/láblécet kapják, mint a főoldal.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function tpk_render_nav() {
    $tpk_url_ajanlatok = tpk_ajanlatok_url();
    $tpk_url_uticelok  = tpk_uticelok_url();
    $tpk_url_utikalauz = tpk_utikalauz_url();
    $tpk_url_rolunk    = tpk_rolunk_url();
    $tpk_url_kapcsolat = tpk_kapcsolat_url();
    $tpk_logo_url      = tpk_logo_url();
    ?>
    <nav class="tpk-nav">
        <a class="tpk-nav-brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php if ( $tpk_logo_url ) : ?>
                <img class="tpk-logo-img" src="<?php echo esc_url( $tpk_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            <?php else : ?>
                <span class="tpk-logo-badge"><span class="tpk-logo-plane"></span></span>
                <span class="tpk-logo-text"><?php bloginfo( 'name' ); ?></span>
            <?php endif; ?>
        </a>
        <button type="button" class="tpk-nav-toggle" aria-expanded="false" aria-controls="tpk-nav-menu" aria-label="Menü megnyitása">
            <span class="tpk-nav-toggle-bar"></span>
            <span class="tpk-nav-toggle-bar"></span>
            <span class="tpk-nav-toggle-bar"></span>
        </button>
        <div class="tpk-nav-menu" id="tpk-nav-menu">
            <a class="tpk-nav-link" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>">Ajánlatok</a>
            <a class="tpk-nav-link" href="<?php echo esc_url( $tpk_url_uticelok ); ?>">Úticélok</a>
            <a class="tpk-nav-link" href="<?php echo esc_url( $tpk_url_utikalauz ); ?>">Útikalauz</a>
            <?php if ( $tpk_url_rolunk ) : ?><a class="tpk-nav-link" href="<?php echo esc_url( $tpk_url_rolunk ); ?>">Rólunk</a><?php endif; ?>
            <?php if ( $tpk_url_kapcsolat ) : ?><a class="tpk-nav-link" href="<?php echo esc_url( $tpk_url_kapcsolat ); ?>">Kapcsolat</a><?php endif; ?>
            <a class="tpk-nav-cta" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>"><?php echo esc_html( apply_filters( 'tpk_nav_cta_szoveg', tpk_get_modulok()['chrome']['nav_cta_szoveg'] ) ); ?></a>
        </div>
    </nav>
    <?php
}

function tpk_render_footer() {
    ?>
    <footer class="tpk-footer">
        <span class="tpk-footer-brand"><?php bloginfo( 'name' ); ?></span>
        <span class="tpk-footer-copy">© <?php echo esc_html( date_i18n( 'Y' ) ); ?> Travelpont.hu — Affiliate partnerlinkeken keresztül</span>
    </footer>
    <?php
}

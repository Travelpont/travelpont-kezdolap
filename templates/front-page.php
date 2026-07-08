<?php
/**
 * Travelpont Kezdőoldal – teljes egyedi sablon (template_include-dal betöltve)
 *
 * A jóváhagyott mockup (Travelpont - kezdőoldal mockup.html) alapján,
 * a valós Ajánlat / Úticél / Blog tartalommal feltöltve.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_ajanlatok = tpk_get_ajanlatok( 6 );
$tpk_orszagok  = tpk_get_orszagok( 3 );
$tpk_cikkek    = tpk_get_cikkek( 3 );
$tpk_pontok    = tpk_miert_mi_pontok();

$tpk_url_ajanlatok = tpk_ajanlatok_url();
$tpk_url_utikalauz = tpk_utikalauz_url();

$tpk_tagline = get_bloginfo( 'description' );
$tpk_title   = $tpk_tagline ? get_bloginfo( 'name' ) . ' – ' . $tpk_tagline : get_bloginfo( 'name' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $tpk_title ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'tpk-managed-page tpk-kezdolap' ); ?>>
<?php wp_body_open(); ?>

<div class="tpk-page">

    <?php tpk_render_nav(); ?>

    <!-- HERO -->
    <section class="tpk-hero">
        <div class="tpk-hero-copy">
            <div class="tpk-hero-badge">
                <span class="tpk-hero-badge-dot"></span>
                <?php echo esc_html( apply_filters( 'tpk_hero_badge_szoveg', 'Kézzel válogatott utazási ajánlatok' ) ); ?>
            </div>
            <h1 class="tpk-hero-title">
                <?php echo wp_kses_post( apply_filters( 'tpk_hero_cim',
                    'Fedezd fel a világot, <span class="tpk-accent-word">okosabban</span> foglalva.'
                ) ); ?>
            </h1>
            <p class="tpk-hero-subtitle">
                <?php echo esc_html( apply_filters( 'tpk_hero_alcim',
                    'A Travelpont naponta válogatja a legjobb repjegy- és szállás-kombinációkat azoknak, akik szeretnek utazni, de nem szeretnek órákat böngészni az árak után.'
                ) ); ?>
            </p>
            <div class="tpk-hero-actions">
                <a class="tpk-btn-dark" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>"><?php echo esc_html( apply_filters( 'tpk_hero_cta_szoveg', 'Nézd meg az ajánlatokat' ) ); ?></a>
                <span class="tpk-hero-note">Ingyenes · nincs regisztráció</span>
            </div>
        </div>
        <div class="tpk-hero-visual">
            <span class="tpk-placeholder-label">úti cél / hero fotó helye</span>
        </div>
    </section>

    <!-- KIEMELT AJÁNLATOK -->
    <section id="offers" class="tpk-section">
        <div class="tpk-section-head">
            <div>
                <p class="tpk-eyebrow">Kiemelt ajánlatok</p>
                <h2 class="tpk-section-title">Ez a hét legjobb dobása</h2>
            </div>
            <a class="tpk-section-link" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>">Összes ajánlat →</a>
        </div>

        <?php if ( $tpk_ajanlatok instanceof WP_Query && $tpk_ajanlatok->have_posts() ) : ?>
            <div class="tpk-grid-3">
                <?php while ( $tpk_ajanlatok->have_posts() ) : $tpk_ajanlatok->the_post();
                    $tpk_id       = get_the_ID();
                    $tpk_dest     = tpa_mezo( $tpk_id, 'tpa_celallomas' );
                    $tpk_dest     = $tpk_dest ? $tpk_dest : get_the_title();
                    $tpk_idopont  = tpa_mezo( $tpk_id, 'tpa_idopont' );
                    $tpk_ejszakak = tpa_mezo( $tpk_id, 'tpa_ejszakak' );
                    $tpk_datum_reszek = array();
                    if ( $tpk_idopont !== '' ) $tpk_datum_reszek[] = $tpk_idopont;
                    if ( $tpk_ejszakak !== '' ) $tpk_datum_reszek[] = $tpk_ejszakak . ' éj';
                    $tpk_datum    = implode( ' · ', $tpk_datum_reszek );
                    $tpk_cimke    = tpk_ajanlat_cimke( $tpk_id );
                    $tpk_repjegy  = tpa_mezo( $tpk_id, 'tpa_repjegy_ar' );
                    $tpk_szallas  = tpa_mezo( $tpk_id, 'tpa_szallas_ar' );
                    $tpk_osszesen = tpa_teljes_ar( $tpk_id );
                    ?>
                    <article class="tpk-offer-card">
                        <div class="tpk-offer-media<?php echo has_post_thumbnail() ? '' : ' tpk-offer-media--placeholder'; ?>"
                             <?php if ( has_post_thumbnail() ) : ?>style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $tpk_id, 'medium_large' ) ); ?>');"<?php endif; ?>>
                            <?php if ( $tpk_cimke ) : ?><span class="tpk-offer-tag"><?php echo esc_html( $tpk_cimke ); ?></span><?php else : ?><span></span><?php endif; ?>
                            <?php if ( ! has_post_thumbnail() ) : ?><span class="tpk-photo-note">fotó helye</span><?php endif; ?>
                        </div>
                        <div class="tpk-offer-body">
                            <h3 class="tpk-offer-dest"><?php echo esc_html( $tpk_dest ); ?></h3>
                            <?php if ( $tpk_datum !== '' ) : ?><p class="tpk-offer-dates"><?php echo esc_html( $tpk_datum ); ?></p><?php endif; ?>

                            <div class="tpk-offer-prices">
                                <?php if ( $tpk_repjegy !== '' ) : ?>
                                    <div class="tpk-price-row">
                                        <span>✈ Repjegy (oda-vissza)</span>
                                        <span><?php echo esc_html( tpa_ar_format( $tpk_repjegy ) ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $tpk_szallas !== '' ) : ?>
                                    <div class="tpk-price-row">
                                        <span>🏨 Szállás<?php echo $tpk_ejszakak !== '' ? ' (' . esc_html( $tpk_ejszakak ) . ' éj)' : ''; ?></span>
                                        <span><?php echo esc_html( tpa_ar_format( $tpk_szallas ) ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $tpk_osszesen !== '' ) : ?>
                                    <div class="tpk-price-divider"></div>
                                    <div class="tpk-price-row tpk-price-total">
                                        <span>Összesen / fő</span>
                                        <span><?php echo esc_html( tpa_ar_format( $tpk_osszesen ) ); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <a class="tpk-btn-accent" href="<?php the_permalink(); ?>">Részletek</a>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p class="tpk-empty"><?php echo esc_html( apply_filters( 'tpk_ajanlatok_ures_szoveg', 'Jelenleg nincs feltöltött ajánlat – nézz vissza hamarosan!' ) ); ?></p>
        <?php endif; ?>
    </section>

    <!-- ÚTICÉLOK -->
    <section id="destinations" class="tpk-destinations">
        <div class="tpk-section-head-centered">
            <p class="tpk-eyebrow tpk-eyebrow--onDark">Úticélok</p>
            <h2 class="tpk-section-title tpk-section-title--onDark">Ahova idén mindenki készül</h2>
        </div>

        <?php if ( $tpk_orszagok instanceof WP_Query && $tpk_orszagok->have_posts() ) : ?>
            <div class="tpk-grid-3">
                <?php while ( $tpk_orszagok->have_posts() ) : $tpk_orszagok->the_post();
                    $tpk_id     = get_the_ID();
                    $tpk_blurb  = tpu_mezo( $tpk_id, 'tpu_leiras' );
                    ?>
                    <a class="tpk-dest-card<?php echo has_post_thumbnail() ? '' : ' tpk-dest-card--placeholder'; ?>"
                       href="<?php the_permalink(); ?>"
                       <?php if ( has_post_thumbnail() ) : ?>style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $tpk_id, 'medium_large' ) ); ?>');"<?php endif; ?>>
                        <span class="tpk-dest-overlay"></span>
                        <span class="tpk-dest-content">
                            <span class="tpk-dest-name"><?php the_title(); ?></span>
                            <?php if ( $tpk_blurb ) : ?><span class="tpk-dest-blurb"><?php echo esc_html( $tpk_blurb ); ?></span><?php endif; ?>
                        </span>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p class="tpk-empty tpk-empty--onDark"><?php echo esc_html( apply_filters( 'tpk_uticelok_ures_szoveg', 'Jelenleg nincs feltöltött úticél.' ) ); ?></p>
        <?php endif; ?>
    </section>

    <!-- MIÉRT MI? -->
    <section class="tpk-section">
        <div class="tpk-why-head">
            <p class="tpk-eyebrow">Miért mi?</p>
            <h2 class="tpk-section-title">Nem robotok válogatnak, hanem mi</h2>
        </div>
        <div class="tpk-grid-4">
            <?php foreach ( $tpk_pontok as $tpk_pont ) : ?>
                <div class="tpk-reason">
                    <div class="tpk-reason-icon-wrap"><span class="tpk-icon-<?php echo esc_attr( $tpk_pont['ikon'] ); ?>"></span></div>
                    <h3 class="tpk-reason-title"><?php echo esc_html( $tpk_pont['title'] ); ?></h3>
                    <p class="tpk-reason-text"><?php echo esc_html( $tpk_pont['text'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ÚTIKALAUZ / BLOG -->
    <?php if ( $tpk_cikkek instanceof WP_Query && $tpk_cikkek->have_posts() ) : ?>
        <section id="blog" class="tpk-blog">
            <div class="tpk-section-head">
                <div>
                    <p class="tpk-eyebrow">Útikalauz</p>
                    <h2 class="tpk-section-title">Olvasnivaló indulás előtt</h2>
                </div>
                <a class="tpk-section-link" href="<?php echo esc_url( $tpk_url_utikalauz ); ?>">Az összes cikk →</a>
            </div>
            <div class="tpk-grid-3">
                <?php while ( $tpk_cikkek->have_posts() ) : $tpk_cikkek->the_post();
                    $tpk_id         = get_the_ID();
                    $tpk_kategoriak = get_the_category();
                    $tpk_kategoria  = ! empty( $tpk_kategoriak ) ? $tpk_kategoriak[0]->name : 'Útikalauz';
                    ?>
                    <article class="tpk-article-card">
                        <div class="tpk-article-media<?php echo has_post_thumbnail() ? '' : ' tpk-article-media--placeholder'; ?>"
                             <?php if ( has_post_thumbnail() ) : ?>style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $tpk_id, 'medium_large' ) ); ?>');"<?php endif; ?>>
                            <?php if ( ! has_post_thumbnail() ) : ?><span class="tpk-photo-note">cikk fotó helye</span><?php endif; ?>
                        </div>
                        <div class="tpk-article-body">
                            <p class="tpk-article-category"><?php echo esc_html( $tpk_kategoria ); ?></p>
                            <h3 class="tpk-article-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="tpk-article-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16 ) ); ?></p>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- ZÁRÓ CTA -->
    <section class="tpk-cta">
        <div>
            <h2 class="tpk-cta-title"><?php echo esc_html( apply_filters( 'tpk_zaro_cta_cim', 'Kész az utad? Nézz körbe az ajánlatok között.' ) ); ?></h2>
            <p class="tpk-cta-subtitle"><?php echo esc_html( apply_filters( 'tpk_zaro_cta_alcim', 'Kövess minket, hogy elsőként lásd az új ajánlatokat.' ) ); ?></p>
        </div>
        <div class="tpk-cta-actions">
            <div class="tpk-social">
                <?php $tpk_social = apply_filters( 'tpk_kozossegi_linkek', array( 'instagram' => '#', 'facebook' => '#' ) ); ?>
                <?php if ( ! empty( $tpk_social['instagram'] ) ) : ?>
                    <a class="tpk-social-btn" href="<?php echo esc_url( $tpk_social['instagram'] ); ?>" aria-label="Instagram"><span class="tpk-social-icon-instagram"></span></a>
                <?php endif; ?>
                <?php if ( ! empty( $tpk_social['facebook'] ) ) : ?>
                    <a class="tpk-social-btn" href="<?php echo esc_url( $tpk_social['facebook'] ); ?>" aria-label="Facebook"><span class="tpk-social-icon-facebook"></span></a>
                <?php endif; ?>
            </div>
            <a class="tpk-btn-accent tpk-btn-accent--cta" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>"><?php echo esc_html( apply_filters( 'tpk_zaro_cta_gomb_szoveg', 'Nézd meg az összes ajánlatot' ) ); ?></a>
        </div>
    </section>

    <?php tpk_render_footer(); ?>

</div>

<?php wp_footer(); ?>
</body>
</html>

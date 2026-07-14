<?php
/**
 * Kezdőlap-modul: ÚTICÉLOK
 * A markup a korábbi templates/front-page.php-ból byte-hűen kiemelve.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_kezi_idk = ( 'kezi' === $tpk_b['valogatas'] && ! empty( $tpk_b['kivalasztott_idk'] ) ) ? array_map( 'absint', (array) $tpk_b['kivalasztott_idk'] ) : array();
$tpk_orszagok = $tpk_kezi_idk ? tpk_get_orszagok( count( $tpk_kezi_idk ), $tpk_kezi_idk ) : tpk_get_orszagok( (int) $tpk_b['darab'] );
$tpk_stilus   = tpk_megj( $tpk_b, 'stilus', 'magazin' );
?>
    <!-- ÚTICÉLOK -->
    <section id="destinations" class="tpk-destinations<?php echo esc_attr( tpk_megj_osztalyok( 'uticelok', $tpk_b ) ); ?>"<?php echo tpk_megj_stilus( $tpk_b ); ?>>
        <div class="tpk-section-head-centered">
            <p class="tpk-eyebrow tpk-eyebrow--onDark"><?php echo esc_html( $tpk_b['eyebrow'] ); ?></p>
            <h2 class="tpk-section-title tpk-section-title--onDark"><?php echo esc_html( $tpk_b['cim'] ); ?></h2>
        </div>

        <?php if ( $tpk_orszagok instanceof WP_Query && $tpk_orszagok->have_posts() ) : ?>
            <div class="tpk-grid-3">
                <?php while ( $tpk_orszagok->have_posts() ) : $tpk_orszagok->the_post();
                    $tpk_id     = get_the_ID();
                    $tpk_blurb  = tpu_mezo( $tpk_id, 'tpu_leiras' );
                    if ( 'mozaik' === $tpk_stilus ) : ?>
                    <a class="tpk-dest-mozaik" href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <img src="<?php echo esc_url( get_the_post_thumbnail_url( $tpk_id, 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
                        <?php else : ?>
                            <span class="tpk-dest-mozaik-ures"></span>
                        <?php endif; ?>
                        <span class="tpk-dest-mozaik-nev"><?php the_title(); ?></span>
                    </a>
                    <?php elseif ( 'kartya' === $tpk_stilus ) : ?>
                    <a class="tpk-dest-csempe" href="<?php the_permalink(); ?>">
                        <span class="tpk-dest-csempe-kep">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <img src="<?php echo esc_url( get_the_post_thumbnail_url( $tpk_id, 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
                            <?php endif; ?>
                        </span>
                        <span class="tpk-dest-csempe-torzs">
                            <span class="tpk-dest-csempe-nev"><?php the_title(); ?></span>
                            <?php if ( $tpk_blurb ) : ?><span class="tpk-dest-csempe-blurb"><?php echo esc_html( $tpk_blurb ); ?></span><?php endif; ?>
                        </span>
                    </a>
                    <?php else : ?>
                    <a class="tpk-dest-card<?php echo has_post_thumbnail() ? '' : ' tpk-dest-card--placeholder'; ?>"
                       href="<?php the_permalink(); ?>"
                       <?php if ( has_post_thumbnail() ) : ?>style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $tpk_id, 'medium_large' ) ); ?>');"<?php endif; ?>>
                        <span class="tpk-dest-overlay"></span>
                        <span class="tpk-dest-content">
                            <span class="tpk-dest-name"><?php the_title(); ?></span>
                            <?php if ( $tpk_blurb ) : ?><span class="tpk-dest-blurb"><?php echo esc_html( $tpk_blurb ); ?></span><?php endif; ?>
                        </span>
                    </a>
                <?php endif;
                endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p class="tpk-empty tpk-empty--onDark"><?php echo esc_html( apply_filters( 'tpk_uticelok_ures_szoveg', $tpk_b['ures_szoveg'] ) ); ?></p>
        <?php endif; ?>
    </section>

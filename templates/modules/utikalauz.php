<?php
/**
 * Kezdőlap-modul: ÚTIKALAUZ / BLOG
 * A markup a korábbi templates/front-page.php-ból byte-hűen kiemelve.
 * Csak akkor jelenik meg, ha van publikált bejegyzés (mint eddig).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_cikkek        = tpk_get_cikkek( (int) $tpk_b['darab'] );
$tpk_url_utikalauz = tpk_utikalauz_url();
?>
    <!-- ÚTIKALAUZ / BLOG -->
    <?php if ( $tpk_cikkek instanceof WP_Query && $tpk_cikkek->have_posts() ) : ?>
        <section id="blog" class="tpk-blog<?php echo esc_attr( tpk_megj_osztalyok( 'utikalauz', $tpk_b ) ); ?>">
            <div class="tpk-section-head">
                <div>
                    <p class="tpk-eyebrow"><?php echo esc_html( $tpk_b['eyebrow'] ); ?></p>
                    <h2 class="tpk-section-title"><?php echo esc_html( $tpk_b['cim'] ); ?></h2>
                </div>
                <a class="tpk-section-link" href="<?php echo esc_url( $tpk_url_utikalauz ); ?>"><?php echo esc_html( $tpk_b['link_szoveg'] ); ?></a>
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

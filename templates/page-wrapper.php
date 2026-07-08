<?php
/**
 * Travelpont Kezdőoldal – általános oldal-keret (template_include-dal betöltve)
 *
 * A `templates/front-page.php` mellett ez adja a TELJES HTML dokumentumot
 * minden más frontend URL-en (lásd `includes/template-loader.php` –
 * `tpk_is_managed_request()`), hogy a Rólunk/Kapcsolat/Ajánlatok/Úticélok/
 * Útikalauz/egyedi bejegyzés oldalak ugyanazt a brandelt nav-ot/footert
 * kapják, mint a főoldal, függetlenül az aktív témától.
 *
 * - Egyedi Oldal/bejegyzés (is_singular()): cím + the_content() – az
 *   Ajánlatok/Úticélok pluginek saját `the_content` szűrője itt is lefut,
 *   tehát az egyedi Ajánlat/Úticél doboz automatikusan megjelenik.
 * - Bejegyzés-index (is_home(), az "Útikalauz" oldal): a standard WP Loop
 *   kártyás rácsban, a főoldali Blog-szekció márkázásával.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_page_title = wp_get_document_title();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $tpk_page_title ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'tpk-managed-page' ); ?>>
<?php wp_body_open(); ?>

<div class="tpk-page">

    <?php tpk_render_nav(); ?>

    <main class="tpk-content-wrap">
        <?php if ( is_home() ) : ?>

            <div class="tpk-content-inner tpk-content-inner--wide">
                <div class="tpk-page-head">
                    <h1 class="tpk-page-title"><?php echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ?: 'Útikalauz' ); ?></h1>
                </div>

                <?php if ( have_posts() ) : ?>
                    <div class="tpk-grid-3">
                        <?php while ( have_posts() ) : the_post();
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
                                    <p class="tpk-article-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    <div class="tpk-pagination"><?php the_posts_pagination(); ?></div>
                <?php else : ?>
                    <p class="tpk-empty">Jelenleg nincs publikált cikk – nézz vissza hamarosan!</p>
                <?php endif; ?>
            </div>

        <?php else :
            $tpk_raw_content = get_queried_object_id() ? get_post_field( 'post_content', get_queried_object_id() ) : '';
            $tpk_wide        = $tpk_raw_content && ( has_shortcode( $tpk_raw_content, 'travelpont_ajanlatok' ) || has_shortcode( $tpk_raw_content, 'travelpont_uticelok' ) );
            ?>
            <div class="tpk-content-inner <?php echo $tpk_wide ? 'tpk-content-inner--wide' : 'tpk-content-inner--narrow'; ?>">
                <?php while ( have_posts() ) : the_post(); ?>
                    <div class="tpk-page-head">
                        <h1 class="tpk-page-title"><?php the_title(); ?></h1>
                    </div>
                    <div class="tpk-page-content">
                        <?php the_content(); ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php tpk_render_footer(); ?>

</div>

<?php wp_footer(); ?>
</body>
</html>

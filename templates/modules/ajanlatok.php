<?php
/**
 * Kezdőlap-modul: KIEMELT AJÁNLATOK
 * A markup a korábbi templates/front-page.php-ból byte-hűen kiemelve.
 * Válogatás: 'auto' = legfrissebb, nem lejárt ajánlatok; 'kezi' = a
 * Portálon kiválasztott ajánlatok, a kiválasztás sorrendjében (a lejárt
 * pinelt ajánlat automatikusan kimarad).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_url_ajanlatok = tpk_ajanlatok_url();

$tpk_kezi_idk  = ( 'kezi' === $tpk_b['valogatas'] && ! empty( $tpk_b['kivalasztott_idk'] ) ) ? array_map( 'absint', (array) $tpk_b['kivalasztott_idk'] ) : array();
$tpk_ajanlatok = $tpk_kezi_idk ? tpk_get_ajanlatok( count( $tpk_kezi_idk ), $tpk_kezi_idk ) : tpk_get_ajanlatok( (int) $tpk_b['darab'] );
?>
    <!-- KIEMELT AJÁNLATOK -->
    <section id="offers" class="tpk-section<?php echo esc_attr( tpk_megj_osztalyok( 'ajanlatok', $tpk_b ) ); ?>"<?php echo tpk_megj_stilus( $tpk_b ); ?>>
        <div class="tpk-section-head">
            <div>
                <p class="tpk-eyebrow"><?php echo esc_html( $tpk_b['eyebrow'] ); ?></p>
                <h2 class="tpk-section-title"><?php echo esc_html( $tpk_b['cim'] ); ?></h2>
            </div>
            <a class="tpk-section-link" href="<?php echo esc_url( $tpk_url_ajanlatok ); ?>"><?php echo esc_html( $tpk_b['link_szoveg'] ); ?></a>
        </div>

        <?php if ( $tpk_ajanlatok instanceof WP_Query && $tpk_ajanlatok->have_posts() ) : ?>
            <div class="tpk-grid-3">
                <?php while ( $tpk_ajanlatok->have_posts() ) : $tpk_ajanlatok->the_post();
                    $tpk_id       = get_the_ID();
                    $tpk_dest     = tpa_mezo( $tpk_id, 'tpa_celallomas' );
                    $tpk_dest     = $tpk_dest ? $tpk_dest : get_the_title();
                    $tpk_idopont  = tpa_idopont_megjelenites( $tpk_id ); // dátumokból képzett kiírás vagy kézi szöveg
                    $tpk_ejszakak = tpa_ejszakak_szam( $tpk_id );        // dátumokból számolva vagy kézi érték
                    $tpk_szallas_nev = tpa_mezo( $tpk_id, 'tpa_szallas_nev' );
                    $tpk_datum_reszek = array();
                    if ( $tpk_idopont !== '' ) $tpk_datum_reszek[] = $tpk_idopont;
                    if ( $tpk_ejszakak !== '' ) $tpk_datum_reszek[] = $tpk_ejszakak . ' éj';
                    $tpk_datum    = implode( ' · ', $tpk_datum_reszek );
                    $tpk_cimke    = tpk_ajanlat_cimke( $tpk_id );
                    $tpk_repjegy  = tpa_mezo( $tpk_id, 'tpa_repjegy_ar' );
                    $tpk_busz_ar  = tpa_mezo( $tpk_id, 'tpa_busz_ar' );
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
                                <?php // A repjegy/buszjegy mező FŐNKÉNTI árat tárol (Ajánlatok plugin v1.14+) ?>
                                <?php if ( $tpk_repjegy !== '' ) : ?>
                                    <div class="tpk-price-row">
                                        <span>✈ Repjegy (oda-vissza)</span>
                                        <span><?php echo esc_html( tpa_ar_format( $tpk_repjegy ) ); ?>/fő</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $tpk_busz_ar !== '' ) : ?>
                                    <div class="tpk-price-row">
                                        <span>🚌 Buszjegy (oda-vissza)</span>
                                        <span><?php echo esc_html( tpa_ar_format( $tpk_busz_ar ) ); ?>/fő</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $tpk_szallas !== '' ) : ?>
                                    <div class="tpk-price-row">
                                        <span>🏨 <?php echo $tpk_szallas_nev !== '' ? esc_html( $tpk_szallas_nev ) : 'Szállás'; ?><?php echo $tpk_ejszakak !== '' ? ' (' . esc_html( $tpk_ejszakak ) . ' éj)' : ''; ?></span>
                                        <span><?php echo esc_html( tpa_ar_format( $tpk_szallas ) ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $tpk_osszesen !== '' ) : ?>
                                    <div class="tpk-price-divider"></div>
                                    <div class="tpk-price-row tpk-price-total">
                                        <span>Összesen</span>
                                        <?php // "-tól" toldalék az Ajánlatok plugin v1.19+ helperével (régebbi pluginnal sima ár) ?>
                                        <span><?php echo esc_html( function_exists( 'tpa_osszeg_format' ) ? tpa_osszeg_format( $tpk_id, $tpk_osszesen ) : tpa_ar_format( $tpk_osszesen ) ); ?></span>
                                    </div>
                                    <p class="tpk-price-megjegyzes"><?php echo esc_html( tpa_ar_megjegyzes_megjelenites( $tpk_id ) ); ?></p>
                                <?php endif; ?>
                            </div>

                            <a class="tpk-btn-accent" href="<?php the_permalink(); ?>">Részletek</a>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p class="tpk-empty"><?php echo esc_html( apply_filters( 'tpk_ajanlatok_ures_szoveg', $tpk_b['ures_szoveg'] ) ); ?></p>
        <?php endif; ?>
    </section>

<?php
/**
 * Generates the ads in the plugin.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the advertisements that are used throughout the plugin page.
 *
 * The advertisements are randomly generated every time the page is loaded. The function also handles the CSS for this.
 *
 * @since 6.2.0
 */
function qsm_show_adverts() {

	// Checks if the option for showing ads if True. Will be false if the No Ads addon is installed.
	if ( 'true' == get_option( 'mlw_advert_shows' ) && hide_qsm_adv !== TRUE ) {

		global $mlwQuizMasterNext;
		wp_enqueue_style( 'qsm_admin_style', plugins_url( '../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
		wp_style_add_data( 'qsm_admin_style', 'rtl', 'replace' );
                if ( false === get_transient('qsm_ads_data') ) {
                    $xml = qsm_fetch_data_from_xml();
                    if ( isset($xml->qsm_ads) ) {
                        $all_ads = $xml->qsm_ads;
                        $json_ads = wp_json_encode($all_ads);
                        $all_ads = $array_into_ads = json_decode($json_ads,TRUE);                        
                        set_transient( 'qsm_ads_data', $array_into_ads, 60 * 60 * 24 );
                    }
                }else {
                    $all_ads = get_transient('qsm_ads_data');
                }
                $count_ads = count($all_ads['ads']);
                $ad_text  = '';
		        $rand_int = wp_rand( 0, $count_ads - 1 );
                $link = '<a target="_blank" href="'. $all_ads['ads'][ $rand_int ]['link'] .'">'. $all_ads['ads'][ $rand_int ]['link_text'] .'</a>';
                $link = str_replace('#38', '&', $link);
                $ad_text = str_replace('[link]', $link, $all_ads['ads'][ $rand_int ]['text']);
		?>
<div class="help-decide">
	<p><?php echo wp_kses_post( $ad_text ) . ' <a class="remove-adv-button" target="_blank" href="https://quizandsurveymaster.com/downloads/advertisement-gone/"><span class="dashicons dashicons-no-alt"></span> Remove Ads</a>'; ?>
	</p>
</div>
<?php
	}
}
?>
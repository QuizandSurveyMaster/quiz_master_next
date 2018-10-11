<?php
/**
 * Handles relevant functions for results pages
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class contains functions for loading, saving, and generating results pages.
 *
 * @since 6.1.0
 */
class QSM_Results_Pages {

	/**
	 * Creates the HTML for the results page.
	 *
	 * @since 6.1.0
	 * @return string The HTML for the page to be displayed.
	 */
	public static function generate_pages( $response_data ) {
		$pages = QSM_Results_Pages::load_pages( $response_data['quiz_id'] );
		ob_start();
		?>
		<div class="qsm-results-page">
			<?php
			do_action( 'qsm_before_results_page' );

			// Cycles through each possible page.
			foreach ( $pages as $page ) {

				// Checks if this page's conditions match this response.
				if ( true ) {

					// Decodes special characters, runs through our template
					// variables, and then outputs the text.
					$page = htmlspecialchars_decode( $page, ENT_QUOTES );
					$page = apply_filters( 'mlw_qmn_template_variable_results_page', $page, $response_data );
					$page = str_replace( "\n", '<br>', $page );
					echo $page;
				}
			}
			do_action( 'qsm_after_results_page' );
			?>
		</div>
		<?php
		return do_shortcode( ob_get_clean() );
	}

	/**
	 * Loads the results pages for a single quiz.
	 *
	 * @since 6.1.0
	 * @param int $quiz_id The ID for the quiz.
	 * @return bool|array The array of pages or false.
	 */
	public static function load_pages( $quiz_id ) {
		$pages   = array();
		$quiz_id = intval( $quiz_id );

		// If the parameter supplied turns to 0 after intval, returns false.
		if ( 0 === $quiz_id ) {
			return false;
		}
		return $pages;
	}

	/**
	 * Saves the results pages for a quiz.
	 *
	 * @since 6.1.0
	 * @param int   $quiz_id The ID for the quiz.
	 * @param array $pages The results pages to be saved.
	 * @return bool True or false depending on success.
	 */
	public static function save_pages( $quiz_id, $pages ) {
		return true;
	}
}
?>

<?php
/**
 * Creates the view for all tabs for editing the quiz.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function allows for the editing of quiz options.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_generate_quiz_options() {

	// Checks if current user can.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
        global $wpdb;
	global $mlwQuizMasterNext;
        
        //Check user capability
        $user = wp_get_current_user();
        if ( in_array( 'author', (array) $user->roles ) ) {
            $user_id = sanitize_text_field($user->ID);
            $quiz_id = isset( $_GET['quiz_id'] ) ? intval($_GET['quiz_id']) : 0;
            $quiz_author_id = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_author_id FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d AND quiz_author_id=%d LIMIT 1", $quiz_id, $user_id) );
            if(!$quiz_author_id){
                wp_die('You are not allow to edit this quiz, You need higher permission!');
            }
        }

	$quiz_name = '';

	// Gets registered tabs for the options page and set current tab.
	$tab_array  = $mlwQuizMasterNext->pluginHelper->get_settings_tabs();
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET[ 'tab' ] ) ? stripslashes( $_GET[ 'tab' ] ) : __( 'Questions', 'quiz-master-next' ) ) );

	// Prepares quiz.
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	if ( isset( $_GET['quiz_id'] ) ) {
		$quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	}

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'qmn_datetime_js', plugins_url( '../../js/jquery.datetimepicker.full.min.js', __FILE__ ) );
        wp_enqueue_style( 'qsm_datetime_style', plugins_url( '../../css/jquery.datetimepicker.css', __FILE__ ) );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );

	wp_enqueue_script( 'qmn_admin_js', plugins_url( '../../js/admin.js', __FILE__ ), array( 'backbone', 'underscore', 'wp-util' ), $mlwQuizMasterNext->version, true );
        wp_enqueue_script( 'micromodal_script', plugins_url( '../../js/micromodal.min.js', __FILE__ ) );
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_enqueue_style( 'qmn_jquery_redmond_theme', plugins_url( '../../css/jquery-ui.css', __FILE__ ) );
	wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );
        wp_localize_script('qmn_admin_js', 'qsmTextTabObject', array( 'quiz_id' => $quiz_id ));
        // Edit Quiz Name.
	if ( isset( $_POST['qsm_edit_name_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_edit_name_quiz_nonce'], 'qsm_edit_name_quiz' ) ) {
            //$quiz_id   = intval( $_POST['edit_quiz_id'] );
            $quiz_name = sanitize_text_field( htmlspecialchars( stripslashes( $_POST['edit_quiz_name'] ), ENT_QUOTES ) );
            $mlwQuizMasterNext->quizCreator->edit_quiz_name( $quiz_id, $quiz_name );
	}
        //Update post status
        if ( isset( $_POST['qsm_update_quiz_status_nonce'] ) && wp_verify_nonce( $_POST['qsm_update_quiz_status_nonce'], 'qsm_update_quiz_status' ) ) {            
            $quiz_post_id = sanitize_text_field( $_POST['quiz_post_id'] );
            $arg_post_arr = array(
                'ID'           => $quiz_post_id,
                'post_status'   => 'publish',
            );        
            $update_status = wp_update_post( $arg_post_arr );
            if ( false !== $update_status ) {
                $mlwQuizMasterNext->alertManager->newAlert( __( 'Quiz status has been updated successfully to publish.', 'quiz-master-next' ), 'success' );
                $mlwQuizMasterNext->audit_manager->new_audit( "Quiz/Survey Status Has Been Updated: $quiz_post_id" );
            } else {                    
                $mlwQuizMasterNext->alertManager->newAlert( __( 'An error occurred while trying to update the status of your quiz or survey. Please try again.', 'quiz-master-next' ), 'error' );
                $mlwQuizMasterNext->log_manager->add( 'Error when updating quiz status', "", 0, 'error' );
            }
	}
	?>
        <?php
        // Get quiz post based on quiz id
        $args = array(
            'posts_per_page' => 1,
            'post_type' => 'qsm_quiz',
            'meta_query' => array(
                array(
                    'key' => 'quiz_id',
                    'value' => $quiz_id,
                    'compare' => '=',
                ),
            ),
        );
        $the_query = new WP_Query($args);

        // The Loop
        $post_status = $post_id = $post_permalink = $edit_link = '';
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {                
                $the_query->the_post();
                $post_permalink = get_the_permalink(get_the_ID());
                $post_status = get_post_status( get_the_ID() );
                $edit_link = get_edit_post_link(get_the_ID());
                $post_id = get_the_ID();
            }
            /* Restore original Post Data */
            wp_reset_postdata();
        }
        ?>    
	<div class="wrap">            
		<div class='mlw_quiz_options'>
                    <h1 style="margin-bottom: 10px;">
                        <?php echo $quiz_name; ?>
                        <?php
                        if( $post_status == 'draft' ){ ?>
                            <form method="POST" action="" style="display: inline-block; margin-right: 10px;float:right;">
                                <?php wp_nonce_field( 'qsm_update_quiz_status','qsm_update_quiz_status_nonce' ); ?>
                                <input type="hidden" name="quiz_post_id" value="<?php echo $post_id; ?>" />
                                <input type="submit" class="button button-primary" value="<?php _e('Publish Quiz', 'quiz-master-next'); ?>" />
                            </form>
                        <?php                         
                        }
                        ?>     
                        <a href="#" title="Edit Name" class="edit-quiz-name">
                        <span class="dashicons dashicons-edit"></span>
                        </a>
                        <a class="button button-default qsm-btn-quiz-edit" rel="noopener" target="_blank" href="<?php echo $post_permalink; ?>">
                            <span class="dashicons dashicons-welcome-view-site"></span>
                        </a>
                        <a class="button button-default qsm-btn-quiz-edit" href="<?php echo $edit_link; ?>">
                            <span class="dashicons dashicons-admin-settings"></span>
                        </a>
                    </h1>                                        
			<?php
			// Puts all output from tab into ob_get_contents below.
			ob_start();

			// If the quiz is set and not empty.
			if ( $quiz_id ) {
				?>
				<nav class="nav-tab-wrapper">
					<?php
					// Cycles through registered tabs to create navigation.
					foreach ( $tab_array as $tab ) {
						$active_class = '';
						if ( $active_tab == $tab['slug'] ) {
							$active_class = 'nav-tab-active';
						}
						?>
						<a href="?page=mlw_quiz_options&quiz_id=<?php echo esc_attr( $quiz_id ); ?>&tab=<?php echo esc_attr( $tab['slug'] ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
						<?php
					}
					?>
				</nav>
				<div class="qsm_tab_content">
					<?php
					// Cycles through tabs looking for current tab to create tab's content.
					foreach ( $tab_array as $tab ) {
						if ( $active_tab == $tab['slug'] ) {
							call_user_func( $tab['function'] );
						}
					}
					?>
				</div>
				<?php
			} else {                            
				?>
				<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
					<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<strong><?php esc_html_e( 'Error!', 'quiz-master-next' ); ?></strong> <?php esc_html_e( 'Please go to the quizzes page and click on the Edit link from the quiz you wish to edit.', 'quiz-master-next' ); ?></p>
				</div>
				<?php
			}
			$mlw_output = ob_get_contents();
			ob_end_clean();

			// Shows alerts, ads, then tab content.
			?>
			<div class="qsm-alerts">
				<?php
				$mlwQuizMasterNext->alertManager->showAlerts();
				?>
			</div>
			<?php
			qsm_show_adverts();
			echo $mlw_output;
			?>
		</div>
                <div class="qsm-popup qsm-popup-slide" id="modal-3" aria-hidden="false">
                    <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
                        <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-3-title">
                            <header class="qsm-popup__header">
                                    <h2 class="qsm-popup__title" id="modal-3-title">Edit Name</h2>
                                    <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
                            </header>
                            <main class="qsm-popup__content" id="modal-3-content">
                                    <form action='' method='post' id="edit-name-form">
                                            <label><?php _e( 'Name', 'quiz-master-next' ); ?></label>
                                            <input type="text" id="edit_quiz_name" name="edit_quiz_name" value="<?php echo $quiz_name; ?>" />
                                            <input type="hidden" id="edit_quiz_id" name="edit_quiz_id" value="<?php echo isset($_GET['quiz_id']) && is_int($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : '0'; ?>" />
                                            <?php wp_nonce_field( 'qsm_edit_name_quiz', 'qsm_edit_name_quiz_nonce' ); ?>
                                    </form>
                            </main>
                            <footer class="qsm-popup__footer">
                                    <button id="edit-name-button" class="qsm-popup__btn qsm-popup__btn-primary">Edit</button>
                                    <button class="qsm-popup__btn" data-micromodal-close="" aria-label="Close this dialog window">Cancel</button>
                            </footer>
                        </div>
                    </div>
		</div>
	</div>

	<!-- Backbone Views -->

	<!-- View for Notices -->
	<script type="text/template" id="tmpl-notice">
		<div class="notice notice-large notice-{{data.type}}">
			<p>{{data.message}}</p>
		</div>
	</script>
<?php
}
?>

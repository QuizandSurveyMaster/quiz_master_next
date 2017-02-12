<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Generates the quizzes page where the quizzes are displayed.
*
* @return void
* @since 4.4.0
*/
function mlw_generate_quiz_admin()
{
	if ( !current_user_can('moderate_comments') )
	{
		return;
	}
	global $wpdb;
	global $mlwQuizMasterNext;
	$table_name = $wpdb->prefix . "mlw_quizzes";

	//Create new quiz
	if ( isset( $_POST["create_quiz"] ) && $_POST["create_quiz"] == "confirmation" )
	{
		$quiz_name = htmlspecialchars(stripslashes( $_POST["quiz_name"] ), ENT_QUOTES);
		$mlwQuizMasterNext->quizCreator->create_quiz($quiz_name);
	}

	//Delete quiz
	if (isset( $_POST["delete_quiz"] ) && $_POST["delete_quiz"] == "confirmation")
	{
		$mlw_quiz_id = intval($_POST["quiz_id"]);
		$quiz_name = sanitize_text_field( $_POST["delete_quiz_name"] );
		$mlwQuizMasterNext->quizCreator->delete_quiz($mlw_quiz_id, $quiz_name);
	}

	//Edit Quiz Name
	if (isset($_POST["quiz_name_editted"]) && $_POST["quiz_name_editted"] == "confirmation")
	{
		$mlw_edit_quiz_id = intval($_POST["edit_quiz_id"]);
		$mlw_edit_quiz_name = htmlspecialchars( stripslashes( $_POST["edit_quiz_name"] ), ENT_QUOTES);
		$mlwQuizMasterNext->quizCreator->edit_quiz_name($mlw_edit_quiz_id, $mlw_edit_quiz_name);
	}

	//Duplicate Quiz
	if (isset($_POST["duplicate_quiz"]) && $_POST["duplicate_quiz"] == "confirmation")
	{
		$mlw_duplicate_quiz_id = intval($_POST["duplicate_quiz_id"]);
		$mlw_duplicate_quiz_name = htmlspecialchars($_POST["duplicate_new_quiz_name"], ENT_QUOTES);
		$mlwQuizMasterNext->quizCreator->duplicate_quiz($mlw_duplicate_quiz_id, $mlw_duplicate_quiz_name, isset($_POST["duplicate_questions"]));
	}

	//Retrieve list of quizzes
	global $wpdb;
	$mlw_qmn_table_limit = 25;
	$mlw_qmn_quiz_count = $wpdb->get_var( "SELECT COUNT(quiz_id) FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0'" );

	if( isset($_GET{'mlw_quiz_page'} ) )
	{
	   $mlw_qmn_quiz_page = $_GET{'mlw_quiz_page'} + 1;
	   $mlw_qmn_quiz_begin = $mlw_qmn_table_limit * $mlw_qmn_quiz_page ;
	}
	else
	{
	   $mlw_qmn_quiz_page = 0;
	   $mlw_qmn_quiz_begin = 0;
	}
	$mlw_qmn_quiz_left = $mlw_qmn_quiz_count - ($mlw_qmn_quiz_page * $mlw_qmn_table_limit);
	$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT quiz_id, quiz_name, quiz_views, quiz_taken, last_activity
		FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0'
		ORDER BY quiz_id DESC LIMIT %d, %d", $mlw_qmn_quiz_begin, $mlw_qmn_table_limit ) );

	$post_to_quiz_array = array();
	$my_query = new WP_Query( array('post_type' => 'quiz') );
	if( $my_query->have_posts() )
	{
	  while( $my_query->have_posts() )
		{
	    $my_query->the_post();
			$post_to_quiz_array[get_post_meta( get_the_ID(), 'quiz_id', true )] = array(
				'link' => get_permalink(),
				'id' => get_the_ID()
			);
	  }
	}
	wp_reset_postdata();
	wp_enqueue_style( 'qmn_admin_style', plugins_url( '../css/qmn_admin.css' , __FILE__ ) );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	wp_enqueue_style( 'qmn_jquery_redmond_theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css' );
	?>
	<script type="text/javascript"
	  src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
	</script>
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;
		$j(function() {
			$j('#new_quiz_dialog').dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});

			$j('#new_quiz_button').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
			$j('#new_quiz_button_two').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
		});
		function deleteQuiz(id,quizName){
			$j("#delete_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#delete_dialog").dialog('open');
			var idHidden = document.getElementById("quiz_id");
			var idHiddenName = document.getElementById("delete_quiz_name");
			idHidden.value = id;
			idHiddenName.value = quizName;
		};
		function editQuizName(id, quizName){
			$j("#edit_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#edit_dialog").dialog('open');
			document.getElementById("edit_quiz_name").value = quizName;
			document.getElementById("edit_quiz_id"). value = id;
		}
		function duplicateQuiz(id, quizName){
			$j("#duplicate_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#duplicate_dialog").dialog('open');
			document.getElementById("duplicate_quiz_id"). value = id;
		}
	</script>
	<div class="wrap qsm-quizes-page">
		<h1><?php _e('Quizzes/Surveys', 'quiz-master-next'); ?><a id="new_quiz_button" href="javascript:();" class="add-new-h2"><?php _e('Add New', 'quiz-master-next'); ?></a></h1>
		<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
		<div class="qsm-quizzes-page-content">
			<div class="<?php if ( get_option( 'mlw_advert_shows' ) != 'false' ) { echo 'qsm-quiz-page-wrapper-with-ads'; } else { echo 'qsm-quiz-page-wrapper'; } ?>">
				<div class="tablenav top">
					<div class="tablenav-pages">
						<span class="displaying-num"><?php echo sprintf(_n('One quiz or survey', '%s quizzes or surveys', $mlw_qmn_quiz_count, 'quiz-master-next'), number_format_i18n($mlw_qmn_quiz_count)); ?></span>
						<span class="pagination-links">
							<?php
							$mlw_qmn_previous_page = 0;
							$mlw_current_page = $mlw_qmn_quiz_page+1;
							$mlw_total_pages = ceil($mlw_qmn_quiz_count/$mlw_qmn_table_limit);
							if( $mlw_qmn_quiz_page > 0 )
							{
							   	$mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
							   	echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
								echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
							   	if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
							   	{
									echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
							   	}
								else
								{
									echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
							   	}
							}
							else if( $mlw_qmn_quiz_page == 0 )
							{
							   if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
							   {
									echo "<a class=\"prev-page disabled\" title=\"Go to the previous page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
									echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
									echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
							   }
							}
							else if( $mlw_qmn_quiz_left < $mlw_qmn_table_limit )
							{
							   $mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
							   echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
								echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
								echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=quiz-master-next/mlw_quizmaster2.php&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
							}
							?>
						</span>
						<br class="clear">
					</div>
				</div>
				<table class="widefat">
					<thead>
						<tr>
							<th>ID</th>
							<th><?php _e('Name', 'quiz-master-next'); ?></th>
							<th><?php _e('URL', 'quiz-master-next'); ?></th>
							<th><?php _e('Shortcode', 'quiz-master-next'); ?></th>
							<th><?php _e('Leaderboard Shortcode', 'quiz-master-next'); ?></th>
							<th><?php _e('Views', 'quiz-master-next'); ?></th>
							<th><?php _e('Taken', 'quiz-master-next'); ?></th>
							<th><?php _e('Last Modified', 'quiz-master-next'); ?></th>
						</tr>
					</thead>
					<tbody id="the-list">
						<?php
						$quotes_list = "";
						$display = "";
						$alternate = "";
						foreach($mlw_quiz_data as $mlw_quiz_info) {
							if($alternate) $alternate = "";
							else $alternate = " class=\"alternate\"";
							$quotes_list .= "<tr{$alternate}>";
							$quotes_list .= "<td>" . $mlw_quiz_info->quiz_id . "</td>";
							$quotes_list .= "<td class='post-title column-title'>" . esc_html($mlw_quiz_info->quiz_name) ." <a class='qsm-edit-name' onclick=\"editQuizName('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='javascript:();'>(".__('Edit Name', 'quiz-master-next').")</a>";
							$quotes_list .= "<div class=\"row-actions\">
							<a class='qsm-action-link' href='admin.php?page=mlw_quiz_options&&quiz_id=".$mlw_quiz_info->quiz_id."'>".__('Edit', 'quiz-master-next')."</a>
							 | <a class='qsm-action-link' href='admin.php?page=mlw_quiz_results&&quiz_id=".$mlw_quiz_info->quiz_id."'>".__('Results', 'quiz-master-next')."</a>
							 | <a href='javascript:();' class='qsm-action-link' onclick=\"duplicateQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\">".__('Duplicate', 'quiz-master-next')."</a>
							 | <a class='qsm-action-link qsm-action-link-delete' onclick=\"deleteQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='javascript:();'>".__('Delete', 'quiz-master-next')."</a>
							</div></td>";
							if (isset($post_to_quiz_array[$mlw_quiz_info->quiz_id]))
							{
								$quotes_list .= "<td>
								<a href='".$post_to_quiz_array[$mlw_quiz_info->quiz_id]['link']."'>" . __( 'View Quiz/Survey', 'quiz-master-next' ) . "</a>
								<div class=\"row-actions\"><a class='linkOptions' href='post.php?post=".$post_to_quiz_array[$mlw_quiz_info->quiz_id]['id']."&action=edit'>Edit Post Settings</a></a>
								</td>";
							}
							else
							{
								$quotes_list .= "<td></td>";
							}
							$quotes_list .= "<td>[mlw_quizmaster quiz=".$mlw_quiz_info->quiz_id."]</td>";
							$quotes_list .= "<td>[mlw_quizmaster_leaderboard mlw_quiz=".$mlw_quiz_info->quiz_id."]</td>";
							$quotes_list .= "<td>" . $mlw_quiz_info->quiz_views . "</td>";
							$quotes_list .= "<td>" . $mlw_quiz_info->quiz_taken ."</td>";
							$quotes_list .= "<td>" . $mlw_quiz_info->last_activity ."</td>";
							$quotes_list .= "</tr>";
						}
						echo $quotes_list; ?>
					</tbody>
					<tfoot>
						<tr>
							<th>ID</th>
							<th><?php _e('Name', 'quiz-master-next'); ?></th>
							<th><?php _e('URL', 'quiz-master-next'); ?></th>
							<th><?php _e('Shortcode', 'quiz-master-next'); ?></th>
							<th><?php _e('Leaderboard Shortcode', 'quiz-master-next'); ?></th>
							<th><?php _e('Views', 'quiz-master-next'); ?></th>
							<th><?php _e('Taken', 'quiz-master-next'); ?></th>
							<th><?php _e('Last Modified', 'quiz-master-next'); ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
			<?php
			if ( get_option('mlw_advert_shows') == 'true' )
			{
				?>
				<div class="qsm-news-ads">
					<h3 class="qsm-news-ads-title">Quiz And Survey Master News</h3>
					<div class="qsm-news-ads-widget">
						<h3>Subscribe to our newsletter!</h3>
						<p>Join our mailing list and recevie a discount on your next purchase! Learn about our newest features, receive email-only promotions, receive tips and guides, and more!</p>
						<a target="_blank" href="http://quizandsurveymaster.com/subscribe-to-our-newsletter/?utm_source=qsm-quizzes-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=subscribe-to-newsletter" class="button-primary">Subscribe Now</a>
					</div>
					<?php
					$qmn_rss = array();
					$qmn_feed = fetch_feed('http://quizandsurveymaster.com/feed');
					if (!is_wp_error($qmn_feed)) {
						$qmn_feed_items = $qmn_feed->get_items(0, 5);
						foreach ($qmn_feed_items as $feed_item) {
						    $qmn_rss[] = array(
						        'link' => $feed_item->get_link(),
						        'title' => $feed_item->get_title(),
						        'description' => $feed_item->get_description(),
										'date' => $feed_item->get_date( 'F j Y' ),
										'author' => $feed_item->get_author()->get_name()
						    );
						}
					}
					foreach($qmn_rss as $item)
					{
						?>
						<div class="qsm-news-ads-widget">
							<h3><?php echo $item['title']; ?></h3>
							<p>By <?php echo $item['author']; ?></p>
							<div>
								<?php echo $item['description']; ?>
							</div>
							<a target='_blank' href="<?php echo $item['link']; ?>" class="button-primary">Read More</a>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
		<!--Dialogs-->

		<!--New Quiz Dialog-->
		<div id="new_quiz_dialog" title="Create New Quiz Or Survey" style="display:none;">
			<form action="" method="post" class="qsm-dialog-form">
				<input type='hidden' name='create_quiz' value='confirmation' />
				<h3><?php _e('Create New Quiz Or Survey', 'quiz-master-next'); ?></h3>
				<label><?php _e('Name', 'quiz-master-next'); ?></label><input type="text" name="quiz_name" value="" />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Create', 'quiz-master-next'); ?>' /></p>
			</form>
		</div>

		<!--Edit Quiz Name Dialog-->
		<div id="edit_dialog" title="Edit Name" style="display:none;">
			<form action='' method='post' class="qsm-dialog-form">
				<label><?php _e('Name', 'quiz-master-next'); ?></label>
				<input type="text" id="edit_quiz_name" name="edit_quiz_name" />
				<input type="hidden" id="edit_quiz_id" name="edit_quiz_id" />
				<input type='hidden' name='quiz_name_editted' value='confirmation' />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Edit', 'quiz-master-next'); ?>' /></p>
			</form>
		</div>

		<!--Duplicate Quiz Dialog-->
		<div id="duplicate_dialog" title="Duplicate Quiz Or Survey" style="display:none;">
			<form action='' method='post' class="qsm-dialog-form">
				<label for="duplicate_questions"><?php _e('Duplicate questions also?', 'quiz-master-next'); ?></label><input type="checkbox" name="duplicate_questions" id="duplicate_questions"/><br />
				<br />
				<label for="duplicate_new_quiz_name"><?php _e('Name Of New Quiz Or Survey:', 'quiz-master-next'); ?></label><input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" />
				<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
				<input type='hidden' name='duplicate_quiz' value='confirmation' />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Duplicate', 'quiz-master-next'); ?>' /></p>
			</form>
		</div>

		<!--Delete Quiz Dialog-->
		<div id="delete_dialog" title="Delete Quiz Or Survey?" style="display:none;">
		<form action='' method='post' class="qsm-dialog-form">
			<h3><b><?php _e('Are you sure you want to delete this quiz or survey?', 'quiz-master-next'); ?></b></h3>
			<input type='hidden' name='delete_quiz' value='confirmation' />
			<input type='hidden' id='quiz_id' name='quiz_id' value='' />
			<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
			<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Delete', 'quiz-master-next'); ?>' /></p>
		</form>
		</div>
	</div>
<?php
}
?>

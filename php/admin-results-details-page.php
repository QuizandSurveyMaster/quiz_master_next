<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
* This function generates the results details that are shown the results page.
*
* @return type void
* @since 4.4.0
*/
function mlw_generate_result_details() {
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}
	global $mlwQuizMasterNext;
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'results';
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_results_tabs();
	?>
	<div class="wrap">
		<h2><?php _e('Quiz Results', 'quiz-master-next'); ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( $tab_array as $tab ) {
				$active_class = '';
				if ( $active_tab == $tab['slug'] ) {
					$active_class = 'nav-tab-active';
				}
				echo "<a href=\"?page=mlw_quiz_result_details&&result_id=" . intval( $_GET["result_id"] ) . "&&tab=" . $tab['slug'] . "\" class=\"nav-tab $active_class\">" . $tab['title'] . "</a>";
			}
			?>
		</h2>
		<div>
		<?php
			foreach( $tab_array as $tab ) {
				if ( $active_tab == $tab['slug'] ) {
					call_user_func( $tab['function'] );
				}
			}
		?>
		</div>
	</div>
	<?php
}


/**
* This function generates the results details tab that shows the details of the quiz
*
* @param type description
* @return void
* @since 4.4.0
*/
function qmn_generate_results_details_tab() {
	echo "<br><br>";
	$mlw_result_id = intval( $_GET["result_id"] );
	global $wpdb;
	$mlw_results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_results WHERE result_id=%d", $mlw_result_id ) );

	$previous_results = $wpdb->get_var( "SELECT result_id FROM " . $wpdb->prefix . "mlw_results WHERE result_id = (SELECT MAX(result_id) FROM " . $wpdb->prefix . "mlw_results WHERE deleted=0 AND result_id < ".$mlw_result_id.")" );
	$next_results = $wpdb->get_var( "SELECT result_id FROM " . $wpdb->prefix . "mlw_results WHERE result_id = (SELECT MIN(result_id) FROM " . $wpdb->prefix . "mlw_results WHERE deleted=0 AND result_id > ".$mlw_result_id.")" );
	if ( ! is_null( $previous_results ) && $previous_results ) {
		echo "<a class='button' href=\"?page=mlw_quiz_result_details&&result_id=" . intval( $previous_results ) . "\" >View Previous Results</a> ";
	}
	if ( ! is_null( $next_results ) && $next_results ) {
		echo " <a class='button' href=\"?page=mlw_quiz_result_details&&result_id=" . intval( $next_results ) . "\" >View Next Results</a>";
	}
	$settings = (array) get_option( 'qmn-settings' );
	if ( isset( $settings['results_details_template'] ) ) {
		$template = htmlspecialchars_decode( $settings['results_details_template'], ENT_QUOTES );
	} else {
		$template = "<h2>Quiz Results for %QUIZ_NAME%</h2>
		<p>Name Provided: %USER_NAME%</p>
		<p>Business Provided: %USER_BUSINESS%</p>
		<p>Phone Provided: %USER_PHONE%</p>
		<p>Email Provided: %USER_EMAIL%</p>
		<p>Score Received: %AMOUNT_CORRECT%/%TOTAL_QUESTIONS% or %CORRECT_SCORE%% or %POINT_SCORE% points</p>
		<h2>Answers Provided:</h2>
		<p>The user took %TIMER% to complete quiz.</p>
		<p>Comments entered were: %COMMENT_SECTION%</p>
		<p>The answers were as follows:</p>
		%QUESTIONS_ANSWERS%";
	}
	if ( is_serialized( $mlw_results_data->quiz_results ) && is_array( @unserialize( $mlw_results_data->quiz_results ) ) ) {
		$results = unserialize($mlw_results_data->quiz_results);
	} else {
		$template = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_results_data->quiz_results, $template);
		$template = str_replace( "%TIMER%" , '', $template);
		$template = str_replace( "%COMMENT_SECTION%" , '', $template);
		$results = array(
			0,
			array(),
			''
		);
	}
	$qmn_array_for_variables = array(
		'quiz_id' => $mlw_results_data->quiz_id,
		'quiz_name' => $mlw_results_data->quiz_name,
		'quiz_system' => $mlw_results_data->quiz_system,
		'user_name' => $mlw_results_data->name,
		'user_business' => $mlw_results_data->business,
		'user_email' => $mlw_results_data->email,
		'user_phone' => $mlw_results_data->phone,
		'user_id' => $mlw_results_data->user,
		'timer' => $results[0],
		'time_taken' => $mlw_results_data->time_taken,
		'total_points' => $mlw_results_data->point_score,
		'total_score' => $mlw_results_data->correct_score,
		'total_correct' => $mlw_results_data->correct,
		'total_questions' => $mlw_results_data->total,
		'comments' => $results[2],
		'question_answers_array' => $results[1]
	);
	$template = apply_filters( 'mlw_qmn_template_variable_results_page', $template, $qmn_array_for_variables );
	$template = str_replace( "\n" , "<br>", $template );
	echo $template;
}


/**
* Generates the results details tab in the quiz results page
*
* @return void
* @since 4.4.0
*/
function qmn_results_details_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_results_settings_tab( __( "Results", 'quiz-master-next' ), "qmn_generate_results_details_tab" );
}
add_action( "plugins_loaded", 'qmn_results_details_tab' );


/**
* Creates the certificate in the certificate tab.
*
* @param type description
* @return type description
* @since 4.4.0
*/
function qmn_generate_results_certificate_tab() {
	//Check if user wants to create certificate
	if ( isset( $_POST["create_certificate"] ) && "confirmation" == $_POST["create_certificate"] ) {
		global $wpdb;
		$mlw_certificate_id = intval( $_GET["result_id"] );
		$mlw_quiz_results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."mlw_results WHERE result_id=%d", $mlw_certificate_id ) );

		$mlw_certificate_results = $wpdb->get_var( $wpdb->prepare( "SELECT certificate_template FROM ".$wpdb->prefix."mlw_quizzes WHERE quiz_id=%d", $mlw_quiz_results->quiz_id ) );

		//Prepare Certificate
		if ( is_serialized( $mlw_certificate_results ) && is_array( @unserialize( $mlw_certificate_results ) ) ) {
			$mlw_certificate_options = unserialize( $mlw_certificate_results );
		} else {
			$mlw_certificate_options = array( 'Enter title here', 'Enter text here', '', '' );
		}
		if ( is_serialized( $mlw_quiz_results->quiz_results ) && is_array( @unserialize( $mlw_quiz_results->quiz_results ) ) ) {
			$results = unserialize($mlw_quiz_results->quiz_results);
		} else {
			$results = array( 0, '', '' );
		}
		$qmn_array_for_variables = array(
			'quiz_id' => $mlw_quiz_results->quiz_id,
			'quiz_name' => $mlw_quiz_results->quiz_name,
			'quiz_system' => $mlw_quiz_results->quiz_system,
			'user_name' => $mlw_quiz_results->name,
			'user_business' => $mlw_quiz_results->business,
			'user_email' => $mlw_quiz_results->email,
			'user_phone' => $mlw_quiz_results->phone,
			'user_id' => $mlw_quiz_results->user,
			'timer' => $results[0],
			'time_taken' => $mlw_quiz_results->time_taken,
			'total_points' => $mlw_quiz_results->point_score,
			'total_score' => $mlw_quiz_results->correct_score,
			'total_correct' => $mlw_quiz_results->correct,
			'total_questions' => $mlw_quiz_results->total,
			'comments' => $results[2],
			'question_answers_array' => $results[1]
		);
		$template = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_certificate_options[1], $qmn_array_for_variables );
		$template = str_replace( "\n" , "<br>", $template );
		$plugindirpath=plugin_dir_path( __FILE__ );
		$mlw_qmn_certificate_file=<<<EOC
<?php
include("$plugindirpath/fpdf/WriteHTML.php");
\$pdf=new PDF_HTML();
\$pdf->AddPage('L');
EOC;
		$mlw_qmn_certificate_file.=$mlw_certificate_options[3] != '' ? '$pdf->Image("'.$mlw_certificate_options[3].'",0,0,$pdf->w, $pdf->h);' : '';
		$mlw_qmn_certificate_file.=<<<EOC
\$pdf->Ln(20);
\$pdf->SetFont('Arial','B',24);
\$pdf->MultiCell(280,20,'$mlw_certificate_options[0]',0,'C');
\$pdf->Ln(15);
\$pdf->SetFont('Arial','',16);
\$pdf->WriteHTML("<p align='center'>$template</p>");
EOC;
		$mlw_qmn_certificate_file.=$mlw_certificate_options[2] != '' ? '$pdf->Image("'.$mlw_certificate_options[2].'",110,130);' : '';
		$mlw_qmn_certificate_file.=<<<EOC
\$pdf->Output('mlw_qmn_certificate.pdf','D');
EOC;
		$mlw_qmn_certificate_filename = "../".str_replace(site_url()."/", '', plugin_dir_url( __FILE__ ))."certificates/mlw_qmn_quiz".date("YmdHis")."admin.php";
		file_put_contents($mlw_qmn_certificate_filename, $mlw_qmn_certificate_file);
		$mlw_qmn_certificate_filename = plugin_dir_url( __FILE__ )."certificates/mlw_qmn_quiz".date("YmdHis")."admin.php";
	}
	?>
	<form action="" method="post" name="create_certificate_form">
		<input type="hidden" name="create_certificate" value="confirmation" />
		<input type="submit" value="<?php _e('Create Certificate','quiz-master-next'); ?>" class="button"/>
	</form>
	<?php
	if ( isset( $_POST["create_certificate"] ) && "confirmation" == $_POST["create_certificate"] ) {
		echo "<a href='$mlw_qmn_certificate_filename' style='color: blue;'>" . __( 'Download Certificate Here','quiz-master-next' ) . "</a><br />";
	}
}


/**
* Registers the tab on the quiz details page
*
* @return void
* @since 4.4.0
*/
function qmn_results_certificate_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_results_settings_tab( __( "Certificate", 'quiz-master-next' ), "qmn_generate_results_certificate_tab" );
}
add_action( "plugins_loaded", 'qmn_results_certificate_tab' );
?>

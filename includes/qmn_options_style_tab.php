<?php
function mlw_options_styling_tab()
{
	echo "<li><a href=\"#tabs-7\">Styling</a></li>";
}

function mlw_options_styling_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	if (isset($_POST["save_style_options"]) && $_POST["save_style_options"] == "confirmation")
	{
		//Function Variables
		$mlw_qmn_style_id = intval($_POST["style_quiz_id"]);
		$mlw_qmn_theme = $_POST["save_quiz_theme"];
		$mlw_qmn_style = htmlspecialchars(stripslashes($_POST["quiz_css"]), ENT_QUOTES);
		
		//Save the new css
		$mlw_save_stle_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET quiz_stye='%s', theme_selected='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_style, $mlw_qmn_theme, $mlw_qmn_style_id ) );
		if ($mlw_save_stle_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The style has been saved successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Styles Have Been Saved For Quiz Number ".$mlw_qmn_style_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0015.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	?>
	<div id="tabs-7" class="mlw_tab_content">
		<script>
			function mlw_qmn_theme(theme)
			{
				document.getElementById('save_quiz_theme').value = theme;
				jQuery("div.mlw_qmn_themeBlockActive").toggleClass("mlw_qmn_themeBlockActive");
				jQuery("#mlw_qmn_theme_block_"+theme).toggleClass("mlw_qmn_themeBlockActive");
				
			}
		</script>
		<?php
			echo "<form action='' method='post' name='quiz_style_form'>";
			echo "<input type='hidden' name='save_style_options' value='confirmation' />";
			echo "<input type='hidden' name='style_quiz_id' value='".$quiz_id."' />";
			echo "<input type='hidden' name='save_quiz_theme' id='save_quiz_theme' value='".$mlw_quiz_options->theme_selected."' />";
		?>
		<h3>Quiz Styles</h3>
		<p>Choose your style:</p>
		<style>
			div.mlw_qmn_themeBlock
			{
				cursor: pointer;
				position: relative;
				height: 100px;
				width: 100px;
				background-color: #eee;
				color: blue;
				border: #ccc solid 1px;
				border-radius: 4px;
				padding: 5px 5px 5px 5px;
				display: inline-block;
				box-sizing: border-box;
				margin: auto;
			}
			div.mlw_qmn_themeBlockActive
			{
				background-color: yellow;
			}
		</style>
		<div onclick="mlw_qmn_theme('default');" id="mlw_qmn_theme_block_default" class="mlw_qmn_themeBlock <?php if ($mlw_quiz_options->theme_selected == 'default') {echo 'mlw_qmn_themeBlockActive';} ?>">Custom</div>
		<?php do_action('mlw_qmn_quiz_themes'); ?>
		<script>
			mlw_qmn_theme('<?php echo $mlw_quiz_options->theme_selected; ?>');			
		</script>
		<br /><br />
		<button id="save_styles_button" class="button" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>
		<hr />
		<h3>Custom Theme CSS</h3>
		<p>Entire quiz is a div with class 'mlw_qmn_quiz'</p>
		<p>Each page of the quiz is div with class 'quiz_section'</p>
		<p>Message before quiz text is a span with class 'mlw_qmn_message_before'</p>
		<p>The text for each question is wrapped in class 'mlw_qmn_question'</p>
		<p>Each comment field for the questions is wrapped in class 'mlw_qmn_question_comment'</p>
		<p>Label text for comment section is wrapped in class 'mlw_qmn_comment_section_text'</p>
		<p>The message displayed at end of quiz is a span with class 'mlw_qmn_message_end'</p>
		<p>Each button shown for pagination (i.e Next/Previous) is wrapped in class 'mlw_qmn_quiz_link'</p>
		<p>Timer is wrapped in class 'mlw_qmn_timer'</p>
		<p>Each horizontal multiple response is wrapped in a span with class 'mlw_horizontal_multiple'</p>
		<button id="save_styles_button" class="button" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>

		<table class="form-table">
			<tr>
				<td width="66%"><textarea style="width: 100%; height: 100%;" id="quiz_css" name="quiz_css"><?php echo $mlw_quiz_options->quiz_stye; ?></textarea>
				</td>	
				<td width="30%">
					<strong>Default:</strong><br />
					div.mlw_qmn_quiz input[type=radio],<br />
					div.mlw_qmn_quiz input[type=submit],<br />
					div.mlw_qmn_quiz label {<br />
						cursor: pointer;<br />
					}<br />
					div.mlw_qmn_quiz input:not([type=submit]):focus,<br />
					div.mlw_qmn_quiz textarea:focus {<br />
						background: #eaeaea;<br />
					}<br />
					div.mlw_qmn_quiz {<br />
						text-align: left;<br />
					}<br />
					div.quiz_section {<br />
						<br />
					}<br />
					div.mlw_qmn_timer {<br />
						position:fixed;<br />
						top:200px;<br />
						right:0px;<br />
						width:130px;<br />
						color:#00CCFF;<br />
						border-radius: 15px;<br />
						background:#000000;<br />
						text-align: center;<br />
						padding: 15px 15px 15px 15px<br />
					}<br />
					div.mlw_qmn_quiz input[type=submit],<br />
					a.mlw_qmn_quiz_link<br />
					{<br />
						    border-radius: 4px;<br />
						    position: relative;<br />
						    background-image: linear-gradient(#fff,#dedede);<br />
							background-color: #eee;<br />
							border: #ccc solid 1px;<br />
							color: #333;<br />
							text-shadow: 0 1px 0 rgba(255,255,255,.5);<br />
							box-sizing: border-box;<br />
						    display: inline-block;<br />
						    padding: 5px 5px 5px 5px;<br />
	   						margin: auto;<br />
					}<br />
				</td>
			</tr>
		</table>
		<button id="save_styles_button" class="button" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>
		</form>
	</div>
	<?php
}
add_action('mlw_qmn_options_tab', 'mlw_options_styling_tab');
add_action('mlw_qmn_options_tab_content', 'mlw_options_styling_tab_content');
?>

<?php
/*
This function is the very heart of the plugin. This function displays the quiz to the user as well as handles all the scripts that are part of the quiz.  Please be very careful if you are editing this script without my assistance.
*/
function mlw_quiz_shortcode($atts)
{
	extract(shortcode_atts(array(
		'quiz' => 0
	), $atts));
	
	

	/*
	Code before loading the quiz
	*/
	
	//Variables needed throughout script
	$mlw_quiz_id = intval($quiz);
	$GLOBALS['mlw_qmn_quiz'] = $mlw_quiz_id;
	$mlw_display = "";
	global $wpdb;
	$mlw_qmn_isAllowed = true;
	$mlw_qmn_section_count = 1;
	$mlw_qmn_section_limit = 0;


	//Load quiz
	$sql = "SELECT * FROM " . $wpdb->prefix . "mlw_quizzes" . " WHERE quiz_id=".$mlw_quiz_id." AND deleted='0'";
	$mlw_quiz_options = $wpdb->get_results($sql);

	foreach($mlw_quiz_options as $mlw_eaches) {
		$mlw_quiz_options = $mlw_eaches;
		break;
	}
	
	//Check if user is required to be checked in
	if ( $mlw_quiz_options->require_log_in == 1 && !is_user_logged_in() )
	{
		$mlw_message = htmlspecialchars_decode($mlw_quiz_options->require_log_in_text, ENT_QUOTES);
		$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
		$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
		$mlw_display = $mlw_message;
		$mlw_display .= wp_login_form( array('echo' => false) );
		return $mlw_display;
		$mlw_qmn_isAllowed = false;
	}	
	
	//Check to see if there is limit on the amount of tries
	if ( $mlw_quiz_options->total_user_tries != 0 && is_user_logged_in() )
	{
		$current_user = wp_get_current_user();
		$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_results WHERE email='%s' AND deleted='0' AND quiz_id=%d", $current_user->user_email, $mlw_quiz_id ) );
		if ($mlw_qmn_user_try_count >= $mlw_quiz_options->total_user_tries) { $mlw_qmn_isAllowed = false; }
	}


	//Load questions
	$sql = "SELECT * FROM " . $wpdb->prefix . "mlw_questions" . " WHERE quiz_id=".$mlw_quiz_id." AND deleted='0' "; 
	if ($mlw_quiz_options->randomness_order == 0)
	{
		$sql .= "ORDER BY question_order ASC";
	}
	if ($mlw_quiz_options->randomness_order == 1 || $mlw_quiz_options->randomness_order == 2)
	{
		$sql .= "ORDER BY rand()";
	}
	if ($mlw_quiz_options->question_from_total != 0)
	{
		$sql .= " LIMIT ".$mlw_quiz_options->question_from_total;
	}
	$mlw_questions = $wpdb->get_results($sql);
	
	
	//Load and prepare answer arrays
	$mlw_qmn_answer_arrays = array();
	foreach($mlw_questions as $mlw_question_info) {
		$mlw_qmn_answer_array_each = @unserialize($mlw_question_info->answer_array);
		if ( !is_array($mlw_qmn_answer_array_each) )
		{
			$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
			$mlw_answer_array_correct[$mlw_question_info->correct_answer-1] = 1;
			$mlw_qmn_answer_arrays[$mlw_question_info->question_id] = array(
				array($mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0]),
				array($mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1]),
				array($mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2]),
				array($mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3]),
				array($mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4]),
				array($mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5]));
		}
		else
		{
			$mlw_qmn_answer_arrays[$mlw_question_info->question_id] = $mlw_qmn_answer_array_each;
		}
	}


	//Variables to load if quiz has been taken
	if (isset($_POST["complete_quiz"]) && $_POST["complete_quiz"] == "confirmation")
	{
		$mlw_success = $_POST["complete_quiz"];
		$mlw_user_name = isset($_POST["mlwUserName"]) ? $_POST["mlwUserName"] : 'None';
		$mlw_user_comp = isset($_POST["mlwUserComp"]) ? $_POST["mlwUserComp"] : 'None';
		$mlw_user_email = isset($_POST["mlwUserEmail"]) ? $_POST["mlwUserEmail"] : 'None';
		$mlw_user_phone = isset($_POST["mlwUserPhone"]) ? $_POST["mlwUserPhone"] : 'None';
		$mlw_qmn_timer = isset($_POST["timer"]) ? $_POST["timer"] : 0;
		$mlw_spam_email = $_POST["email"];
	}
	
	wp_enqueue_script( 'json2' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-effects-core' );
	wp_enqueue_script( 'jquery-effects-slide' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-ui-tooltip' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	?>
	<script type="text/javascript"
	  src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
	</script>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
	<script type="text/javascript">
		setTimeout(function(){
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;
		$j(function() {
   			 $j( ".mlw_qmn_quiz" ).tooltip();
 		});
		}, 100);
		setTimeout(function()
		{
			var $j = jQuery.noConflict();
			$j('.mlw_qmn_quiz input').on('keypress', function (e) {
				if (e.which === 13) {
					e.preventDefault();
				}
			});
		}, 100);
	</script>
 	<style type="text/css">
 		.ui-tooltip
		{
		    /* tooltip container box */
		    max-width: 500px !important;
		}
		.ui-tooltip-content
		{
		    /* tooltip content */
		    max-width: 500px !important;
		}
 	</style>

			<?php 
			if ($mlw_quiz_options->theme_selected == "default")
			{
				echo "<style type='text/css'>".$mlw_quiz_options->quiz_stye."</style>";
			}
			else
			{
				echo "<link type='text/css' href='".get_option('mlw_qmn_theme_'.$mlw_quiz_options->theme_selected)."' rel='stylesheet' />";
			}
			
	/*
	The following code is for displaying the quiz and completion screen
	*/
	
	//If there is no quiz for the shortcode provided
	if ($mlw_quiz_options->quiz_name == "")
	{
		$mlw_display .= "It appears that this quiz is not set up correctly.";
		return $mlw_display;
	}



	//Display Quiz
	if (!isset($_POST["complete_quiz"]) && $mlw_quiz_options->quiz_name != "" && $mlw_qmn_isAllowed)
	{
		//Check if total entries are limited
		if ( $mlw_quiz_options->limit_total_entries != 0 )
		{
			$mlw_qmn_entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(quiz_id) FROM ".$wpdb->prefix."mlw_results WHERE deleted='0' AND quiz_id=%d", $mlw_quiz_id ) );
			if ($mlw_qmn_entries_count >= $mlw_quiz_options->limit_total_entries)
			{
				$mlw_message = htmlspecialchars_decode($mlw_quiz_options->limit_total_entries_text, ENT_QUOTES);
				$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
				$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
				$mlw_display = $mlw_message;
				return $mlw_display;
				$mlw_qmn_isAllowed = false;
			}
		}
		$mlw_qmn_total_questions = 0;
		//Calculate number of pages if pagination is turned on
		if ($mlw_quiz_options->pagination != 0)
		{
			$mlw_qmn_section_limit = 2 + count($mlw_questions);
			if ($mlw_quiz_options->comment_section == 0)
			{
				$mlw_qmn_section_limit = $mlw_qmn_section_limit + 1;
			}
			
			//Gather text for pagination buttons
			$mlw_qmn_pagination_text = "";
			$mlw_qmn_pagination_text = @unserialize($mlw_quiz_options->pagination_text);
			if (!is_array($mlw_qmn_pagination_text)) {
		        $mlw_qmn_pagination_text = array('Previous', $mlw_quiz_options->pagination_text);
		    }
			?>
			<script type="text/javascript">
				setTimeout(function(){
				var $j = jQuery.noConflict();
				$j( ".quiz_section" ).hide();
				$j( ".quiz_section" ).append( "<br />" );
				$j( ".mlw_qmn_quiz" ).append( "<a class=\"mlw_qmn_quiz_link mlw_previous\" href=\"javascript:prevSlide(<?php echo $mlw_quiz_options->pagination; ?>, 1);\"><?php echo $mlw_qmn_pagination_text[0]; ?></a>" );
				$j( ".mlw_qmn_quiz" ).append( "<a class=\"mlw_qmn_quiz_link mlw_next\" href=\"javascript:nextSlide(<?php echo $mlw_quiz_options->pagination; ?>, 1);\"><?php echo $mlw_qmn_pagination_text[1]; ?></a>" );
				window.mlw_quiz_slide = 0;
				window.mlw_previous = 0;
				window.mlw_quiz_total_slides = <?php echo $mlw_qmn_section_limit; ?>;
				nextSlide(1, 0);
				}, 100);
				function nextSlide(mlw_pagination, mlw_goto_top)
				{
					jQuery( ".quiz_section" ).hide();
					for (var i = 0; i < mlw_pagination; i++) 
					{
						if (i == 0 && window.mlw_previous == 1 && window.mlw_quiz_slide > 1)
						{
							window.mlw_quiz_slide = window.mlw_quiz_slide + mlw_pagination;
						}
						else
						{
							window.mlw_quiz_slide++;
						}
						if (window.mlw_quiz_slide < 1)
						{
							window.mlw_quiz_slide = 1;
						}
						if (window.mlw_quiz_slide == 1)
					    {
					    	jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).hide();
					    }
					    if (window.mlw_quiz_slide > 1)
					    {
					    	jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).show();
					    }
					    if (window.mlw_quiz_slide == window.mlw_quiz_total_slides)
					    {
					        jQuery( ".mlw_qmn_quiz_link.mlw_next" ).hide();
					    } 
					    if (window.mlw_quiz_slide < window.mlw_quiz_total_slides)
					    {
					        jQuery( ".mlw_qmn_quiz_link.mlw_next" ).show();
					    } 
					    jQuery( ".quiz_section.slide"+window.mlw_quiz_slide ).show();
					}
					window.mlw_previous = 0;
					if (mlw_goto_top == 1)
					{
						window.location.hash = "mlw_does_not_exist";
						window.location.hash = "mlw_top_of_quiz";
					}
				    
				}
				function prevSlide(mlw_pagination, mlw_goto_top)
				{
					jQuery( ".quiz_section" ).hide();
					for (var i = 0; i < mlw_pagination; i++) 
					{ 
						if (i == 0 && window.mlw_previous == 0)
						{
							window.mlw_quiz_slide = window.mlw_quiz_slide - mlw_pagination;
						}
						else
						{
							window.mlw_quiz_slide--;
						}
						if (window.mlw_quiz_slide < 1)
						{
							window.mlw_quiz_slide = 1;
						}
						if (window.mlw_quiz_slide == 1)
					    {
					    	jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).hide();
					    }
					    if (window.mlw_quiz_slide > 1)
					    {
					    	jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).show();
					    }
					    if (window.mlw_quiz_slide == window.mlw_quiz_total_slides)
					    {
					        jQuery( ".mlw_qmn_quiz_link.mlw_next" ).hide();
					    } 
					    if (window.mlw_quiz_slide < window.mlw_quiz_total_slides)
					    {
					        jQuery( ".mlw_qmn_quiz_link.mlw_next" ).show();
					    }
					    jQuery( ".quiz_section.slide"+window.mlw_quiz_slide ).show();	
					}
					window.mlw_previous = 1;
					if (mlw_goto_top == 1)
					{
						window.location.hash = "mlw_does_not_exist";
						window.location.hash = "mlw_top_of_quiz";
					}
				}
			</script>
			<?php
		}
		if ($mlw_quiz_options->timer_limit != 0)
		{
			?>
			<div id="mlw_qmn_timer" class="mlw_qmn_timer"></div>
			<script type="text/javascript">
				setTimeout(function(){
				var minutes = 0;
				if (window.sessionStorage.getItem('mlw_started_quiz<?php echo $mlw_quiz_id; ?>') == "yes" && window.sessionStorage.getItem('mlw_time_quiz<?php echo $mlw_quiz_id; ?>') >= 0)
				{
					minutes = window.sessionStorage.getItem('mlw_time_quiz<?php echo $mlw_quiz_id; ?>');
				}
				else
				{
					minutes = <?php echo $mlw_quiz_options->timer_limit; ?>;
				}
				window.amount = (minutes*60);
				window.titleText = window.document.title;
				document.getElementById("mlw_qmn_timer").innerHTML = minToSec(window.amount);
				window.counter=setInterval(timer, 1000);
				}, 100);
				function timer()
				{
					window.amount=window.amount-1;
					if (window.amount < 0)
					{
						window.amount = 0;	
					}
					window.sessionStorage.setItem('mlw_time_quiz<?php echo $mlw_quiz_id; ?>', window.amount/60);
					window.sessionStorage.setItem('mlw_started_quiz<?php echo $mlw_quiz_id; ?>', "yes");
				    document.getElementById("mlw_qmn_timer").innerHTML = minToSec(window.amount);
				    window.document.title = minToSec(window.amount) + " " + window.titleText;
				  	if (window.amount <= 0)
				  	{
				    	clearInterval(window.counter);
				    	jQuery( ".mlw_qmn_quiz input:radio" ).attr('disabled',true);
				    	jQuery( ".mlw_qmn_quiz input:checkbox" ).attr('disabled',true);
				    	jQuery( ".mlw_qmn_quiz select" ).attr('disabled',true);
				    	jQuery( ".mlw_qmn_question_comment" ).attr('disabled',true);
				    	jQuery( ".mlw_answer_open_text" ).attr('disabled',true);
				    	//document.quizForm.submit();
				     	return;
				  	}
				}
				function minToSec(amount)
				{
					var timer_display = '';
					var hours = Math.floor(amount/3600);
					if (hours == '0')
					{
						timer_display = timer_display +"00:";
					}
					else if (hours < 10)
					{
						timer_display = timer_display + '0' + hours + ":";
					}
					else
					{
						timer_display = timer_display + hours + ":";
					}
					var minutes = Math.floor((amount % 3600)/60);
					if (minutes == '0')
					{
						timer_display = timer_display +"00:";
					}
					else if (minutes < 10)
					{
						timer_display = timer_display + '0' + minutes + ":";
					}
					else
					{
						timer_display = timer_display + minutes + ":";
					}
					var seconds = Math.floor(amount % 60);
					if (seconds == '0') 
					{ 
						timer_display = timer_display +"00";
					}
					else if (seconds < 10)
					{
						timer_display = timer_display +'0' + seconds;
					}
					else
					{
						timer_display = timer_display + seconds;
					}
					return timer_display;
				}
			</script>
			<?php
		}
		
		?>
		<script type="text/javascript">
			var myVar=setInterval("mlwQmnTimer();",1000);
	 		function mlwQmnTimer()
	 		{
	 			var x = +document.getElementById("timer").value;
	 			x = x + 1;
	 			document.getElementById("timer").value = x;
	 		}
	 		
		</script>
		<?php
		//Update the quiz views
		$mlw_views = $mlw_quiz_options->quiz_views;
		$mlw_views += 1;
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET quiz_views='".$mlw_views."' WHERE quiz_id=".$mlw_quiz_id;
		$results = $wpdb->query( $update );
		
		//Form validation script
		?>
		<script>
			function clear_field(field)
			{
				if (field.defaultValue == field.value) field.value = '';
			}
			
			function mlw_validateForm()
			{
				mlw_validateResult = true;
				if (document.forms['quizForm']['mlwUserEmail'].value != '')
				{
					var x=document.forms['quizForm']['mlwUserEmail'].value;
					var atpos=x.indexOf('@');
					var dotpos=x.lastIndexOf('.');
					if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
					  {
					  	document.getElementById('mlw_error_message').innerHTML = '**Not a valid e-mail address!**';
					  	document.getElementById('mlw_error_message_bottom').innerHTML = '**Not a valid e-mail address!**';
					  mlw_validateResult =  false;
					  }
				}
				
				jQuery('#quizForm *').filter(':input').each(function(){
					jQuery(this).css("outline", "");
					if (jQuery(this).attr('class'))
					{
						if(jQuery(this).attr('class').indexOf('mlwRequiredNumber') > -1 && this.value == "" && +this.value != NaN)
						{
							document.getElementById('mlw_error_message').innerHTML = '**This field must be a number!**';
							document.getElementById('mlw_error_message_bottom').innerHTML = '**This field must be a number!**';
							jQuery(this).css("outline", "2px solid red");
							mlw_validateResult =  false;
						}
						if(jQuery(this).attr('class').indexOf('mlwRequiredText') > -1 && this.value == "")
						{
							document.getElementById('mlw_error_message').innerHTML = '**Please complete all required fields!**';
							document.getElementById('mlw_error_message_bottom').innerHTML = '**Please complete all required fields!**';
							jQuery(this).css("outline", "2px solid red");
							mlw_validateResult =  false;
						}
						if(jQuery(this).attr('class').indexOf('mlwRequiredCaptcha') > -1 && this.value != mlw_code)
						{
							document.getElementById('mlw_error_message').innerHTML = '**The entered text is not correct!**';
							document.getElementById('mlw_error_message_bottom').innerHTML = '**The entered text is not correct!**';
							jQuery(this).css("outline", "2px solid red");
							mlw_validateResult =  false;
						}
						if(jQuery(this).attr('class').indexOf('mlwRequiredCheck') > -1 && !this.checked)
						{
							document.getElementById('mlw_error_message').innerHTML = '**Please complete all required fields!**';
							document.getElementById('mlw_error_message_bottom').innerHTML = '**Please complete all required fields!**';
							jQuery(this).css("outline", "2px solid red");
							mlw_validateResult =  false;
						}
					}
				});
				
				if (!mlw_validateResult) {return mlw_validateResult;}
				
				jQuery( '.mlw_qmn_quiz input:radio' ).attr('disabled',false);
				jQuery( '.mlw_qmn_quiz input:checkbox' ).attr('disabled',false);
				jQuery( '.mlw_qmn_quiz select' ).attr('disabled',false);
				jQuery( '.mlw_qmn_question_comment' ).attr('disabled',false);
				jQuery( '.mlw_answer_open_text' ).attr('disabled',false);
			}		
		</script>
		<?php
		
		if ( $mlw_quiz_options->pagination != 0) { $mlw_display .= "<style>.quiz_section { display: none; }</style>"; }
		
		//Begin the quiz
		$mlw_display .= "<div class='mlw_qmn_quiz'>";
		$mlw_display .= "<form name='quizForm' id='quizForm' action='' method='post' class='mlw_quiz_form' onsubmit='return mlw_validateForm()' novalidate >";
		$mlw_display .= "<span id='mlw_top_of_quiz'></span>";
		$mlw_display .= "<div class='quiz_section  quiz_begin slide".$mlw_qmn_section_count."'>";
		$mlw_message_before = htmlspecialchars_decode($mlw_quiz_options->message_before, ENT_QUOTES);
		$mlw_message_before = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_before);
		$mlw_message_before = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_before);
		$mlw_display .= "<span class='mlw_qmn_message_before'>".$mlw_message_before."</span><br />";
		$mlw_display .= "<span name='mlw_error_message' id='mlw_error_message' style='color: red;'></span><br />";

		if ($mlw_quiz_options->contact_info_location == 0)
		{
			$mlw_display .= mlwDisplayContactInfo($mlw_quiz_options);
		}
		$mlw_display .= "</div>";
		
		//Display the questions
		foreach($mlw_questions as $mlw_question) {
			$mlw_question_settings = @unserialize($mlw_question->question_settings);
			if (!is_array($mlw_question_settings)) 
			{
				$mlw_question_settings = array();
				$mlw_question_settings['required'] = 1;
			}
			if ( !isset($mlw_question_settings['required']))
			{
				$mlw_question_settings['required'] = 1;
			}
			$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
			$mlw_display .= "<div class='quiz_section slide".$mlw_qmn_section_count."'>";
			if ($mlw_question->question_type == 0)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				$mlw_qmn_answer_array = $mlw_qmn_answer_arrays[$mlw_question->question_id];
				if (is_array($mlw_qmn_answer_array))
				{
					if ($mlw_quiz_options->randomness_order == 2)
					{
						shuffle($mlw_qmn_answer_array);
					}
					$mlw_answer_total = 0;
					foreach($mlw_qmn_answer_array as $mlw_qmn_answer_each)
					{
						$mlw_answer_total++;
						if ($mlw_qmn_answer_each[0] != "")
						{
							$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_".$mlw_answer_total."' value='".esc_attr($mlw_qmn_answer_each[0])."' /> <label for='question".$mlw_question->question_id."_".$mlw_answer_total."'>".htmlspecialchars_decode($mlw_qmn_answer_each[0], ENT_QUOTES)."</label>";
							$mlw_display .= "<br />";
						}
					}
					$mlw_display .= "<input type='radio' style='display: none;' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_none' checked='checked' value='No Answer Provided' />";
				}
				else
				{
					if ($mlw_question->answer_one != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_one' value='1' /> <label for='question".$mlw_question->question_id."_one'>".htmlspecialchars_decode($mlw_question->answer_one, ENT_QUOTES)."</label>";
						$mlw_display .= "<br />";
					}
					if ($mlw_question->answer_two != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_two' value='2' /> <label for='question".$mlw_question->question_id."_two'>".htmlspecialchars_decode($mlw_question->answer_two, ENT_QUOTES)."</label>";
						$mlw_display .= "<br />";
					}
					if ($mlw_question->answer_three != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_three' value='3' /> <label for='question".$mlw_question->question_id."_three'>".htmlspecialchars_decode($mlw_question->answer_three, ENT_QUOTES)."</label>";
						$mlw_display .= "<br />";
					}
					if ($mlw_question->answer_four != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_four' value='4' /> <label for='question".$mlw_question->question_id."_four'>".htmlspecialchars_decode($mlw_question->answer_four, ENT_QUOTES)."</label>";
						$mlw_display .= "<br />";
					}
					if ($mlw_question->answer_five != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_five' value='5' /> <label for='question".$mlw_question->question_id."_five'>".htmlspecialchars_decode($mlw_question->answer_five, ENT_QUOTES)."</label>";
						$mlw_display .= "<br />";
					}
					if ($mlw_question->answer_six != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_six' value='6' /> <label for='question".$mlw_question->question_id."_six'>".htmlspecialchars_decode($mlw_question->answer_six, ENT_QUOTES)."</label>";
						$mlw_display .= "<br />";
					}
				}
			}
			elseif ($mlw_question->question_type == 4)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				$mlw_qmn_answer_array = $mlw_qmn_answer_arrays[$mlw_question->question_id];
				if (is_array($mlw_qmn_answer_array))
				{
					if ($mlw_quiz_options->randomness_order == 2)
					{
						shuffle($mlw_qmn_answer_array);
					}
					$mlw_answer_total = 0;
					foreach($mlw_qmn_answer_array as $mlw_qmn_answer_each)
					{
						$mlw_answer_total++;
						if ($mlw_qmn_answer_each[0] != "")
						{
							$mlw_display .= "<input type='hidden' name='question".$mlw_question->question_id."' value='This value does not matter' />";
							$mlw_display .= "<input type='checkbox' name='question".$mlw_question->question_id."_".$mlw_answer_total."' id='question".$mlw_question->question_id."_".$mlw_answer_total."' value='".esc_attr($mlw_qmn_answer_each[0])."' /> <label for='question".$mlw_question->question_id."_".$mlw_answer_total."'>".htmlspecialchars_decode($mlw_qmn_answer_each[0], ENT_QUOTES)."</label>";
							$mlw_display .= "<br />";
						}
					}
				}
			}
			elseif ($mlw_question->question_type == 10)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				$mlw_qmn_answer_array = $mlw_qmn_answer_arrays[$mlw_question->question_id];
				if (is_array($mlw_qmn_answer_array))
				{
					if ($mlw_quiz_options->randomness_order == 2)
					{
						shuffle($mlw_qmn_answer_array);
					}
					$mlw_answer_total = 0;
					foreach($mlw_qmn_answer_array as $mlw_qmn_answer_each)
					{
						$mlw_answer_total++;
						if ($mlw_qmn_answer_each[0] != "")
						{
							$mlw_display .= "<input type='hidden' name='question".$mlw_question->question_id."' value='This value does not matter' />";
							$mlw_display .= "<span class='mlw_horizontal_multiple'><input type='checkbox' name='question".$mlw_question->question_id."_".$mlw_answer_total."' id='question".$mlw_question->question_id."_".$mlw_answer_total."' value='".esc_attr($mlw_qmn_answer_each[0])."' /> <label for='question".$mlw_question->question_id."_".$mlw_answer_total."'>".htmlspecialchars_decode($mlw_qmn_answer_each[0], ENT_QUOTES)."&nbsp;</label></span>";
						}
					}
				}
			}
			elseif ($mlw_question->question_type == 1)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				$mlw_qmn_answer_array = $mlw_qmn_answer_arrays[$mlw_question->question_id];
				if (is_array($mlw_qmn_answer_array))
				{
					if ($mlw_quiz_options->randomness_order == 2)
					{
						shuffle($mlw_qmn_answer_array);
					}
					$mlw_answer_total = 0;
					foreach($mlw_qmn_answer_array as $mlw_qmn_answer_each)
					{
						$mlw_answer_total++;
						if ($mlw_qmn_answer_each[0] != "")
						{
							$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='".esc_attr($mlw_qmn_answer_each[0])."' /> ".htmlspecialchars_decode($mlw_qmn_answer_each[0], ENT_QUOTES)." ";
						}
					}
					$mlw_display .= "<input type='radio' style='display: none;' name='question".$mlw_question->question_id."' id='question".$mlw_question->question_id."_none' checked='checked' value='No Answer Provided' />";
				}
				else
				{
					if ($mlw_question->answer_one != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='1' />".htmlspecialchars_decode($mlw_question->answer_one, ENT_QUOTES);
					}
					if ($mlw_question->answer_two != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='2' />".htmlspecialchars_decode($mlw_question->answer_two, ENT_QUOTES);
					}
					if ($mlw_question->answer_three != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='3' />".htmlspecialchars_decode($mlw_question->answer_three, ENT_QUOTES);
					}
					if ($mlw_question->answer_four != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='4' />".htmlspecialchars_decode($mlw_question->answer_four, ENT_QUOTES);
					}
					if ($mlw_question->answer_five != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='5' />".htmlspecialchars_decode($mlw_question->answer_five, ENT_QUOTES);
					}
					if ($mlw_question->answer_six != "")
					{
						$mlw_display .= "<input type='radio' name='question".$mlw_question->question_id."' value='6' />".htmlspecialchars_decode($mlw_question->answer_six, ENT_QUOTES);
					}
				}
				$mlw_display .= "<br />";
			}
			elseif ($mlw_question->question_type == 2)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				$mlw_display .= "<select name='question".$mlw_question->question_id."'>";
				$mlw_qmn_answer_array = $mlw_qmn_answer_arrays[$mlw_question->question_id];
				if (is_array($mlw_qmn_answer_array))
				{
					if ($mlw_quiz_options->randomness_order == 2)
					{
						shuffle($mlw_qmn_answer_array);
					}
					$mlw_answer_total = 0;
					foreach($mlw_qmn_answer_array as $mlw_qmn_answer_each)
					{
						$mlw_answer_total++;
						if ($mlw_qmn_answer_each[0] != "")
						{
							$mlw_display .= "<option value='".esc_attr($mlw_qmn_answer_each[0])."'>".htmlspecialchars_decode($mlw_qmn_answer_each[0], ENT_QUOTES)."</option>";
						}
					}
				}
				else
				{
					if ($mlw_question->answer_one != "")
					{
						$mlw_display .= "<option value='1'>".htmlspecialchars_decode($mlw_question->answer_one, ENT_QUOTES)."</option>";
					}
					if ($mlw_question->answer_two != "")
					{
						$mlw_display .= "<option value='2'>".htmlspecialchars_decode($mlw_question->answer_two, ENT_QUOTES)."</option>";
					}
					if ($mlw_question->answer_three != "")
					{
						$mlw_display .= "<option value='3'>".htmlspecialchars_decode($mlw_question->answer_three, ENT_QUOTES)."</option>";
					}
					if ($mlw_question->answer_four != "")
					{
						$mlw_display .= "<option value='4'>".htmlspecialchars_decode($mlw_question->answer_four, ENT_QUOTES)."</option>";
					}
					if ($mlw_question->answer_five != "")
					{
						$mlw_display .= "<option value='5'>".htmlspecialchars_decode($mlw_question->answer_five, ENT_QUOTES)."</option>";
					}
					if ($mlw_question->answer_six != "")
					{
						$mlw_display .= "<option value='6'>".htmlspecialchars_decode($mlw_question->answer_six, ENT_QUOTES)."</option>";
					}
				}
				$mlw_display .= "</select>";
				$mlw_display .= "<br />";
			}
			elseif ($mlw_question->question_type == 5)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				if ($mlw_question_settings['required'] == 0) {$mlw_requireClass = "mlwRequiredText";} else {$mlw_requireClass = "";}
				$mlw_display .= "<textarea class='mlw_answer_open_text $mlw_requireClass' cols='70' rows='5' name='question".$mlw_question->question_id."' /></textarea>";
				$mlw_display .= "<br />";
			}
			elseif ($mlw_question->question_type == 6)
			{
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES);
				$mlw_display .= "<br />";
			}
			elseif ($mlw_question->question_type == 7)
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				if ($mlw_question_settings['required'] == 0) {$mlw_requireClass = "mlwRequiredNumber";} else {$mlw_requireClass = "";}
				$mlw_display .= "<input type='number' class='mlw_answer_number $mlw_requireClass' name='question".$mlw_question->question_id."' />";
				$mlw_display .= "<br />";
			}
			elseif ($mlw_question->question_type == 8)
			{
				if ($mlw_question_settings['required'] == 0) {$mlw_requireClass = "mlwRequiredCheck";} else {$mlw_requireClass = "";}
				$mlw_display .= "<input type='checkbox' id='mlwAcceptance' class='$mlw_requireClass ' />";
				$mlw_display .= "<label for='mlwAcceptance'><span class='mlw_qmn_question' style='font-weight:bold;'>".htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span></label>";
				$mlw_display .= "<br />";
			}
			elseif ($mlw_question->question_type == 9)
			{
				if ($mlw_question_settings['required'] == 0) {$mlw_requireClass = "mlwRequiredCaptcha";} else {$mlw_requireClass = "";}
				$mlw_display .= "<div class='mlw_captchaWrap'>";
				$mlw_display .= "<canvas alt='' id='mlw_captcha' class='mlw_captcha' width='100' height='50'></canvas>";
				$mlw_display .= "</div>";
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
		        $mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
		        $mlw_display .= "<input type='text' class='mlw_answer_open_text $mlw_requireClass' id='mlw_captcha_text' name='mlw_user_captcha'/>";
		        $mlw_display .= "<input type='hidden' name='mlw_code_captcha' id='mlw_code_captcha' value='none' />";
				$mlw_display .= "<br />";
				$mlw_display .= "<script>
				var mlw_code = '';
				var mlw_chars = '0123456789ABCDEFGHIJKL!@#$%^&*()MNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
				var mlw_code_length = 5;
				for (var i=0; i<mlw_code_length; i++) {
		            var rnum = Math.floor(Math.random() * mlw_chars.length);
		            mlw_code += mlw_chars.substring(rnum,rnum+1);
		        }
		        var mlw_captchaCTX = document.getElementById('mlw_captcha').getContext('2d');
		        mlw_captchaCTX.font = 'normal 24px Verdana';
		        mlw_captchaCTX.strokeStyle = '#000000';
		        mlw_captchaCTX.clearRect(0,0,100,50);
		        mlw_captchaCTX.strokeText(mlw_code,10,30,70);
		        mlw_captchaCTX.textBaseline = 'middle';
		        document.getElementById('mlw_code_captcha').value = mlw_code;
		        </script>
		        ";
			}
			else
			{
				$mlw_display .= "<span class='mlw_qmn_question' style='font-weight:bold;'>";
				$mlw_qmn_total_questions = $mlw_qmn_total_questions + 1;
				if ($mlw_quiz_options->question_numbering == 1) { $mlw_display .= $mlw_qmn_total_questions.") "; }
				$mlw_display .= htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES)."</span><br />";
				if ($mlw_question_settings['required'] == 0) {$mlw_requireClass = "mlwRequiredText";} else {$mlw_requireClass = "";}
				$mlw_display .= "<input type='text' class='mlw_answer_open_text $mlw_requireClass' name='question".$mlw_question->question_id."' />";
				$mlw_display .= "<br />";				
			}
			if ($mlw_question->comments == 0)
			{
				$mlw_display .= "<input type='text' class='mlw_qmn_question_comment' x-webkit-speech id='mlwComment".$mlw_question->question_id."' name='mlwComment".$mlw_question->question_id."' value='".esc_attr(htmlspecialchars_decode($mlw_quiz_options->comment_field_text, ENT_QUOTES))."' onclick='clear_field(this)'/>";
				$mlw_display .= "<br />";
			}
			if ($mlw_question->comments == 2)
			{
				$mlw_display .= "<textarea cols='70' rows='5' class='mlw_qmn_question_comment' id='mlwComment".$mlw_question->question_id."' name='mlwComment".$mlw_question->question_id."' onclick='clear_field(this)'>".htmlspecialchars_decode($mlw_quiz_options->comment_field_text, ENT_QUOTES)."</textarea>";
				$mlw_display .= "<br />";
			}
			if ($mlw_question->hints != "")
			{
				$mlw_display .= "<span title=\"".htmlspecialchars_decode($mlw_question->hints, ENT_QUOTES)."\" style=\"text-decoration:underline;color:rgb(0,0,255);\" class='mlw_qmn_hint_link'>Hint</span>";
				$mlw_display .= "<br /><br />";
			}
			$mlw_display .= "</div>";
			if ( $mlw_quiz_options->pagination == 0) { $mlw_display .= "<br />"; }
		}
		
		//Display comment box if needed
		if ($mlw_quiz_options->comment_section == 0)
		{
			$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
			$mlw_display .= "<div class='quiz_section slide".$mlw_qmn_section_count."'>";
			$mlw_message_comments = htmlspecialchars_decode($mlw_quiz_options->message_comment, ENT_QUOTES);
			$mlw_message_comments = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_comments);
			$mlw_message_comments = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_comments);
			$mlw_display .= "<label for='mlwQuizComments' class='mlw_qmn_comment_section_text' style='font-weight:bold;'>".$mlw_message_comments."</label><br />";
			$mlw_display .= "<textarea cols='70' rows='15' id='mlwQuizComments' name='mlwQuizComments' ></textarea>";
			$mlw_display .= "</div>";
			if ( $mlw_quiz_options->pagination == 0) { $mlw_display .= "<br /><br />"; }
		}
		$mlw_display .= "<br />";
		$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
		$mlw_display .= "<div class='quiz_section slide".$mlw_qmn_section_count." quiz_end'>";
		if ($mlw_quiz_options->message_end_template != '')
		{
			$mlw_message_end = htmlspecialchars_decode($mlw_quiz_options->message_end_template, ENT_QUOTES);
			$mlw_message_end = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_end);
			$mlw_message_end = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_end);
			$mlw_display .= "<span class='mlw_qmn_message_end'>".$mlw_message_end."</span>";
			$mlw_display .= "<br /><br />";
		}
		if ($mlw_quiz_options->contact_info_location == 1)
		{
			$mlw_display .= mlwDisplayContactInfo($mlw_quiz_options);
		}
		ob_start();
	        do_action('mlw_qmn_end_quiz_section');
	        $mlw_display .= ob_get_contents();
	    ob_end_clean();
		$mlw_display .= "<span style='display: none;'>If you are human, leave this field blank or you will be considered spam:</span>";
		$mlw_display .= "<input style='display: none;' type='text' name='email' id='email' />";
		$mlw_display .= "<input type='hidden' name='total_questions' id='total_questions' value='".$mlw_qmn_total_questions."'/>";
		$mlw_display .= "<input type='hidden' name='timer' id='timer' value='0'/>";
		$mlw_display .= "<input type='hidden' name='complete_quiz' value='confirmation' />";
		$mlw_display .= "<input type='submit' value='".esc_attr(htmlspecialchars_decode($mlw_quiz_options->submit_button_text, ENT_QUOTES))."' />";
		$mlw_display .= "<span name='mlw_error_message_bottom' id='mlw_error_message_bottom' style='color: red;'></span><br />";
		$mlw_display .= "</form>";
		$mlw_display .= "</div>";
		$mlw_display .= "</div>";
		
	}
	//Display Completion Screen
	else
	{
		?>
		<script type="text/javascript">
			window.sessionStorage.setItem('mlw_time_quiz<?php echo $mlw_quiz_id; ?>', 'completed');
			window.sessionStorage.setItem('mlw_started_quiz<?php echo $mlw_quiz_id; ?>', "no");
		</script>
		<?php
		if (empty($mlw_spam_email) && $mlw_qmn_isAllowed && ((!isset($_POST["mlw_code_captcha"])) || isset($_POST["mlw_code_captcha"]) && $_POST["mlw_user_captcha"] == $_POST["mlw_code_captcha"]))
		{
		
		//Load questions
		$sql = "SELECT * FROM " . $wpdb->prefix . "mlw_questions" . " WHERE quiz_id=".$mlw_quiz_id." AND deleted='0' "; 
		if ($mlw_quiz_options->randomness_order == 0)
		{
			$sql .= "ORDER BY question_order ASC";
		}
		if ($mlw_quiz_options->randomness_order == 1 || $mlw_quiz_options->randomness_order == 2)
		{
			$sql .= "ORDER BY rand()";
		}
		$mlw_questions = $wpdb->get_results($sql);
		
		//Load and prepare answer arrays
		$mlw_qmn_loaded_answer_arrays = array();
		foreach($mlw_questions as $mlw_question_info) {
			$mlw_qmn_answer_array_each = @unserialize($mlw_question_info->answer_array);
			if ( !is_array($mlw_qmn_answer_array_each) )
			{
				$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
				$mlw_answer_array_correct[$mlw_question_info->correct_answer-1] = 1;
				$mlw_qmn_loaded_answer_arrays[$mlw_question_info->question_id] = array(
					array($mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0]),
					array($mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1]),
					array($mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2]),
					array($mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3]),
					array($mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4]),
					array($mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5]));
			}
			else
			{
				$mlw_qmn_loaded_answer_arrays[$mlw_question_info->question_id] = $mlw_qmn_answer_array_each;
			}
		}
	
		//Variables needed for scoring
		$mlw_points = 0;
		$mlw_correct = 0;
		$mlw_total_questions = 0;
		$mlw_total_score = 0;
		$mlw_question_answers = "";
		isset($_POST["total_questions"]) ? $mlw_total_questions = intval($_POST["total_questions"]) : $mlw_total_questions = 0;

		//Update the amount of times the quiz has been taken
		$mlw_taken = $mlw_quiz_options->quiz_taken;
		$mlw_taken += 1;
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET quiz_taken='".$mlw_taken."' WHERE quiz_id=".$mlw_quiz_id;
		$results = $wpdb->query( $update );

		//See which answers were correct and award points if necessary
		$mlw_user_text = "";
		$mlw_correct_text = "";
		$mlw_qmn_answer_array = array();
		foreach($mlw_questions as $mlw_question) {
			$mlw_user_text = "";
			$mlw_correct_text = "";
			if ( isset($_POST["question".$mlw_question->question_id]) || isset($_POST["mlwComment".$mlw_question->question_id]) )
			{
				if ( $mlw_question->question_type == 0 || $mlw_question->question_type == 1 || $mlw_question->question_type == 2)
				{
					if (isset($_POST["question".$mlw_question->question_id]))
					{
						$mlw_user_answer = $_POST["question".$mlw_question->question_id];
					}
					else
					{
						$mlw_user_answer = " ";
					}						
					$mlw_qmn_question_answers_array = $mlw_qmn_loaded_answer_arrays[$mlw_question->question_id];
					foreach($mlw_qmn_question_answers_array as $mlw_qmn_question_answers_each)
					{
						if (htmlspecialchars(stripslashes($mlw_user_answer), ENT_QUOTES) == esc_attr($mlw_qmn_question_answers_each[0]))
						{
							$mlw_points += $mlw_qmn_question_answers_each[1];
							$mlw_user_text .= strval(htmlspecialchars_decode($mlw_qmn_question_answers_each[0], ENT_QUOTES));
							if ($mlw_qmn_question_answers_each[2] == 1)
							{
								$mlw_correct += 1;
							}
						}
						if ($mlw_qmn_question_answers_each[2] == 1)
						{
							$mlw_correct_text .= htmlspecialchars_decode($mlw_qmn_question_answers_each[0], ENT_QUOTES);
						}
					}
				}
				elseif ( $mlw_question->question_type == 3 ||  $mlw_question->question_type == 5 ||  $mlw_question->question_type == 7)
				{
					if (isset($_POST["question".$mlw_question->question_id]))
					{
						$mlw_user_answer = $_POST["question".$mlw_question->question_id];
					}
					else
					{
						$mlw_user_answer = " ";
					}
					$mlw_user_text .= strval(stripslashes(htmlspecialchars_decode($mlw_user_answer, ENT_QUOTES)));
					$mlw_qmn_question_answers_array = $mlw_qmn_loaded_answer_arrays[$mlw_question->question_id];
					foreach($mlw_qmn_question_answers_array as $mlw_qmn_question_answers_each)
					{
						$mlw_correct_text = strval(htmlspecialchars_decode($mlw_qmn_question_answers_each[0], ENT_QUOTES));
						if (strtoupper($mlw_user_text) == strtoupper($mlw_correct_text))
						{
							$mlw_correct += 1;
							$mlw_points += $mlw_qmn_question_answers_each[1];
							break;
						}
					}
				}
				elseif ( $mlw_question->question_type == 4 ||  $mlw_question->question_type == 10)
				{
					$mlw_qmn_user_correct_answers = 0;
					$mlw_qmn_total_correct_answers = 0;
					$mlw_qmn_question_answers_array = $mlw_qmn_loaded_answer_arrays[$mlw_question->question_id];
					$mlw_qmn_total_answers = count($mlw_qmn_question_answers_array);
					foreach($mlw_qmn_question_answers_array as $mlw_qmn_question_answers_each)
					{
						for ($i = 1; $i <= $mlw_qmn_total_answers; $i++) {
						    if (isset($_POST["question".$mlw_question->question_id."_".$i]) && htmlspecialchars(stripslashes($_POST["question".$mlw_question->question_id."_".$i]), ENT_QUOTES) == esc_attr($mlw_qmn_question_answers_each[0]))
						    {
						    	$mlw_points += $mlw_qmn_question_answers_each[1];
								$mlw_user_text .= strval(htmlspecialchars_decode($mlw_qmn_question_answers_each[0], ENT_QUOTES)).".";
								if ($mlw_qmn_question_answers_each[2] == 1)
								{
									$mlw_qmn_user_correct_answers += 1;
								}
								else
								{
									$mlw_qmn_user_correct_answers = -1;
								}
						    }
						}
						if ($mlw_qmn_question_answers_each[2] == 1)
						{
							$mlw_correct_text .= htmlspecialchars_decode($mlw_qmn_question_answers_each[0], ENT_QUOTES).".";
							$mlw_qmn_total_correct_answers++;
						}
					}
					if ($mlw_qmn_user_correct_answers == $mlw_qmn_total_correct_answers)
					{
						$mlw_correct += 1;
					}
				}
				if (isset($_POST["mlwComment".$mlw_question->question_id]))
				{
					$mlw_qm_question_comment = $_POST["mlwComment".$mlw_question->question_id];
				}
				else
				{
					$mlw_qm_question_comment = "";
				}
				
				$mlw_question_answer_display = htmlspecialchars_decode($mlw_quiz_options->question_answer_template, ENT_QUOTES);
				$mlw_question_answer_display = str_replace( "%QUESTION%" , htmlspecialchars_decode($mlw_question->question_name, ENT_QUOTES), $mlw_question_answer_display);
				$mlw_question_answer_display = str_replace( "%USER_ANSWER%" , $mlw_user_text, $mlw_question_answer_display);
				$mlw_question_answer_display = str_replace( "%CORRECT_ANSWER%" , $mlw_correct_text, $mlw_question_answer_display);
				$mlw_question_answer_display = str_replace( "%USER_COMMENTS%" , $mlw_qm_question_comment, $mlw_question_answer_display);
				$mlw_question_answer_display = str_replace( "%CORRECT_ANSWER_INFO%" , htmlspecialchars_decode($mlw_question->question_answer_info, ENT_QUOTES), $mlw_question_answer_display);
	
				$mlw_qmn_answer_array[] = array($mlw_question->question_name, htmlspecialchars($mlw_user_text, ENT_QUOTES), htmlspecialchars($mlw_correct_text, ENT_QUOTES), htmlspecialchars(stripslashes($mlw_qm_question_comment), ENT_QUOTES));
				
				$mlw_question_answers .= $mlw_question_answer_display;
				$mlw_question_answers .= "<br />";
			}
		}
		
		//Calculate Total Percent Score And Average Points Only If Total Questions Doesn't Equal Zero To Avoid Division By Zero Error
		if ($mlw_total_questions != 0)
		{
			$mlw_total_score = round((($mlw_correct/$mlw_total_questions)*100), 2);
			$mlw_average_points = round(($mlw_points/$mlw_total_questions), 2);
		}
		else
		{
			$mlw_total_score = 0;
			$mlw_average_points = 0;
		}
		
		//Prepare comment section if set
		if (isset($_POST["mlwQuizComments"]))
		{
			$mlw_qm_quiz_comments = $_POST["mlwQuizComments"];
		}
		else
		{
			$mlw_qm_quiz_comments = "";
		}
		
		
		//Prepare Certificate
		$mlw_certificate_link = "";
		$mlw_certificate_options = unserialize($mlw_quiz_options->certificate_template);
		if (!is_array($mlw_certificate_options)) {
	        // something went wrong, initialize to empty array
	        $mlw_certificate_options = array('Enter title here', 'Enter text here', '', '', 1);
	    }
	    if ($mlw_certificate_options[4] == 0)
	    {
			$mlw_message_certificate = $mlw_certificate_options[1];
			$mlw_message_certificate = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_certificate);
			$mlw_message_certificate = str_replace( "\n" , "<br>", $mlw_message_certificate);
			$mlw_plugindirpath = plugin_dir_path( __FILE__ );
			$plugindirpath=plugin_dir_path( __FILE__ );
			$mlw_qmn_certificate_file=<<<EOC
<?php
include("$plugindirpath/WriteHTML.php");
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
\$pdf->WriteHTML("<p align='center'>$mlw_message_certificate</p>");
EOC;
			$mlw_qmn_certificate_file.=$mlw_certificate_options[2] != '' ? '$pdf->Image("'.$mlw_certificate_options[2].'",110,130);' : '';
			$mlw_qmn_certificate_file.=<<<EOC
\$pdf->Output('mlw_qmn_certificate.pdf','D');
unlink(__FILE__);
EOC;
			$mlw_qmn_certificate_filename = str_replace(home_url()."/", '', plugin_dir_url( __FILE__ ))."certificates/mlw_qmn_quiz".date("YmdHis").$mlw_qmn_timer.".php";
			file_put_contents($mlw_qmn_certificate_filename, $mlw_qmn_certificate_file);
			$mlw_qmn_certificate_filename = plugin_dir_url( __FILE__ )."certificates/mlw_qmn_quiz".date("YmdHis").$mlw_qmn_timer.".php";
			$mlw_certificate_link = "<a href='".$mlw_qmn_certificate_filename."' style='color: blue;'>Download Certificate</a>";
	    }
	    
		/*
			Prepare the landing page
			-First, unserialize message_after column
			-Second, check for array in case user has not updated
			Message array = (array( bottomvalue, topvalue, text),array( bottomvalue, topvalue, text), etc..., array(0,0,text))
		*/
		$mlw_message_after_array = @unserialize($mlw_quiz_options->message_after);
		if (is_array($mlw_message_after_array))
		{
			//Cycle through landing pages
			foreach($mlw_message_after_array as $mlw_each)
			{
				//Check to see if default
				if ($mlw_each[0] == 0 && $mlw_each[1] == 0)
				{
					$mlw_message_after = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
					$mlw_message_after = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message_after);
					$mlw_message_after = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message_after);
					$mlw_message_after = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message_after);
					$mlw_message_after = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message_after);
					$mlw_message_after = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message_after);
					$mlw_message_after = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_after);
					$mlw_message_after = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message_after);
					$mlw_message_after = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message_after);
					$mlw_message_after = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message_after);
					$mlw_message_after = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message_after);
					$mlw_message_after = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message_after);
					$mlw_message_after = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message_after);
					$mlw_message_after = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message_after);
					$mlw_message_after = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message_after);
					$mlw_message_after = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_after);
					$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
					$mlw_display .= $mlw_message_after;
					break;
				}
				else
				{
					//Check to see if points fall in correct range
					if ($mlw_quiz_options->system == 1 && $mlw_points >= $mlw_each[0] && $mlw_points <= $mlw_each[1])
					{
						$mlw_message_after = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
						$mlw_message_after = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message_after);
						$mlw_message_after = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message_after);
						$mlw_message_after = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message_after);
						$mlw_message_after = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message_after);
						$mlw_message_after = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message_after);
						$mlw_message_after = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message_after);
						$mlw_message_after = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message_after);
						$mlw_message_after = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message_after);
						$mlw_message_after = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message_after);
						$mlw_message_after = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message_after);
						$mlw_message_after = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_after);
						$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
						$mlw_display .= $mlw_message_after;
						break;
					}
					//Check to see if score fall in correct range
					if ($mlw_quiz_options->system == 0 && $mlw_total_score >= $mlw_each[0] && $mlw_total_score <= $mlw_each[1])
					{
						$mlw_message_after = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
						$mlw_message_after = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message_after);
						$mlw_message_after = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message_after);
						$mlw_message_after = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message_after);
						$mlw_message_after = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message_after);
						$mlw_message_after = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message_after);
						$mlw_message_after = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message_after);
						$mlw_message_after = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message_after);
						$mlw_message_after = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message_after);
						$mlw_message_after = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message_after);
						$mlw_message_after = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message_after);
						$mlw_message_after = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message_after);
						$mlw_message_after = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_after);
						$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
						$mlw_display .= $mlw_message_after;
						break;
					}
				}				
			}			
		}
		else
		{
			//Prepare the after quiz message
			$mlw_message_after = htmlspecialchars_decode($mlw_quiz_options->message_after, ENT_QUOTES);
			$mlw_message_after = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message_after);
			$mlw_message_after = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message_after);
			$mlw_message_after = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message_after);
			$mlw_message_after = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message_after);
			$mlw_message_after = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message_after);
			$mlw_message_after = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message_after);
			$mlw_message_after = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message_after);
			$mlw_message_after = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message_after);
			$mlw_message_after = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message_after);
			$mlw_message_after = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message_after);
			$mlw_message_after = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message_after);
			$mlw_message_after = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message_after);
			$mlw_message_after = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message_after);
			$mlw_message_after = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message_after);
			$mlw_message_after = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message_after);
			$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
			$mlw_display .= $mlw_message_after;
		}
		
		if ($mlw_quiz_options->social_media == 1)
		{
		?>
			<script>
			function mlw_qmn_share(network, mlw_qmn_social_text, mlw_qmn_title)
			{
				var sTop = window.screen.height/2-(218);
                var sLeft = window.screen.width/2-(313);
				var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
				var pageUrl = window.location.href;
                var pageUrlEncoded = encodeURIComponent(pageUrl);
				if (network == 1)
				{
					var Url = "https://www.facebook.com/dialog/feed?"
                    	+ "display=popup&"
                        + "app_id=483815031724529&"
                        + "link=" + pageUrlEncoded + "&"
                        + "name=" + encodeURIComponent(mlw_qmn_social_text) + "&"
						+ "description=  &"
                        + "redirect_uri=http://www.mylocalwebstop.com/mlw_qmn_close.html";
				}
				if (network == 2)
				{
					var Url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(mlw_qmn_social_text);
				}
                window.open(Url, "Share", sqShareOptions);
                return false;
            }
			</script>
			<?php
			$mlw_social_message = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_quiz_options->social_media_text);
			$mlw_social_message = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_social_message);
			$mlw_social_message = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_social_message);
			$mlw_social_message = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_social_message);
			$mlw_social_message = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_social_message);
			$mlw_social_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_social_message);
			$mlw_social_message = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_social_message);
			$mlw_social_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_social_message);
			$mlw_display .= "<br />
			<a class=\"mlw_qmn_quiz_link\" style=\"display: inline; vertical-align:top !important;font-weight: bold; cursor: pointer;text-decoration: none;\" onclick=\"mlw_qmn_share(1, '".esc_js($mlw_social_message)."', '".esc_js($mlw_quiz_options->quiz_name)."');\">Facebook</a>
			<a class=\"mlw_qmn_quiz_link\" style=\"display: inline; vertical-align:top !important;font-weight: bold; cursor: pointer;text-decoration: none;\" onclick=\"mlw_qmn_share(2, '".esc_js($mlw_social_message)."', '".esc_js($mlw_quiz_options->quiz_name)."');\">Twitter</a>
			<br />";
		}
		
		//Switch email type to HTML
		add_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
	
		/*
			Prepare and send the user email
			- First, check to see if user_email_template is the newer array format, if not use it as the template
			- If it is an array, check to see if the score meets the parameter of one of the templates. If, not send the default
		*/
		$mlw_message = "";
		if ($mlw_quiz_options->send_user_email == "0")
		{
			if ($mlw_user_email != "")
			{
				$mlw_user_email_array = @unserialize($mlw_quiz_options->user_email_template);
				if (is_array($mlw_user_email_array))
				{
					//Cycle through landing pages
					foreach($mlw_user_email_array as $mlw_each)
					{
						
						//Generate Email Subject
						if (!isset($mlw_each[3]))
						{
							$mlw_each[3] = "Quiz Results For %QUIZ_NAME";
						}
						$mlw_each[3] = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_each[3]);
						$mlw_each[3] = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_each[3]);
						
						
						//Check to see if default
						if ($mlw_each[0] == 0 && $mlw_each[1] == 0)
						{
							$mlw_message = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
							$mlw_message = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message);
							$mlw_message = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message);
							$mlw_message = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message);
							$mlw_message = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message);
							$mlw_message = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message);
							$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
							$mlw_message = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message);
							$mlw_message = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message);
							$mlw_message = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message);
							$mlw_message = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message);
							$mlw_message = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message);
							$mlw_message = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message);
							$mlw_message = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message);
							$mlw_message = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message);
							$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
							$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
							$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
							$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
							$mlw_headers = 'From: '.$mlw_quiz_options->email_from_text.' <'.$mlw_quiz_options->admin_email.'>' . "\r\n";
							wp_mail($mlw_user_email, $mlw_each[3], $mlw_message, $mlw_headers);
							break;
						}
						else
						{
							//Check to see if points fall in correct range
							if ($mlw_quiz_options->system == 1 && $mlw_points >= $mlw_each[0] && $mlw_points <= $mlw_each[1])
							{
								$mlw_message = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
								$mlw_message = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message);
								$mlw_message = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message);
								$mlw_message = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message);
								$mlw_message = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message);
								$mlw_message = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message);
								$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
								$mlw_message = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message);
								$mlw_message = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message);
								$mlw_message = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message);
								$mlw_message = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message);
								$mlw_message = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message);
								$mlw_message = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message);
								$mlw_message = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message);
								$mlw_message = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message);
								$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
								$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
								$mlw_headers = 'From: '.$mlw_quiz_options->email_from_text.' <'.$mlw_quiz_options->admin_email.'>' . "\r\n";
								wp_mail($mlw_user_email, $mlw_each[3], $mlw_message, $mlw_headers);
								break;
							}
							
							//Check to see if score fall in correct range
							if ($mlw_quiz_options->system == 0 && $mlw_total_score >= $mlw_each[0] && $mlw_total_score <= $mlw_each[1])
							{
								$mlw_message = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
								$mlw_message = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message);
								$mlw_message = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message);
								$mlw_message = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message);
								$mlw_message = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message);
								$mlw_message = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message);
								$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
								$mlw_message = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message);
								$mlw_message = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message);
								$mlw_message = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message);
								$mlw_message = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message);
								$mlw_message = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message);
								$mlw_message = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message);
								$mlw_message = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message);
								$mlw_message = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message);
								$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
								$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
								$mlw_headers = 'From: '.$mlw_quiz_options->email_from_text.' <'.$mlw_quiz_options->admin_email.'>' . "\r\n";
								wp_mail($mlw_user_email, $mlw_each[3], $mlw_message, $mlw_headers);
								break;
							}
						}
					}
				}
				else
				{
					$mlw_message = htmlspecialchars_decode($mlw_quiz_options->user_email_template, ENT_QUOTES);
					$mlw_message = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message);
					$mlw_message = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message);
					$mlw_message = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message);
					$mlw_message = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message);
					$mlw_message = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message);
					$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
					$mlw_message = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message);
					$mlw_message = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message);
					$mlw_message = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message);
					$mlw_message = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message);
					$mlw_message = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message);
					$mlw_message = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message);
					$mlw_message = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message);
					$mlw_message = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message);
					$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
					$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
					$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
					$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
					$mlw_headers = 'From: '.$mlw_quiz_options->email_from_text.' <'.$mlw_quiz_options->admin_email.'>' . "\r\n";
					wp_mail($mlw_user_email, "Quiz Results For ".$mlw_quiz_options->quiz_name, $mlw_message, $mlw_headers);
				}
			}
		}

		//Prepare and send the admin email
		$mlw_message = "";
		if ($mlw_quiz_options->send_admin_email == "0")
		{
			$mlw_message = htmlspecialchars_decode($mlw_quiz_options->admin_email_template, ENT_QUOTES);
			$mlw_message = str_replace( "%POINT_SCORE%" , $mlw_points, $mlw_message);
			$mlw_message = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $mlw_message);
			$mlw_message = str_replace( "%AMOUNT_CORRECT%" , $mlw_correct, $mlw_message);
			$mlw_message = str_replace( "%TOTAL_QUESTIONS%" , $mlw_total_questions, $mlw_message);
			$mlw_message = str_replace( "%CORRECT_SCORE%" , $mlw_total_score, $mlw_message);
			$mlw_message = str_replace( "%USER_NAME%" , $mlw_user_name, $mlw_message);
			$mlw_message = str_replace( "%USER_BUSINESS%" , $mlw_user_comp, $mlw_message);
			$mlw_message = str_replace( "%USER_PHONE%" , $mlw_user_phone, $mlw_message);
			$mlw_message = str_replace( "%USER_EMAIL%" , $mlw_user_email, $mlw_message);
			$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
			$mlw_message = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $mlw_message);
			$mlw_message = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $mlw_message);
			$mlw_message = str_replace( "%TIMER%" , $mlw_qmn_timer, $mlw_message);
			$mlw_message = str_replace( "%CERTIFICATE_LINK%" , $mlw_certificate_link, $mlw_message);
			$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
			if ( get_option('mlw_advert_shows') == 'true' ) {$mlw_message .= "<br>This email was generated by the Quiz Master Next script by Frank Corso";}
			$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
			$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
			$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
			$mlw_headers = 'From: '.$mlw_quiz_options->email_from_text.' <'.$mlw_quiz_options->admin_email.'>' . "\r\n";
			$mlw_qmn_admin_emails = explode(",", $mlw_quiz_options->admin_email);
			foreach($mlw_qmn_admin_emails as $admin_email)
			{
				wp_mail($admin_email, "Quiz Results For ".$mlw_quiz_options->quiz_name, $mlw_message, $mlw_headers);
			}
		}
		
		//Remove HTML type for emails
		remove_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );

		//Save the results into database
		$mlw_quiz_results_array = array( intval($mlw_qmn_timer), $mlw_qmn_answer_array, htmlspecialchars(stripslashes($mlw_qm_quiz_comments), ENT_QUOTES));
		$mlw_quiz_results = serialize($mlw_quiz_results_array);
		
		global $wpdb;
		$table_name = $wpdb->prefix . "mlw_results";
		$results = $wpdb->query( $wpdb->prepare( "INSERT INTO " . $table_name . " (result_id, quiz_id, quiz_name, quiz_system, point_score, correct_score, correct, total, name, business, email, phone, user, time_taken, time_taken_real, quiz_results, deleted) VALUES (NULL, %d, '%s', %d, %d, %d, %d, %d, '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s', 0)", $mlw_quiz_id, $mlw_quiz_options->quiz_name, $mlw_quiz_options->system, $mlw_points, $mlw_total_score, $mlw_correct, $mlw_total_questions, $mlw_user_name, $mlw_user_comp, $mlw_user_email, $mlw_user_phone, get_current_user_id(), date("h:i:s A m/d/Y"), date("Y-m-d H:i:s"), $mlw_quiz_results) );
		
		//Integration Action
		do_action('mlw_qmn_load_results_page', $wpdb->insert_id, $mlw_quiz_options->quiz_settings);
		}
		else
		{
			if (!$mlw_qmn_isAllowed)
			{
				$current_user = wp_get_current_user();
				$mlw_message = htmlspecialchars_decode($mlw_quiz_options->total_user_tries_text, ENT_QUOTES);
				$mlw_message = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_message);
				$mlw_message = str_replace( "%USER_NAME%" , $current_user->display_name, $mlw_message);
				$mlw_message = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $mlw_message);
				$mlw_display .= $mlw_message;
			}
			elseif (isset($_POST["mlw_code_captcha"]) && $_POST["mlw_user_captcha"] != $_POST["mlw_code_captcha"])
			{
				$mlw_display .= "There was an issue with the captcha verification. Please try again.";	
			}
			else { $mlw_display .= "Thank you.";	}
		}
	}
return $mlw_display;
}


/*
This function displays fields to ask for contact information
*/
function mlwDisplayContactInfo($mlw_quiz_options)
{
	$mlw_contact_display = "";
	//Check to see if user is logged in, then ask for contact if not
	if ( is_user_logged_in() )
	{
		//If this quiz does not let user edit contact information we hide this section
		if ($mlw_quiz_options->loggedin_user_contact == 1)
		{
			$mlw_contact_display .= "<div style='display:none;'>";
		}
		
		//Retrieve current user information and save into text fields for contact information
		$current_user = wp_get_current_user();
		if ($mlw_quiz_options->user_name != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_name == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->name_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserName' value='".$current_user->display_name."' />";
			$mlw_contact_display .= "<br /><br />";

		}
		if ($mlw_quiz_options->user_comp != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_comp == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->business_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserComp' value='' />";
			$mlw_contact_display .= "<br /><br />";
		}
		if ($mlw_quiz_options->user_email != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_email == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->email_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserEmail' value='".$current_user->user_email."' />";
			$mlw_contact_display .= "<br /><br />";
		}
		if ($mlw_quiz_options->user_phone != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_phone == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->phone_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserPhone' value='' />";
			$mlw_contact_display .= "<br /><br />";
		}

		//End of hidden section div
		if ($mlw_quiz_options->loggedin_user_contact == 1)
		{
			$mlw_contact_display .= "</div>";
		}
	}
	else
	{
		//See if the site wants to ask for any contact information, then ask for it
		if ($mlw_quiz_options->user_name != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_name == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->name_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserName' value='' />";
			$mlw_contact_display .= "<br /><br />";
		}
		if ($mlw_quiz_options->user_comp != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_comp == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->business_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserComp' value='' />";
			$mlw_contact_display .= "<br /><br />";
		}
		if ($mlw_quiz_options->user_email != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_email == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->email_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserEmail' value='' />";
			$mlw_contact_display .= "<br /><br />";
		}
		if ($mlw_quiz_options->user_phone != 2)
		{
			$mlw_contact_class = "class=\"\"";
			if ($mlw_quiz_options->user_phone == 1)
			{
				$mlw_contact_class = "class=\"mlwRequiredText\"";
			}
			$mlw_contact_display .= "<span style='font-weight:bold;';>".htmlspecialchars_decode($mlw_quiz_options->phone_field_text, ENT_QUOTES)."</span><br />";
			$mlw_contact_display .= "<input type='text' $mlw_contact_class x-webkit-speech name='mlwUserPhone' value='' />";
			$mlw_contact_display .= "<br /><br />";
		}
	}
	return $mlw_contact_display;
}

/*
This function helps set the email type to HTML
*/
function mlw_qmn_set_html_content_type() {

	return 'text/html';
}
?>

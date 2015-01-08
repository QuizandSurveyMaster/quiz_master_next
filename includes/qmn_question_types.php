<?php

add_action("plugins_loaded", 'qmn_question_type_multiple_choice');
function qmn_question_type_multiple_choice()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Multiple Choice", 'quiz-master-next'), 'qmn_multiple_choice_display', true, 'qmn_multiple_choice_review', 0);
}

function qmn_multiple_choice_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredRadio";} else {$mlw_requireClass = "";}
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<div class='qmn_radio_answers $mlw_requireClass'>";
  if (is_array($answers))
  {
    $mlw_answer_total = 0;
    foreach($answers as $answer)
    {
      $mlw_answer_total++;
      if ($answer[0] != "")
      {
        $question_display .= "<input type='radio' class='qmn_quiz_radio' name='question".$id."' id='question".$id."_".$mlw_answer_total."' value='".esc_attr($answer[0])."' /> <label for='question".$id."_".$mlw_answer_total."'>".htmlspecialchars_decode($answer[0], ENT_QUOTES)."</label>";
        $question_display .= "<br />";
      }
    }
    $question_display .= "<input type='radio' style='display: none;' name='question".$id."' id='question".$id."_none' checked='checked' value='No Answer Provided' />";
  }
  $question_display .= "</div>";
  return $question_display;
}

function qmn_multiple_choice_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_horizontal_multiple_choice');
function qmn_question_type_horizontal_multiple_choice()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Horizontal Multiple Choice", 'quiz-master-next'), 'qmn_horizontal_multiple_choice_display', true, 'qmn_horizontal_multiple_choice_review', 1);
}

function qmn_horizontal_multiple_choice_display($id, $question, $answers)
{

}

function qmn_horizontal_multiple_choice_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_drop_down');
function qmn_question_type_drop_down()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Drop Down", 'quiz-master-next'), 'qmn_drop_down_display', true, 'qmn_drop_down_review', 2);
}

function qmn_drop_down_display($id, $question, $answers)
{

}

function qmn_drop_down_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_small_open');
function qmn_question_type_small_open()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Small Open Answer", 'quiz-master-next'), 'qmn_small_open_display', true, 'qmn_small_open_review', 3);
}

function qmn_small_open_display($id, $question, $answers)
{

}

function qmn_small_open_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_multiple_response');
function qmn_question_type_multiple_response()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Multiple Response", 'quiz-master-next'), 'qmn_multiple_response_display', true, 'qmn_multiple_response_review', 4);
}

function qmn_multiple_response_display($id, $question, $answers)
{

}

function qmn_multiple_response_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_large_open');
function qmn_question_type_large_open()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Large Open Answer", 'quiz-master-next'), 'qmn_large_open_display', true, 'qmn_large_open_review', 5);
}

function qmn_large_open_display($id, $question, $answers)
{

}

function qmn_large_open_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_text_block');
function qmn_question_type_text_block()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Text Block", 'quiz-master-next'), 'qmn_text_block_display', false, null, 6);
}

function qmn_text_block_display($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_number');
function qmn_question_type_number()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Number", 'quiz-master-next'), 'qmn_number_display', true, 'qmn_number_review', 7);
}

function qmn_number_display($id, $question, $answers)
{

}

function qmn_number_review($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_accept');
function qmn_question_type_accept()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Accept", 'quiz-master-next'), 'qmn_accept_display', false, null, 8);
}

function qmn_accept_display($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_captcha');
function qmn_question_type_captcha()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Captcha", 'quiz-master-next'), 'qmn_captcha_display', false, null, 9);
}

function qmn_captcha_display($id, $question, $answers)
{

}

add_action("plugins_loaded", 'qmn_question_type_horizontal_multiple_response');
function qmn_question_type_horizontal_multiple_response()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Horizontal Multiple Response", 'quiz-master-next'), 'qmn_horizontal_multiple_response_display', true, 'qmn_horizontal_multiple_response_review', 10);
}

function qmn_horizontal_multiple_response_display($id, $question, $answers)
{

}

function qmn_horizontal_multiple_response_review($id, $question, $answers)
{

}


?>

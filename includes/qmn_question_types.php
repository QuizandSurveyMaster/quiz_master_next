<?php

add_action("plugins_loaded", 'qmn_question_type_multiple_choice');
function qmn_question_type_multiple_choice()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Multiple Choice", 'quiz-master-next'), 'qmn_multiple_choice_display', true, 'qmn_multiple_choice_review', 0);
}

function qmn_multiple_choice_display($id, $question, $answers)
{

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

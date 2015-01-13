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
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  if (isset($_POST["question".$id]))
  {
    $mlw_user_answer = $_POST["question".$id];
  }
  else
  {
    $mlw_user_answer = " ";
  }
  foreach($answers as $answer)
  {
    if (htmlspecialchars(stripslashes($mlw_user_answer), ENT_QUOTES) == esc_attr($answer[0]))
    {
      $return_array["points"] = $answer[1];
      $return_array["user_text"] = strval(htmlspecialchars_decode($answer[0], ENT_QUOTES));
      if ($answer[2] == 1)
      {
        $return_array["correct"] = "correct";
      }
    }
    if ($answer[2] == 1)
    {
      $return_array["correct_text"] = htmlspecialchars_decode($answer[0], ENT_QUOTES);
    }
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_horizontal_multiple_choice');
function qmn_question_type_horizontal_multiple_choice()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Horizontal Multiple Choice", 'quiz-master-next'), 'qmn_horizontal_multiple_choice_display', true, 'qmn_horizontal_multiple_choice_review', 1);
}

function qmn_horizontal_multiple_choice_display($id, $question, $answers)
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
        $question_display .= "<span class='mlw_horizontal_choice'><input type='radio' class='qmn_quiz_radio' name='question".$id."' id='question".$id."_".$mlw_answer_total."' value='".esc_attr($answer[0])."' /><label for='question".$id."_".$mlw_answer_total."'>".htmlspecialchars_decode($answer[0], ENT_QUOTES)."</label></span>";
      }
    }
    $question_display .= "<input type='radio' style='display: none;' name='question".$id."' id='question".$id."_none' checked='checked' value='No Answer Provided' />";
  }
  $question_display .= "</div>";
  $question_display .= "<br />";
  return $question_display;
}

function qmn_horizontal_multiple_choice_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  if (isset($_POST["question".$id]))
  {
    $mlw_user_answer = $_POST["question".$id];
  }
  else
  {
    $mlw_user_answer = " ";
  }
  foreach($answers as $answer)
  {
    if (htmlspecialchars(stripslashes($mlw_user_answer), ENT_QUOTES) == esc_attr($answer[0]))
    {
      $return_array["points"] = $answer[1];
      $return_array["user_text"] = strval(htmlspecialchars_decode($answer[0], ENT_QUOTES));
      if ($answer[2] == 1)
      {
        $return_array["correct"] = "correct";
      }
    }
    if ($answer[2] == 1)
    {
      $return_array["correct_text"] = htmlspecialchars_decode($answer[0], ENT_QUOTES);
    }
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_drop_down');
function qmn_question_type_drop_down()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Drop Down", 'quiz-master-next'), 'qmn_drop_down_display', true, 'qmn_drop_down_review', 2);
}

function qmn_drop_down_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<select name='question".$id."'>";
  if (is_array($answers))
  {
    $mlw_answer_total = 0;
    foreach($answers as $answer)
    {
      $mlw_answer_total++;
      if ($answer[0] != "")
      {
        $question_display .= "<option value='".esc_attr($answer[0])."'>".htmlspecialchars_decode($answer[0], ENT_QUOTES)."</option>";
      }
    }
  }
  $question_display .= "</select>";
  $question_display .= "<br />";
  return $question_display;
}

function qmn_drop_down_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  if (isset($_POST["question".$id]))
  {
    $mlw_user_answer = $_POST["question".$id];
  }
  else
  {
    $mlw_user_answer = " ";
  }
  foreach($answers as $answer)
  {
    if (htmlspecialchars(stripslashes($mlw_user_answer), ENT_QUOTES) == esc_attr($answer[0]))
    {
      $return_array["points"] = $answer[1];
      $return_array["user_text"] = strval(htmlspecialchars_decode($answer[0], ENT_QUOTES));
      if ($answer[2] == 1)
      {
        $return_array["correct"] = "correct";
      }
    }
    if ($answer[2] == 1)
    {
      $return_array["correct_text"] = htmlspecialchars_decode($answer[0], ENT_QUOTES);
    }
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_small_open');
function qmn_question_type_small_open()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Small Open Answer", 'quiz-master-next'), 'qmn_small_open_display', true, 'qmn_small_open_review', 3);
}

function qmn_small_open_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredText";} else {$mlw_requireClass = "";}
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<input type='text' class='mlw_answer_open_text $mlw_requireClass' name='question".$id."' />";
  $question_display .= "<br />";
  return $question_display;
}

function qmn_small_open_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  if (isset($_POST["question".$id]))
  {
    $mlw_user_answer = $_POST["question".$id];
  }
  else
  {
    $mlw_user_answer = " ";
  }
  $return_array['user_text'] = strval(stripslashes(htmlspecialchars_decode($mlw_user_answer, ENT_QUOTES)));
  foreach($answers as $answer)
  {
    $return_array['correct_text'] = strval(htmlspecialchars_decode($answer[0], ENT_QUOTES));
    if (strtoupper($return_array['user_text']) == strtoupper($return_array['correct_text']))
    {
      $return_array['correct'] = "correct";
      $return_array['points'] = $answer[1];
      break;
    }
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_multiple_response');
function qmn_question_type_multiple_response()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Multiple Response", 'quiz-master-next'), 'qmn_multiple_response_display', true, 'qmn_multiple_response_review', 4);
}

function qmn_multiple_response_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredCheck";} else {$mlw_requireClass = "";}
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<div class='qmn_check_answers $mlw_requireClass'>";
  if (is_array($answers))
  {
    $mlw_answer_total = 0;
    foreach($answers as $answer)
    {
      $mlw_answer_total++;
      if ($answer[0] != "")
      {
        $question_display .= "<input type='hidden' name='question".$id."' value='This value does not matter' />";
        $question_display .= "<input type='checkbox' name='question".$id."_".$mlw_answer_total."' id='question".$id."_".$mlw_answer_total."' value='".esc_attr($answer[0])."' /> <label for='question".$id."_".$mlw_answer_total."'>".htmlspecialchars_decode($answer[0], ENT_QUOTES)."</label>";
        $question_display .= "<br />";
      }
    }
  }
  $question_display .= "</div>";
  return $question_display;
}

function qmn_multiple_response_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  $user_correct = 0;
  $total_correct = 0;
  $total_answers = count($answers);
  foreach($answers as $answer)
  {
    for ($i = 1; $i <= $total_answers; $i++)
    {
        if (isset($_POST["question".$id."_".$i]) && htmlspecialchars(stripslashes($_POST["question".$id."_".$i]), ENT_QUOTES) == esc_attr($answer[0]))
        {
          $return_array["points"] = $answer[1];
          $return_array["user_text"] .= strval(htmlspecialchars_decode($answer[0], ENT_QUOTES)).".";
          if ($answer[2] == 1)
          {
            $user_correct += 1;
          }
          else
          {
            $user_correct = -1;
          }
        }
    }
    if ($answer[2] == 1)
    {
      $return_array["correct_text"] .= htmlspecialchars_decode($answer[0], ENT_QUOTES).".";
      $total_correct++;
    }
  }
  if ($user_correct == $total_correct)
  {
    $return_array["correct"] = "correct";
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_large_open');
function qmn_question_type_large_open()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Large Open Answer", 'quiz-master-next'), 'qmn_large_open_display', true, 'qmn_large_open_review', 5);
}

function qmn_large_open_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredText";} else {$mlw_requireClass = "";}
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<textarea class='mlw_answer_open_text $mlw_requireClass' cols='70' rows='5' name='question".$id."' /></textarea>";
  $question_display .= "<br />";
  return $question_display;
}

function qmn_large_open_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  if (isset($_POST["question".$id]))
  {
    $mlw_user_answer = $_POST["question".$id];
  }
  else
  {
    $mlw_user_answer = " ";
  }
  $return_array['user_text'] = strval(stripslashes(htmlspecialchars_decode($mlw_user_answer, ENT_QUOTES)));
  foreach($answers as $answer)
  {
    $return_array['correct_text'] = strval(htmlspecialchars_decode($answer[0], ENT_QUOTES));
    if (strtoupper($return_array['user_text']) == strtoupper($return_array['correct_text']))
    {
      $return_array['correct'] = "correct";
      $return_array['points'] = $answer[1];
      break;
    }
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_text_block');
function qmn_question_type_text_block()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Text Block", 'quiz-master-next'), 'qmn_text_block_display', false, null, 6);
}

function qmn_text_block_display($id, $question, $answers)
{
  $question_display = '';
  $question_display .= htmlspecialchars_decode($question, ENT_QUOTES);
  $question_display .= "<br />";
  return $question_display;
}

add_action("plugins_loaded", 'qmn_question_type_number');
function qmn_question_type_number()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Number", 'quiz-master-next'), 'qmn_number_display', true, 'qmn_number_review', 7);
}

function qmn_number_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredNumber";} else {$mlw_requireClass = "";}
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<input type='number' class='mlw_answer_number $mlw_requireClass' name='question".$id."' />";
  $question_display .= "<br />";
  return $question_display;
}

function qmn_number_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  if (isset($_POST["question".$id]))
  {
    $mlw_user_answer = $_POST["question".$id];
  }
  else
  {
    $mlw_user_answer = " ";
  }
  $return_array['user_text'] = strval(stripslashes(htmlspecialchars_decode($mlw_user_answer, ENT_QUOTES)));
  foreach($answers as $answer)
  {
    $return_array['correct_text'] = strval(htmlspecialchars_decode($answer[0], ENT_QUOTES));
    if (strtoupper($return_array['user_text']) == strtoupper($return_array['correct_text']))
    {
      $return_array['correct'] = "correct";
      $return_array['points'] = $answer[1];
      break;
    }
  }
  return $return_array;
}

add_action("plugins_loaded", 'qmn_question_type_accept');
function qmn_question_type_accept()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Accept", 'quiz-master-next'), 'qmn_accept_display', false, null, 8);
}

function qmn_accept_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredAccept";} else {$mlw_requireClass = "";}
  $question_display .= "<input type='checkbox' id='mlwAcceptance' class='$mlw_requireClass ' />";
  $question_display .= "<label for='mlwAcceptance'><span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span></label>";
  $question_display .= "<br />";
  return $question_display;
}

add_action("plugins_loaded", 'qmn_question_type_captcha');
function qmn_question_type_captcha()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Captcha", 'quiz-master-next'), 'qmn_captcha_display', false, null, 9);
}

function qmn_captcha_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredCaptcha";} else {$mlw_requireClass = "";}
  $question_display .= "<div class='mlw_captchaWrap'>";
  $question_display .= "<canvas alt='' id='mlw_captcha' class='mlw_captcha' width='100' height='50'></canvas>";
  $question_display .= "</div>";
  $question_display .= "<span class='mlw_qmn_question'>";
  $question_display .= htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<input type='text' class='mlw_answer_open_text $mlw_requireClass' id='mlw_captcha_text' name='mlw_user_captcha'/>";
  $question_display .= "<input type='hidden' name='mlw_code_captcha' id='mlw_code_captcha' value='none' />";
  $question_display .= "<br />";
  $question_display .= "<script>
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
  return $question_display;
}

add_action("plugins_loaded", 'qmn_question_type_horizontal_multiple_response');
function qmn_question_type_horizontal_multiple_response()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type(__("Horizontal Multiple Response", 'quiz-master-next'), 'qmn_horizontal_multiple_response_display', true, 'qmn_horizontal_multiple_response_review', 10);
}

function qmn_horizontal_multiple_response_display($id, $question, $answers)
{
  $question_display = '';
  global $mlwQuizMasterNext;
  $required = $mlwQuizMasterNext->pluginHelper->get_question_setting($id, 'required');
  if ($required == 0) {$mlw_requireClass = "mlwRequiredCheck";} else {$mlw_requireClass = "";}
  $question_display .= "<span class='mlw_qmn_question'>".htmlspecialchars_decode($question, ENT_QUOTES)."</span><br />";
  $question_display .= "<div class='qmn_check_answers $mlw_requireClass'>";
  if (is_array($answers))
  {
    $mlw_answer_total = 0;
    foreach($answers as $answer)
    {
      $mlw_answer_total++;
      if ($answer[0] != "")
      {
        $question_display .= "<input type='hidden' name='question".$id."' value='This value does not matter' />";
        $question_display .= "<span class='mlw_horizontal_multiple'><input type='checkbox' name='question".$id."_".$mlw_answer_total."' id='question".$id."_".$mlw_answer_total."' value='".esc_attr($answer[0])."' /> <label for='question".$id."_".$mlw_answer_total."'>".htmlspecialchars_decode($answer[0], ENT_QUOTES)."&nbsp;</label></span>";
      }
    }
  }
  $question_display .= "</div>";
  return $question_display;
}

function qmn_horizontal_multiple_response_review($id, $question, $answers)
{
  $return_array = array(
    'points' => 0,
    'correct' => 'incorrect',
    'user_text' => '',
    'correct_text' => ''
  );
  $user_correct = 0;
  $total_correct = 0;
  $total_answers = count($answers);
  foreach($answers as $answer)
  {
    for ($i = 1; $i <= $total_answers; $i++)
    {
        if (isset($_POST["question".$id."_".$i]) && htmlspecialchars(stripslashes($_POST["question".$id."_".$i]), ENT_QUOTES) == esc_attr($answer[0]))
        {
          $return_array["points"] = $answer[1];
          $return_array["user_text"] .= strval(htmlspecialchars_decode($answer[0], ENT_QUOTES)).".";
          if ($answer[2] == 1)
          {
            $user_correct += 1;
          }
          else
          {
            $user_correct = -1;
          }
        }
    }
    if ($answer[2] == 1)
    {
      $return_array["correct_text"] .= htmlspecialchars_decode($answer[0], ENT_QUOTES).".";
      $total_correct++;
    }
  }
  if ($user_correct == $total_correct)
  {
    $return_array["correct"] = "correct";
  }
  return $return_array;
}
?>

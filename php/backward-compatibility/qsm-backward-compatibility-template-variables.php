<?php

function qsm_bckcmp_tempvar_qa_text_qt_multi_choice_correct( $answers_from_db, $answers_from_response, $question_settings ) {
    $question_with_answer_text = '';
    $new_array_user_answer = isset($answers_from_response['user_compare_text']) ? explode('=====', $answers_from_response['user_compare_text']) : array();
    foreach ( $answers_from_db as $single_answer ) {
        $current_answer_text = trim(stripslashes(htmlspecialchars_decode($single_answer[0], ENT_QUOTES)));
        $is_answer_correct   = false;
        if ( $new_array_user_answer ) {
            foreach ( $new_array_user_answer as $new_array_value ) {
                $new_array_value = trim(stripslashes(htmlspecialchars_decode($new_array_value, ENT_QUOTES)));
                if ( $current_answer_text == $new_array_value ) {
                    $is_answer_correct = true;
                    break;
                }
            }
        }
        $image_class = '';
        if ( isset($question_settings['answerEditor']) && 'image' == $question_settings['answerEditor'] ) {
            $show_user_answer = '<img src="' . htmlspecialchars_decode($single_answer[0], ENT_QUOTES) . '"/>';
            $image_class      = 'qmn_image_option';
        } else {
            $show_user_answer = htmlspecialchars_decode($single_answer[0], ENT_QUOTES);
            $image_class      = '';
        }
        if ( isset($single_answer[2]) && 1 == $single_answer[2] && $is_answer_correct ) {
            $question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answers_from_response ' . $image_class . '">' . $show_user_answer . '</span>';
        } elseif ( isset($single_answer[2]) && 1 === $single_answer[2] ) {
            $question_with_answer_text .= '<span class="qsm-text-correct-option ' . $image_class . '">' . $show_user_answer . '</span>';
        } elseif ( $is_answer_correct && 1 !== $single_answer[2] ) {
            $question_with_answer_text .= '<span class="qsm-text-wrong-option ' . $image_class . '">' . $show_user_answer . '</span>';
        } else {
            $question_with_answer_text .= '<span class="qsm-text-simple-option ' . $image_class . '">' . $show_user_answer . '</span>';
        }
    }

    return $question_with_answer_text;
}

function qsm_bckcmp_tempvar_qa_text_qt_single_choice_correct( $answers_from_db, $answers_from_response, $question_settings ) {
    $question_with_answer_text = '';
    foreach ( $answers_from_db as $single_answer ) {
        $single_answer_option = $single_answer[0];
        if ( isset($question_settings['answerEditor']) && 'rich' == $question_settings['answerEditor'] ) {
            $single_answer_option = htmlspecialchars_decode($single_answer[0], ENT_QUOTES);
            $single_answer_option = htmlentities($single_answer_option);
            if ( strpos($single_answer_option, '&lt;') !== false || strpos($single_answer_option, '&quot;') !== false ) {
                $single_answer_option = htmlspecialchars($single_answer_option);
            }
            if ( strpos($answers_from_response[1], '&lt;') !== false || strpos($answers_from_response[1], '&quot;') !== false ) {
                $answer_value = htmlentities($answers_from_response[1]);
            } else {
                $answer_value = htmlspecialchars_decode($answers_from_response[1], ENT_QUOTES);
                $answer_value = htmlspecialchars_decode($answer_value, ENT_QUOTES);
                $answer_value = htmlentities($answer_value);
                $answer_value = htmlspecialchars($answer_value);
            }
        } else {
            $answer_value = htmlspecialchars_decode($answers_from_response[1], ENT_QUOTES);
        }
        $image_class = '';
        if ( isset($question_settings['answerEditor']) && 'image' == $question_settings['answerEditor'] ) {
            $show_user_answer = '<img src="' . htmlspecialchars_decode($single_answer[0], ENT_QUOTES) . '"/>';
            $image_class      = 'qmn_image_option';
        } else {
            $show_user_answer = htmlspecialchars_decode($single_answer[0], ENT_QUOTES);
            $image_class      = '';
        }
        if ( isset($single_answer[2]) && 1 == $single_answer[2] && $answer_value == $single_answer_option ) {
            $question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer ' . $image_class . '">' . $show_user_answer . '</span>';
        } elseif ( isset($single_answer[2]) && 1 == $single_answer[2] ) {
            $question_with_answer_text .= '<span class="qsm-text-correct-option ' . $image_class . '">' . $show_user_answer . '</span>';
        } elseif ( $answer_value == $single_answer_option && 1 !== $single_answer[2] ) {
            $question_with_answer_text .= '<span class="qsm-text-wrong-option ' . $image_class . '">' . $show_user_answer . '</span>';
        } else {
            $question_with_answer_text .= '<span class="qsm-text-simple-option ' . $image_class . '">' . $show_user_answer . '</span>';
        }
    }
    return $question_with_answer_text;
}

function qsm_bckcmp_tempvar_qa_text_qt_multi_choice_points( $answers_from_db, $answers_from_response, $question_settings ) {
    $question_with_answer_text = '';
    $user_selected_answer = htmlspecialchars_decode($answers_from_response[1], ENT_QUOTES);
    foreach ( $answers_from_db as $single_answer ) {
        $image_class = '';
        if ( isset($question_settings['answerEditor']) && 'image' == $question_settings['answerEditor'] ) {
            $show_user_answer = '<img src="' . htmlspecialchars_decode($single_answer[0], ENT_QUOTES) . '"/>';
            $image_class      = 'qmn_image_option';
        } else {
            $show_user_answer = htmlspecialchars_decode($single_answer[0], ENT_QUOTES);
            $image_class      = '';
        }
        if ( strpos($user_selected_answer, $single_answer[0]) !== false ) {
            $question_with_answer_text .= '<span class="qsm-text-correct-option ' . $image_class . '">' . $show_user_answer . '</span>';
        } else {
            $question_with_answer_text .= '<span class="qsm-text-simple-option ' . $image_class . '">' . $show_user_answer . '</span>';
        }
    }
    return $question_with_answer_text;
}

function qsm_bckcmp_tempvar_qa_text_qt_single_choice_points( $answers_from_db, $answers_from_response, $question_settings ) {
    $question_with_answer_text = '';
    foreach ( $answers_from_db as $single_answer ) {
        $image_class = '';
        if ( isset($question_settings['answerEditor']) && 'image' == $question_settings['answerEditor'] ) {
            $show_user_answer = '<img src="' . htmlspecialchars_decode($single_answer[0], ENT_QUOTES) . '"/>';
            $image_class      = 'qmn_image_option';
        } else {
            $show_user_answer = htmlspecialchars_decode($single_answer[0], ENT_QUOTES);
            $image_class      = '';
        }
        if ( htmlspecialchars_decode($answers_from_response[1], ENT_QUOTES) == $single_answer[0] ) {
            $question_with_answer_text .= '<span class="qsm-text-correct-option ' . $image_class . '">' . $show_user_answer . '</span>';
        } else {
            $question_with_answer_text .= '<span class="qsm-text-simple-option ' . $image_class . '">' . $show_user_answer . '</span>';
        }
    }
    return $question_with_answer_text;
}
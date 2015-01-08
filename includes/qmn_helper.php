<?php
class QMNPluginHelper
{
	public $addon_tabs = array();
	public $settings_tabs = array();
	public $question_types = array();

	public function __construct()
	{
		add_action('mlw_qmn_options_tab', array($this, 'get_settings_tabs'));
		add_action('mlw_qmn_options_tab_content', array($this, 'get_settings_tabs_content'));
	}

	public function register_question_type($name, $display_function, $graded, $review_function = null, $slug = null)
	{
		if (is_null($slug))
		{
			$slug = strtolower(str_replace( " ", "-", $name));
		}
		else
		{
			$slug = strtolower(str_replace( " ", "-", $slug));
		}
		$new_type = array(
			'name' => $name,
			'display' => $display_function,
			'review' => $review_function,
			'graded' => $graded,
			'slug' => $slug
		);
		$this->question_types[] = $new_type;
	}

	public function get_question_type_options()
	{
		$type_array = array();
		foreach($this->question_types as $type)
		{
			$type_array[] = array(
				'slug' => $type["slug"],
				'name' => $type["name"]
			);
		}
		return $type_array;
	}

	public function display_question($slug, $question_id, $quiz_options)
	{
		$display = '';
		global $wpdb;
		global $qmn_total_questions;
		$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."mlw_questions WHERE question_id=%d", intval($question_id)));
		$answers = array();
		if (is_serialized($question->answer_array) && is_array(@unserialize($question->answer_array)))
		{
			$answers = @unserialize($question->answer_array);
		}
		else
		{
			$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
			$mlw_answer_array_correct[$question->correct_answer-1] = 1;
			$answers = array(
				array($question->answer_one, $question->answer_one_points, $mlw_answer_array_correct[0]),
				array($question->answer_two, $question->answer_two_points, $mlw_answer_array_correct[1]),
				array($question->answer_three, $question->answer_three_points, $mlw_answer_array_correct[2]),
				array($question->answer_four, $question->answer_four_points, $mlw_answer_array_correct[3]),
				array($question->answer_five, $question->answer_five_points, $mlw_answer_array_correct[4]),
				array($question->answer_six, $question->answer_six_points, $mlw_answer_array_correct[5]));
		}
		if ($quiz_options->randomness_order == 2)
		{
			shuffle($answers);
		}
		foreach($this->question_types as $type)
		{
			if ($type["slug"] == strtolower(str_replace( " ", "-", $slug)))
			{
				if ($type["graded"])
				{
					$qmn_total_questions += 1;
				}
				$display .= call_user_func($type['display'], intval($question_id), $question->question_name, $answers);
			}
		}
		$return $display;
	}

	public function display_review($slug, $question_id)
	{
		$results_array = array();
		global $wpdb;
		$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."mlw_questions WHERE question_id=%d", intval($question_id)));
		$answers = array();
		if (is_serialized($question->answer_array) && is_array(@unserialize($question->answer_array)))
		{
			$answers = @unserialize($question->answer_array);
		}
		else
		{
			$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
			$mlw_answer_array_correct[$question->correct_answer-1] = 1;
			$answers = array(
				array($question->answer_one, $question->answer_one_points, $mlw_answer_array_correct[0]),
				array($question->answer_two, $question->answer_two_points, $mlw_answer_array_correct[1]),
				array($question->answer_three, $question->answer_three_points, $mlw_answer_array_correct[2]),
				array($question->answer_four, $question->answer_four_points, $mlw_answer_array_correct[3]),
				array($question->answer_five, $question->answer_five_points, $mlw_answer_array_correct[4]),
				array($question->answer_six, $question->answer_six_points, $mlw_answer_array_correct[5]));
		}
		foreach($this->question_types as $type)
		{
			if ($type["slug"] == strtolower(str_replace( " ", "-", $slug)) && !is_null($type["review"]))
			{
				$results_array = call_user_func($type['review'], intval($question_id), $question->question_name, $answers);
			}
		}
		return $results_array;
	}

	public function get_question_setting($question_id, $setting)
	{
		global $wpdb;
		$qmn_settings_array = '';
		$settings = $wpdb->get_var( $wpdb->prepare( "SELECT question_settings FROM " . $wpdb->prefix . "mlw_questions WHERE question_id=%d", $question_id ) );
		if (is_serialized($settings) && is_array(@unserialize($settings)))
		{
			$qmn_settings_array = @unserialize($settings);
		}
		if (is_array($qmn_settings_array) && isset($qmn_settings_array[$setting]))
		{
			return $qmn_settings_array[$setting];
		}
		else
		{
			return '';
		}
	}

	public function register_addon_settings_tab($title, $function)
	{
		$slug = strtolower(str_replace( " ", "-", $title));
		$new_tab = array(
			'title' => $title,
			'function' => $function,
			'slug' => $slug
		);
		$this->addon_tabs[] = $new_tab;
	}

	public function get_addon_tabs()
	{
		return $this->addon_tabs;
	}

	public function register_quiz_settings_tabs($title, $function)
	{
		$slug = strtolower(str_replace( " ", "-", $title));
		$new_tab = array(
			'title' => $title,
			'function' => $function,
			'slug' => $slug
		);
		$this->settings_tabs[] = $new_tab;
	}

	public function get_settings_tabs()
	{
		foreach($this->settings_tabs as $tab)
		{
			echo "<li><a href=\"#".$tab["slug"]."\">".$tab["title"]."</a></li>";
		}
	}

	public function get_settings_tabs_content()
	{
		foreach($this->settings_tabs as $tab)
		{
			echo "<div id=\"".$tab["slug"]."\" class=\"mlw_tab_content\">";
			call_user_func($tab['function']);
			echo "</div>";
		}
	}
}
?>

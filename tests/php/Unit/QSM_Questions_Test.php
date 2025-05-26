<?php

use PHPUnit\Framework\TestCase;

// If WP_UnitTestCase is not autoloaded, we might need to require a bootstrap file.
// For now, let's assume it's available or use PHPUnit's TestCase as a fallback for structure.
if ( ! class_exists( 'WP_UnitTestCase' ) && class_exists( 'PHPUnit\Framework\TestCase' ) ) {
	class WP_UnitTestCase extends \PHPUnit\Framework\TestCase {}
} elseif ( ! class_exists( 'WP_UnitTestCase' ) ) {
	// Fallback if no TestCase class is found - this will likely fail but helps structure
	class WP_UnitTestCase {
		public function assertTrue( $condition, $message = '' ) {}
		public function assertCount( $expectedCount, $haystack, $message = '' ) {}
		public function assertEquals( $expected, $actual, $message = '' ) {}
		// Add other assertions as needed for planning
	}
}

// Ensure the class we are testing is loaded.
// The path will need to be relative to the WordPress root or handled by an autoloader.
// For now, assuming it might be run from a context where this relative path works,
// or it's handled by a bootstrap.
if ( ! class_exists( 'QSM_Questions' ) ) {
	// Adjust path as necessary if tests are run from a specific directory
	$qsm_questions_path = __DIR__ . '/../../../../php/classes/class-qsm-questions.php';
	if ( file_exists( $qsm_questions_path ) ) {
		require_once $qsm_questions_path;
	} else {
		// Potentially die or throw an error if the class can't be loaded
		// This indicates a setup problem for the test environment.
	}
}


class QSM_Questions_Test extends WP_UnitTestCase {

	// Helper function to count correct/incorrect answers in a list
	private function count_correct_incorrect( $answers_array ) {
		$correct_count = 0;
		$incorrect_count = 0;
		if ( ! is_array( $answers_array ) ) {
			return array( 'correct' => 0, 'incorrect' => 0 );
		}
		foreach ( $answers_array as $answer ) {
			if ( isset( $answer[2] ) && 1 == $answer[2] ) {
				$correct_count++;
			} else {
				$incorrect_count++;
			}
		}
		return array( 'correct' => $correct_count, 'incorrect' => $incorrect_count );
	}

	// Helper function to generate sample answers
	private function generate_answers( $num_correct, $num_incorrect ) {
		$answers = array();
		$key_counter = 0;
		for ( $i = 0; $i < $num_correct; $i++ ) {
			$answers[ 'c' . $key_counter++ ] = array( 'Correct ' . $i, 1, 1 );
		}
		for ( $i = 0; $i < $num_incorrect; $i++ ) {
			$answers[ 'i' . $key_counter++ ] = array( 'Incorrect ' . $i, 0, 0 );
		}
		return $answers;
	}

	/**
	 * Test Scenario 1: Limit 0 (No Limit)
	 * L = 0. Expected: All N_total answers are present.
	 */
	public function test_scenario1_limit_zero() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 3, 2 ); // 3 correct, 2 incorrect
		$display_limit = 0;
		
		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );
		
		$this->assertCount( 5, $result, "Scenario 1: Should return all 5 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 3, $counts['correct'], "Scenario 1: Should have 3 correct answers." );
		$this->assertEquals( 2, $counts['incorrect'], "Scenario 1: Should have 2 incorrect answers." );
	}

	/**
	 * Test Scenario 2: Limit Applied, Correct < Limit
	 * N_correct = 1, N_incorrect = 9 (Total 10). L = 4.
	 * Expected: 1 correct answer, 3 randomly selected incorrect answers. Total 4.
	 */
	public function test_scenario2_limit_applied_correct_less_than_limit() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 1, 9 );
		$display_limit = 4;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 4, $result, "Scenario 2: Should return 4 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 1, $counts['correct'], "Scenario 2: Should have 1 correct answer." );
		$this->assertEquals( 3, $counts['incorrect'], "Scenario 2: Should have 3 incorrect answers." );
	}

	/**
	 * Test Scenario 3: Limit Applied, Correct < Limit, Not Enough Incorrect
	 * N_correct = 1, N_incorrect = 2 (Total 3). L = 4.
	 * Expected: 1 correct answer, 2 incorrect answers. Total 3 (all available answers).
	 */
	public function test_scenario3_limit_applied_not_enough_incorrect() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 1, 2 );
		$display_limit = 4;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 3, $result, "Scenario 3: Should return 3 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 1, $counts['correct'], "Scenario 3: Should have 1 correct answer." );
		$this->assertEquals( 2, $counts['incorrect'], "Scenario 3: Should have 2 incorrect answers." );
	}

	/**
	 * Test Scenario 4: Limit Applied, Correct == Limit
	 * N_correct = 4, N_incorrect = 6 (Total 10). L = 4.
	 * Expected: 4 correct answers, 0 incorrect answers. Total 4.
	 */
	public function test_scenario4_limit_applied_correct_equals_limit() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 4, 6 );
		$display_limit = 4;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 4, $result, "Scenario 4: Should return 4 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 4, $counts['correct'], "Scenario 4: Should have 4 correct answers." );
		$this->assertEquals( 0, $counts['incorrect'], "Scenario 4: Should have 0 incorrect answers." );
	}

	/**
	 * Test Scenario 5: Limit Applied, Correct > Limit (All Correct Shown)
	 * N_correct = 5, N_incorrect = 5 (Total 10). L = 4.
	 * Expected: All 5 correct answers are shown. Total 5.
	 */
	public function test_scenario5_limit_applied_correct_greater_than_limit() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 5, 5 );
		$display_limit = 4;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 5, $result, "Scenario 5: Should return 5 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 5, $counts['correct'], "Scenario 5: Should have 5 correct answers." );
		$this->assertEquals( 0, $counts['incorrect'], "Scenario 5: Should have 0 incorrect answers." );
	}

	/**
	 * Test Scenario 6: Limit Greater Than Total Answers
	 * N_correct = 2, N_incorrect = 3 (Total 5). L = 10.
	 * Expected: All 5 answers are shown.
	 */
	public function test_scenario6_limit_greater_than_total() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 2, 3 );
		$display_limit = 10;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 5, $result, "Scenario 6: Should return 5 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 2, $counts['correct'], "Scenario 6: Should have 2 correct answers." );
		$this->assertEquals( 3, $counts['incorrect'], "Scenario 6: Should have 3 incorrect answers." );
	}
	
	/**
	 * Test Scenario 7: Only Correct Answers
	 * N_correct = 3, N_incorrect = 0 (Total 3). L = 2.
	 * Expected: All 3 correct answers are shown.
	 */
	public function test_scenario7_only_correct_answers_limit_applied() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 3, 0 );
		$display_limit = 2;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 3, $result, "Scenario 7: Should return 3 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 3, $counts['correct'], "Scenario 7: Should have 3 correct answers." );
		$this->assertEquals( 0, $counts['incorrect'], "Scenario 7: Should have 0 incorrect answers." );
	}

	/**
	 * Test Scenario 8: Only Incorrect Answers
	 * N_correct = 0, N_incorrect = 5 (Total 5). L = 3.
	 * Expected: 3 randomly selected incorrect answers.
	 */
	public function test_scenario8_only_incorrect_answers_limit_applied() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 0, 5 );
		$display_limit = 3;

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );

		$this->assertCount( 3, $result, "Scenario 8: Should return 3 answers." );
		$counts = $this->count_correct_incorrect( $result );
		$this->assertEquals( 0, $counts['correct'], "Scenario 8: Should have 0 correct answers." );
		$this->assertEquals( 3, $counts['incorrect'], "Scenario 8: Should have 3 incorrect answers." );
	}

	/**
	 * Test that original keys are preserved in the output.
	 */
	public function test_original_keys_are_preserved() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = array(
			'ans_key_1' => array( 'Correct 1', 1, 1 ),
			'ans_key_2' => array( 'Incorrect 1', 0, 0 ),
			'ans_key_3' => array( 'Correct 2', 1, 1 ),
			'ans_key_4' => array( 'Incorrect 2', 0, 0 ),
			'ans_key_5' => array( 'Incorrect 3', 0, 0 ),
		);
		$display_limit = 3; // Expect 2 correct, 1 incorrect

		$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );
		$this->assertCount( 3, $result );

		$original_keys = array_keys( $all_answers );
		$result_keys = array_keys( $result );

		foreach ( $result_keys as $key ) {
			$this->assertContains( $key, $original_keys, "Result key '$key' should be one of the original keys." );
		}
		
		// Specifically check that the correct answers retain their keys
		$this->assertArrayHasKey( 'ans_key_1', $result, "Correct answer with key 'ans_key_1' should be in result." );
		$this->assertArrayHasKey( 'ans_key_3', $result, "Correct answer with key 'ans_key_3' should be in result." );

		// Check that one of the incorrect keys is present
		$incorrect_result_keys = array_filter($result_keys, function($k) { return strpos($k, 'ans_key_') === 0 && ($k === 'ans_key_2' || $k === 'ans_key_4' || $k === 'ans_key_5'); });
        $this->assertCount(1, array_intersect(['ans_key_2', 'ans_key_4', 'ans_key_5'], $result_keys), "Exactly one incorrect answer key should be present.");

	}

	/**
	 * Test shuffling: output order should not be fixed for incorrect answers when limited.
	 * This is a basic check, true randomness is hard to test.
	 * We run it a few times and expect the order of incorrect answers to vary.
	 */
	public function test_shuffling_of_incorrect_answers() {
		if ( ! class_exists( 'QSM_Questions' ) ) {
			$this->markTestSkipped('QSM_Questions class not loaded.');
		}
		$all_answers = $this->generate_answers( 1, 10 ); // 1 correct, 10 incorrect
		$display_limit = 5; // 1 correct, 4 incorrect

		$results_incorrect_orders = array();
		for ( $i = 0; $i < 5; $i++ ) { // Run multiple times
			$result = QSM_Questions::get_answers_for_display( $all_answers, $display_limit );
			$current_incorrect_order = array();
			foreach ( $result as $key => $answer ) {
				if ( $answer[2] == 0 ) { // Incorrect answer
					$current_incorrect_order[] = $key;
				}
			}
			$results_incorrect_orders[] = implode( ',', $current_incorrect_order );
		}
		// Check if all runs produced the exact same order of incorrect answers.
		// If they are all the same, shuffling might not be working as expected for the incorrect part.
		$this->assertFalse( count( array_unique( $results_incorrect_orders ) ) === 1 && count($results_incorrect_orders) > 1, "Shuffling of incorrect answers should lead to varied orders over multiple runs." );
	}
}

?>

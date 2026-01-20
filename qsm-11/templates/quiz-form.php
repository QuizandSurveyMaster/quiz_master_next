<?php
/**
 * Template for quiz form (New System)
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/quiz-form.php
 *
 * Available variables:
 * @var int $quiz_id Quiz ID
 * @var object $options Quiz options
 * @var array $quiz_data Quiz data
 * @var QSM_New_Pagination_Renderer $renderer Renderer instance
 * @var array $args All template arguments
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo apply_filters( 'qsm_display_before_form', '', $options, $quiz_data );
// Get form action URL
$form_action = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
?>
<form 
    name="quizForm<?php echo esc_attr( $quiz_id ); ?>" 
    id="quizForm<?php echo esc_attr( $quiz_id ); ?>" 
    action="<?php echo esc_url( $form_action ); ?>" 
    method="POST" 
    class="<?php echo esc_attr( apply_filters( 'qsm_quiz_form_class_after', 'qsm-quiz-form qmn_quiz_form mlw_quiz_form', $quiz_id, $options ) ); ?>" 
    novalidate 
    enctype="multipart/form-data"
    data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>"
    <?php echo apply_filters( 'qsm_quiz_form_attributes', '', $quiz_id, $options ); ?>
>
    <?php
    /**
     * Hook: qsm_quiz_form_content
     * 
     * This hook renders all elements inside the quiz form.
     * Elements are registered with priorities to control order.
     * 
     * @param int $quiz_id Quiz ID
     * @param QSM_New_Pagination_Renderer $renderer Renderer instance
     * @param array $args Template arguments
     * 
     * @since 9.0
     */
    echo apply_filters( 'qmn_begin_quiz_form', '', $options, $quiz_data );
    do_action( 'qsm_quiz_form_content', $quiz_id, $renderer, $args );
    
    /**
     * Hook: qsm_before_end_quiz_form
     * 
     * This hook is triggered before the end of the quiz form.
     * 
     * @param object $options Quiz options
     * @param array $quiz_data Quiz data
     * 
     * @since 9.0
     */
    echo apply_filters( 'qmn_end_quiz_form', '', $options, $quiz_data );

	do_action( 'qsm_before_end_quiz_form', $options, $quiz_data, array() );
    ?>
</form>
<?php
	do_action( 'qsm_after_end_quiz_form', $options, $quiz_data, array() );

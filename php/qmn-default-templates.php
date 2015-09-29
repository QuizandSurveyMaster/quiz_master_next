<?php
function qmn_register_default_templates() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_template( 'Primary', 'qmn_primary.css');
	$mlwQuizMasterNext->pluginHelper->register_quiz_template( 'Amethyst', 'qmn_amethyst.css');
	$mlwQuizMasterNext->pluginHelper->register_quiz_template( 'Emerald', 'qmn_emerald.css');
	$mlwQuizMasterNext->pluginHelper->register_quiz_template( 'Turquoise', 'qmn_turquoise.css');
	$mlwQuizMasterNext->pluginHelper->register_quiz_template( 'Gray', 'qmn_gray.css');
}
add_action( 'plugins_loaded', 'qmn_register_default_templates' );
?>

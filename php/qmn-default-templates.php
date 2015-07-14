<?php
function qmn_register_default_templates() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_template( 'primary', 'qmn_primary.css');
}
add_action( 'plugins_loaded', 'qmn_register_default_templates' );
?>

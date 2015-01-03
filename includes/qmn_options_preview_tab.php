<?php
function mlw_options_preview_tab()
{
	echo "<li><a href=\"#tabs-preview\">Preview (Beta)</a></li>";
}

function mlw_options_preview_tab_content()
{
	?>
	<div id="tabs-preview" class="mlw_tab_content">
		<?php
		echo do_shortcode( '[mlw_quizmaster quiz='.intval($_GET["quiz_id"]).']' );
		?>
	</div>
	<?php
}
add_action('mlw_qmn_options_tab', 'mlw_options_preview_tab');
add_action('mlw_qmn_options_tab_content', 'mlw_options_preview_tab_content');
?>

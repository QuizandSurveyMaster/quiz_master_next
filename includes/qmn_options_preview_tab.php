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
?>

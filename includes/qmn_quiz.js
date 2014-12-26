setTimeout(function(){
	var $j = jQuery.noConflict();
	// increase the default animation speed to exaggerate the effect
	$j.fx.speeds._default = 1000;
	$j(function() {
		$j( ".mlw_qmn_quiz" ).tooltip();
	});
}, 100);
setTimeout(function()
{
	var $j = jQuery.noConflict();
	$j('.mlw_qmn_quiz input').on('keypress', function (e) {
		if (e.which === 13) {
			e.preventDefault();
		}
	});
}, 100);

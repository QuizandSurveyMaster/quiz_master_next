setTimeout(function(){
	var $j = jQuery.noConflict();
	$j( ".quiz_section" ).hide();
	$j( ".quiz_section" ).append( "<br />" );
	$j( ".mlw_qmn_quiz" ).append( "<a class=\"mlw_qmn_quiz_link mlw_previous\" href=\"javascript:prevSlide("+qmn_pagination+", 1);\">"+qmn_pagination_previous_text+"</a>" );
	$j( ".mlw_qmn_quiz" ).append( "<a class=\"mlw_qmn_quiz_link mlw_next\" href=\"javascript:nextSlide("+qmn_pagination+", 1);\">"+qmn_pagination_next_text+"</a>" );
	window.mlw_quiz_slide = 0;
	window.mlw_previous = 0;
	window.mlw_quiz_total_slides = qmn_section_limit;
	nextSlide(1, 0);
}, 100);
function nextSlide(mlw_pagination, mlw_goto_top)
{
	jQuery( ".quiz_section" ).hide();
	for (var i = 0; i < mlw_pagination; i++) 
	{
		if (i == 0 && window.mlw_previous == 1 && window.mlw_quiz_slide > 1)
		{
			window.mlw_quiz_slide = window.mlw_quiz_slide + mlw_pagination;
		}
		else
		{
			window.mlw_quiz_slide++;
		}
		if (window.mlw_quiz_slide < 1)
		{
			window.mlw_quiz_slide = 1;
		}
		if (window.mlw_quiz_slide == 1)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).hide();
		}
		if (window.mlw_quiz_slide > 1)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).show();
		}
		if (window.mlw_quiz_slide == window.mlw_quiz_total_slides)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_next" ).hide();
		} 
		if (window.mlw_quiz_slide < window.mlw_quiz_total_slides)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_next" ).show();
		} 
		jQuery( ".quiz_section.slide"+window.mlw_quiz_slide ).show();
	}
	window.mlw_previous = 0;
	if (mlw_goto_top == 1)
	{
		window.location.hash = "mlw_does_not_exist";
		window.location.hash = "mlw_top_of_quiz";
	}
}
function prevSlide(mlw_pagination, mlw_goto_top)
{
	jQuery( ".quiz_section" ).hide();
	for (var i = 0; i < mlw_pagination; i++) 
	{ 
		if (i == 0 && window.mlw_previous == 0)
		{
			window.mlw_quiz_slide = window.mlw_quiz_slide - mlw_pagination;
		}
		else
		{
			window.mlw_quiz_slide--;
		}
		if (window.mlw_quiz_slide < 1)
		{
			window.mlw_quiz_slide = 1;
		}
		if (window.mlw_quiz_slide == 1)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).hide();
		}
		if (window.mlw_quiz_slide > 1)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_previous" ).show();
		}
		if (window.mlw_quiz_slide == window.mlw_quiz_total_slides)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_next" ).hide();
		} 
		if (window.mlw_quiz_slide < window.mlw_quiz_total_slides)
		{
			jQuery( ".mlw_qmn_quiz_link.mlw_next" ).show();
		}
		jQuery( ".quiz_section.slide"+window.mlw_quiz_slide ).show();	
	}
	window.mlw_previous = 1;
	if (mlw_goto_top == 1)
	{
		window.location.hash = "mlw_does_not_exist";
		window.location.hash = "mlw_top_of_quiz";
	}
}

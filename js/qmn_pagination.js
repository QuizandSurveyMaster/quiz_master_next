function qmnValidatePage() {
	var validated = true;
	jQuery(".quiz_section.slide"+window.mlw_quiz_slide+' *').each(function(){
		jQuery(this).css("outline", "");
		if (jQuery(this).attr('class'))
		{
			if(jQuery(this).attr('class').indexOf('mlwEmail') > -1 && this.value != "")
			{
				var x=this.value;
				var atpos=x.indexOf('@');
				var dotpos=x.lastIndexOf('.');
				if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
				{
					jQuery('.qmn_page_error_message').text('**'+email_error+'**');
					validated =  false;
					jQuery(this).css("outline", "2px solid red");
				}
			}
			if(jQuery(this).attr('class').indexOf('mlwRequiredNumber') > -1 && this.value == "" && +this.value != NaN)
			{
				jQuery('.qmn_page_error_message').text('**'+number_error+'**');
				jQuery(this).css("outline", "2px solid red");
				validated =  false;
			}
			if(jQuery(this).attr('class').indexOf('mlwRequiredText') > -1 && this.value == "")
			{
				jQuery('.qmn_page_error_message').text('**'+empty_error+'**');
				jQuery(this).css("outline", "2px solid red");
				validated =  false;
			}
			if(jQuery(this).attr('class').indexOf('mlwRequiredCaptcha') > -1 && this.value != mlw_code)
			{
				jQuery('.qmn_page_error_message').text('**'+incorrect_error+'**');
				jQuery(this).css("outline", "2px solid red");
				validated =  false;
			}
			if(jQuery(this).attr('class').indexOf('mlwRequiredAccept') > -1 && !this.checked)
			{
				jQuery('.qmn_page_error_message').text('**'+empty_error+'**');
				jQuery(this).css("outline", "2px solid red");
				validated =  false;
			}
			if(jQuery(this).attr('class').indexOf('mlwRequiredRadio') > -1)
			{
        check_val = jQuery(this).find('input:checked').val();
        if (check_val == "No Answer Provided")
				{
					jQuery('.qmn_page_error_message').text('**'+empty_error+'**');
					jQuery(this).css("outline", "2px solid red");
					validated =  false;
				}
			}
			if(jQuery(this).attr('class').indexOf('mlwRequiredCheck') > -1)
			{
				if (!jQuery(this).find('input:checked').length)
				{
					jQuery('.qmn_page_error_message').text('**'+empty_error+'**');
					jQuery(this).css("outline", "2px solid red");
					validated =  false;
				}
			}
		}
	});
	if (validated) {
		jQuery('.qmn_page_error_message').text(' ');
	}
	return validated;
}
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

jQuery( ".quiz_section" ).hide();
jQuery( ".quiz_section" ).append( "<br />" );
jQuery( ".mlw_qmn_quiz" ).append( '<a class="mlw_qmn_quiz_link mlw_previous" href="#">'+qmn_pagination_previous_text+'</a>' );
jQuery( ".mlw_qmn_quiz" ).append( '<span class="qmn_page_message"></span>' );
jQuery( ".mlw_qmn_quiz" ).append( '<span class="qmn_page_error_message"></span>' );
jQuery( ".mlw_qmn_quiz" ).append( '<a class="mlw_qmn_quiz_link mlw_next" href="#">'+qmn_pagination_next_text+'</a>' );
window.mlw_quiz_slide = 0;
window.mlw_previous = 0;
window.mlw_quiz_total_slides = qmn_section_limit;
nextSlide(1, 0);

jQuery(".mlw_next").click(function(event) {
	event.preventDefault();
	if ( qmnValidatePage() ) {
		nextSlide(qmn_pagination, 0);
	}
});

jQuery(".mlw_previous").click(function(event) {
	event.preventDefault();
	prevSlide(qmn_pagination, 0);
});

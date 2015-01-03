setTimeout(function(){
	var minutes = 0;
	if (window.sessionStorage.getItem('mlw_started_quiz'+qmn_quiz_id) == "yes" && window.sessionStorage.getItem('mlw_time_quiz'+qmn_quiz_id) >= 0)
	{
		minutes = window.sessionStorage.getItem('mlw_time_quiz'+qmn_quiz_id);
	}
	else
	{
		minutes = qmn_timer_limit;
	}
	window.amount = (minutes*60);
	window.titleText = window.document.title;
	document.getElementById("mlw_qmn_timer").innerHTML = minToSec(window.amount);
	window.counter=setInterval(timer, 1000);
}, 100);
function timer()
{
	window.amount=window.amount-1;
	if (window.amount < 0)
	{
		window.amount = 0;	
	}
	window.sessionStorage.setItem('mlw_time_quiz'+qmn_quiz_id, window.amount/60);
	window.sessionStorage.setItem('mlw_started_quiz'+qmn_quiz_id, "yes");
	document.getElementById("mlw_qmn_timer").innerHTML = minToSec(window.amount);
	window.document.title = minToSec(window.amount) + " " + window.titleText;
	if (window.amount <= 0)
	{
		clearInterval(window.counter);
		jQuery( ".mlw_qmn_quiz input:radio" ).attr('disabled',true);
		jQuery( ".mlw_qmn_quiz input:checkbox" ).attr('disabled',true);
		jQuery( ".mlw_qmn_quiz select" ).attr('disabled',true);
		jQuery( ".mlw_qmn_question_comment" ).attr('disabled',true);
		jQuery( ".mlw_answer_open_text" ).attr('disabled',true);
		//document.quizForm.submit();
		return;
	}
}
function minToSec(amount)
{
	var timer_display = '';
	var hours = Math.floor(amount/3600);
	if (hours == '0')
	{
		timer_display = timer_display +"00:";
	}
	else if (hours < 10)
	{
		timer_display = timer_display + '0' + hours + ":";
	}
	else
	{
		timer_display = timer_display + hours + ":";
	}
	var minutes = Math.floor((amount % 3600)/60);
	if (minutes == '0')
	{
		timer_display = timer_display +"00:";
	}
	else if (minutes < 10)
	{
		timer_display = timer_display + '0' + minutes + ":";
	}
	else
	{
		timer_display = timer_display + minutes + ":";
	}
	var seconds = Math.floor(amount % 60);
	if (seconds == '0') 
	{ 
		timer_display = timer_display +"00";
	}
	else if (seconds < 10)
	{
		timer_display = timer_display +'0' + seconds;
	}
	else
	{
		timer_display = timer_display + seconds;
	}
	return timer_display;
}

function mlw_qmn_share(network, mlw_qmn_social_text, mlw_qmn_title)
{
	var sTop = window.screen.height/2-(218);
	var sLeft = window.screen.width/2-(313);
	var sqShareOptions = "height=400,width=580,toolbar=0,status=0,location=0,menubar=0,directories=0,scrollbars=0,top=" + sTop + ",left=" + sLeft;
	var pageUrl = window.location.href;
	var pageUrlEncoded = encodeURIComponent(pageUrl);
	if (network == 'facebook')
	{
		var Url = "https://www.facebook.com/dialog/feed?"
			+ "display=popup&"
			+ "app_id=483815031724529&"
			+ "link=" + pageUrlEncoded + "&"
			+ "name=" + encodeURIComponent(mlw_qmn_social_text) + "&"
			+ "description=  &"
			+ "redirect_uri=http://www.mylocalwebstop.com/mlw_qmn_close.html";
	}
	if (network == 'twitter')
	{
		var Url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(mlw_qmn_social_text);
	}
	window.open(Url, "Share", sqShareOptions);
	return false;
}

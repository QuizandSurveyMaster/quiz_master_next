<?php
class MlwQmnAlertManager {
	
	public $alerts = array();
	
	public function newAlert($message, $type)
	{
		$this->alerts[] = array( 'message' => $message, 'type' => $type );
	}
	
	public function showAlerts()
	{
		$alert_list = "";
		foreach ($this->alerts as $alert)
		{
			if ($alert['type'] == "success")
			{
				$alert_list .= "<div id=\"message\" class=\"updated below-h2\"><p><strong>Success! </strong>".$alert["message"]."</p></div>";
			}
			if ($alert['type'] == "error")
			{
				$alert_list .= "<div id=\"message\" class=\"error below-h2\"><p><strong>Error! </strong>".$alert["message"]."</p></div>";
			}
		}
		echo $alert_list;
	}
	
}
?>

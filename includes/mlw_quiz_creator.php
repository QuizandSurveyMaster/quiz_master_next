<?php
/**
 * QMN Quiz Creator Class
 *
 * This class handles quiz creation, update, and deletion from the admin panel
 *
 * The Quiz Creator class handles all the quiz management functions that is done from the admin panel
 *
 * @since 3.7.1
 */
class QMNQuizCreator
{
	/**
	 * QMN ID of quiz
	 *
	 * @var object
	 * @since 3.7.1
	 */
	private $quiz_id;
	
	/**
	 * If the quiz ID is set, store it as the class quiz ID
	 *
	 * @since 3.7.1
	 */
	public function __construct()
	{
		if (isset($_GET["quiz_id"]))
		{
			$this->quiz_id = intval($_GET["quiz_id"]);
		}
	}
}
?>

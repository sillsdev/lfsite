<?php

namespace models;

use models\mapper\Id;
use models\mapper\ArrayOf;

require_once(APPPATH . '/models/ProjectModel.php');

class CommentModelMongoMapper extends \models\mapper\MongoMapper
{
	/**
	 * @var CommentModelMongoMapper[]
	 */
	private static $_pool = array();
	
	/**
	 * @param string $databaseName
	 * @return CommentModelMongoMapper
	 */
	public static function connect($databaseName) {
		if (!isset(static::$_pool[$databaseName])) {
			static::$_pool[$databaseName] = new CommentModelMongoMapper($databaseName, 'questions');
		}
		return static::$_pool[$databaseName];
	}
	
}

class CommentModel extends \models\mapper\MapperModel
{
	public function __construct($projectModel, $id = '') {
		$this->id = new Id();
		parent::__construct(CommentModelMongoMapper::connect($projectModel->databaseName()), $id);
	}
	
	public static function remove($databaseName, $id) {
		CommentModelMongoMapper::connect($databaseName)->remove($id);
	}

	/**
	 * @var string id
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $comment;
	
	/**
	 * @var Reference
	 */
	public $authorUserRef;
	
	//public $authorDate; // TODO CP 2013-07
			
}

class QuestionModel extends CommentModel
{
	/**
	 * @var ProjectModel
	 */
	private $_projectModel;

	/**
	 * @param ProjectModel $projectModel
	 * @param Id $id
	 */
	public function __construct($projectModel, $id = '') {
		$this->_projectModel = $projectModel;
		$this->answers = new ArrayOf(ArrayOf::OBJECT, 'generateAnswer');
		parent::__construct($projectModel, $id);
	}
	
	public function generateAnswer($data = null) {
		return new AnswerModel($this->_projectModel);
	}
	
	/**
	 * @var ArrayOf<AnswerModel>
	 */
	public $answers;
	
}

class AnswerModel extends CommentModel
{
	public function __construct($projectModel, $id = NULL) {
		parent::__construct($projectModel, $id);
		$this->comments = array();
	}
	
	/**
	 * @var array<CommentModel>
	 */
	public $comments;
}

class QuestionListModel extends \models\mapper\MapperListModel
{

	public function __construct($projectModel/*, $textId*/)
	{
		// TODO Include $textId in the query CP 2013-07
		parent::__construct(
			CommentModelMongoMapper::connect($projectModel->databaseName()),
			array('comment' => array('$regex' => '')),
			array('comment')
		);
	}
	
}

?>
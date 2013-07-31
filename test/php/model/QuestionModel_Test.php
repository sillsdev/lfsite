<?php

use models\QuestionListModel;

use models\mapper\MongoStore;
use models\ProjectModel;
use models\QuestionModel;

require_once(dirname(__FILE__) . '/../TestConfig.php');
require_once(SimpleTestPath . 'autorun.php');

require_once(TestPath . 'common/MongoTestEnvironment.php');

require_once(SourcePath . "models/ProjectModel.php");
require_once(SourcePath . "models/QuestionModel.php");


class TestQuestionModel extends UnitTestCase {

	private $_someQuestionId;

	function __construct() {
		$e = new MongoTestEnvironment();
		$e->clean();
	}

	function testWrite_ReadBackSame() {
		$model = new QuestionModel(new MockProjectModel());
		$model->title = "SomeQuestion";
		$id = $model->write();
		$this->assertNotNull($id);
		$this->assertIsA($id, 'string');
		$this->assertEqual($id, $model->id->asString());
		$otherModel = new QuestionModel(new MockProjectModel(), $id);
		$this->assertEqual($id, $otherModel->id->asString());
		$this->assertEqual('SomeQuestion', $otherModel->title);

		$this->_someQuestionId = $id;
	}

	function testProjectList_HasCountAndEntries() {
		$model = new QuestionListModel(new MockProjectModel());
		$model->read();

		$this->assertNotEqual(0, $model->count);
		$this->assertNotNull($model->entries);
	}

}

?>

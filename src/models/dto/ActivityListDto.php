<?php

namespace models\dto;

require_once(APPPATH . 'models/ActivityModel.php');


use models\ProjectList_UserModel;

use models\ActivityListModel;

use models\ProjectListModel;

use models\UserModel;

use models\ProjectModel;


class ActivityListDto
{
	/**
	 * @param string $projectModel
	 * @param string $questionId
	 * @return array - the DTO array
	 */
	public static function getActivityForProject($projectModel) {
		$activityList = new ActivityListModel($projectModel);
		$activityList->read();
		
		function encodeUser($userIdRef) {
			$user = new UserModel($userIdRef->{'$id'});
			return array(
					'id' => $user->id->asString(),
					'username' => $user->username,
					'avatar_ref' => $user->avatar_ref
			);
		}
		
		// turn userRefs into userArrays
		foreach ($activityList->entries as &$a) {
			$a['projectRef'] = ($a['projectRef']) ? $a['projectRef']->{'$id'} : '';
			$a['textRef'] = ($a['textRef']) ? $a['textRef']->{'$id'} : '';
			$a['questionRef'] = ($a['questionRef']) ? $a['questionRef']->{'$id'} : '';
			$a['date'] = ($a['date']) ? $a['date']->sec : 0;
			$a['userRef'] = ($a['userRef']) ? encodeUser($a['userRef']) : '';
			$a['userRef2'] = ($a['userRef2']) ? encodeUser($a['userRef2']) : '';
		}
		return $activityList;
	}
	
	/**
	 * @param string $userId
	 * @return array - the DTO array
	*/
	public static function getActivityForUser($userId) {
		$projectList = new ProjectList_UserModel($userId);
		$dto = array();
		foreach ($projectList->entries as $project) {
			$dto = array_merge($dto, $this::getActivityForProject($project));
		}
		function sortActivity($a, $b) {
			return ($a['date'] > $b['date']) ? 1 : -1;
		}
		uasort($dto, "sortActivity");
		return $dto;
	}
}

?>
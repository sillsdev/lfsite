'use strict';

angular.module('lexicon.settings', ['bellows.services', 'palaso.ui.listview', 'palaso.ui.typeahead', 'ui.bootstrap', 'palaso.ui.notice', 'palaso.ui.textdrop'])
.controller('SettingsCtrl', ['$scope', 'userService', 'sessionService', 'silNoticeService', 'lexProjectService', 'lexConfigService',
                                 function($scope, userService, ss, notice, lexProjectService, configService) {
	$scope.readProject = function() {
		lexProjectService.readProject(function(result) {
			if (result.ok) {
				configService.setConfig(result.data.config);
				$scope.project = result.data.project;
				
				// Rights
				var rights = result.data.rights;
				$scope.rights = {};
				$scope.rights.deleteOther = ss.hasRight(rights, ss.domain.USERS, ss.operation.DELETE); 
				$scope.rights.create = ss.hasRight(rights, ss.domain.USERS, ss.operation.CREATE); 
				$scope.rights.editOther = ss.hasRight(rights, ss.domain.USERS, ss.operation.EDIT);
				$scope.rights.showControlBar = $scope.rights.deleteOther || $scope.rights.create || $scope.rights.editOther;
				$scope.rights.canEditCommunicationSettings = ss.hasRight(ss.realm.SITE(), ss.domain.PROJECTS, ss.operation.EDIT);
			}
		});
	};
	
	$scope.readProject();
	
	$scope.updateProject = function() {
		lexProjectService.updateProject($scope.project, function(result) {
			if (result.ok) {
				notice.push(notice.SUCCESS, $scope.project.projectname + " settings updated successfully");
			}
		});
	};
	
	$scope.settings = {
		'sms': {},
		'email': {}
	};
		
	$scope.readCommunicationSettings = function() {
		lexProjectService.readSettings($scope.project.id, function(result) {
			if (result.ok) {
				$scope.settings.sms = result.data.sms;
				$scope.settings.email = result.data.email;
			}
		});
	};

	$scope.updateCommunicationSettings = function() {
		lexProjectService.updateSettings($scope.project.id, $scope.settings.sms, $scope.settings.email, function(result) {
			if (result.ok) {
				notice.push(notice.SUCCESS, $scope.project.projectname + " SMS settings updated successfully");
			}
		});
	};
	
}])
;

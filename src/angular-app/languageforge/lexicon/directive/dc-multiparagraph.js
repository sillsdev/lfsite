'use strict';

angular.module('palaso.ui.dc.multiParagraph', ['bellows.services', 'palaso.ui.showOverflow', 'palaso.ui.dc.formattedtext'])

// Dictionary Control Multitext
.directive('dcMultiParagraph', [function() {
  return {
    restrict: 'E',
    templateUrl: '/angular-app/languageforge/lexicon/directive/dc-multiparagraph.html',
    scope: {
      config: "=",
      model: "=",
      control: "=",
      selectField: "&"
    },
    controller: ['$scope', 'sessionService', function($scope, ss) {
      $scope.inputSystems = ss.session.projectSettings.config.inputSystems;

      $scope.inputSystemDirection = function inputSystemDirection(tag) {
        if (!(tag in $scope.inputSystems)) {
          return 'ltr';
        }
        return ($scope.inputSystems[tag].isRightToLeft) ? 'rtl' : 'ltr';
      };

      $scope.modelContainsSpan = function modelContainsSpan() {
        if (angular.isUndefined($scope.model)) {
          return false;
        }
        return $scope.model.value.indexOf('</span>') > -1;
      };

    }]
  };
}]);

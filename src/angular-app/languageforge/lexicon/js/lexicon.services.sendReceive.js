'use strict';

angular.module('lexicon.services')
  .service('lexSendReceiveApi', ['jsonRpc', function (jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.getUserProjects = function getUserProjects(username, password, callback) {
      jsonRpc.call('sendReceive_getUserProjects', [username, password], callback);
    };

    this.updateSRProject = function updateSRProject(srProject, callback) {
      jsonRpc.call('sendReceive_updateSRProject', [srProject], callback);
    };

    this.receiveProject = function receiveProject(callback) {
      jsonRpc.call('sendReceive_receiveProject', [], callback);
    };

    this.commitProject = function commitProject(callback) {
      jsonRpc.call('sendReceive_commitProject', [], callback);
    };

    this.getProjectStatus = function getProjectStatus(callback) {
      jsonRpc.call('sendReceive_getProjectStatus', [], callback);
    };
  }])
  .service('lexSendReceive', ['sessionService', 'silNoticeService', 'lexSendReceiveApi',
    '$interval',
    function (sessionService, notice, sendReceiveApi, $interval) {
      var _this = this;
      var projectSettings = sessionService.session.projectSettings;
      var syncProjectStatusSuccessCallback = angular.noop;
      var pollProjectStatusSuccessCallback = angular.noop;
      var cloneProjectStatusSuccessCallback = angular.noop;
      var syncStatusTimer;
      var pollStatusTimer;
      var cloneStatusTimer;
      var previousSRState;
      var pollStatusInterval = 32000; // ms
      var syncStatusInverval = 3000; // ms
      var unknownSRState = 'LF_UNKNOWN';

      var status = undefined;
      if (angular.isDefined(projectSettings) &&
          angular.isDefined(projectSettings.sendReceive) &&
          angular.isDefined(projectSettings.sendReceive.status)) {
        status = projectSettings.sendReceive.status;
        previousSRState = status.SRState;
      }

      this.clearState = function clearState() {
        if (!status || angular.isUndefined(status)) {
          status = {};
        }

        status.SRState = unknownSRState;
        previousSRState = unknownSRState;
      };

      // SRState is CLONING / SYNCING
      this.isInProgress = function isInProgress() {
        return (_this.isSendReceiveProject &&
          angular.isDefined(status) && angular.isDefined(status.SRState) &&
          (status.SRState == 'CLONING' || status.SRState == 'LF_CLONING' || status.SRState == 'SYNCING'));
      };

      // S/R isInProgress() or SRState is PENDING
      this.isStarted = function isStarted() {
        return (_this.isInProgress() || status.SRState == 'PENDING') || status.SRState == 'LF_PENDING';
      };

      this.isSendReceiveProject = function isSendReceiveProject() {
        return projectSettings.hasSendReceive;
      };

      this.setSyncProjectStatusSuccessCallback =
        function setSyncProjectStatusSuccessCallback(callback) {
          syncProjectStatusSuccessCallback = callback;
        };

      // Called after a lexicon project page is done loading
      this.checkInitialState = function checkInitialState() {
        if (_this.isSendReceiveProject()) {
          if (!status || angular.isUndefined(status)) {
            _this.clearState();
            getSyncProjectStatus();
            _this.startSyncStatusTimer();
          } else if (_this.isInProgress()) {
            _this.setSyncStarted();
          } else {
            if (status.SRState == unknownSRState) {
              _this.clearState();
            }
            _this.startPollStatusTimer();
          }
        }
      };

      this.setSyncStarted = function setSyncStarted() {
        notice.cancelProgressBar();

        // TODO: Remove this loading notice and display when we determine the real initial state
         notice.setLoading('***Syncing with LanguageDepot.org...');
        // Until LfMerge runs and updates the state file, SRState is unknown
        // Assume LF_PENDING for now
        console.log('SRState is ' + status.SRState + '.  Going to LF_PENDING');
        status.SRState = 'SYNCING'; // 'LF_PENDING'
        _this.startSyncStatusTimer();
      };

      this.setStateUnsynced = function setStateUnsynced() {
        if (_this.isSendReceiveProject()) {
          status.SRState = 'LF_UNSYNCED';
        }
      };

      function getSyncProjectStatus() {
        sendReceiveApi.getProjectStatus(function (result) {
          if (result.ok) {
            if (!result.data) {
              _this.clearState();
              _this.startPollStatusTimer();
              notice.cancelLoading();
              return;
            }

            // var isInitialCheck = (status.SRState == '');
            status = result.data;

            if (status.PercentComplete > 0) {
              notice.setPercentComplete(status.PercentComplete);
            } else {
              notice.cancelProgressBar();
            }

            if (!_this.isInProgress()) {
              _this.startPollStatusTimer();
              notice.cancelLoading();
            }

            console.log(status);

            switch(status.SRState) {
              case 'PENDING' :
              case 'LF_PENDING' :
                notice.push(notice.INFO, 'Please wait while other projects are being synced. ' +
                    'You may continue to edit this project until it starts to sync.');
                break;
              case 'SYNCING' :
                notice.setLoading('Syncing with LanguageDepot.org...');
                break;
              case 'HOLD' :
                notice.push(notice.ERROR, 'Well this is embarrassing. Something went ' +
                    'wrong and your project is now on hold. Contact an administrator.');
                break;
              case 'IDLE' :
                console.log('previousSRState: ' + previousSRState);
                if (previousSRState == 'SYNCING') {
                  notice.push(notice.SUCCESS, 'The project was successfully synced.');
                }
                (syncProjectStatusSuccessCallback || angular.noop)();
                break;
            };
          }
          previousSRState = status.SRState;
        });
      }

      this.startSyncStatusTimer = function startSyncStatusTimer() {
        _this.cancelPollStatusTimer();
        _this.cancelCloneStatusTimer();
        if (angular.isDefined(syncStatusTimer)) return;

        syncStatusTimer = $interval(getSyncProjectStatus, syncStatusInverval);
      };

      this.cancelSyncStatusTimer = function cancelSyncStatusTimer() {
        if (angular.isDefined(syncStatusTimer)) {
          $interval.cancel(syncStatusTimer);
          syncStatusTimer = undefined;
        }
      };

      // UI strings corresponding to SRState in the LfMerge state file.
      // SRStates with an "LF_" prefix are languageforge overrides
      this.syncNotice = function syncNotice() {
        if (angular.isUndefined(status)) return;

        switch (status.SRState) {
          case 'CLONING':
            return 'Creating initial data...';
          case 'SYNCING':
            return 'Syncing...';
          case 'PENDING':
          case 'LF_PENDING':
            return 'Pending';
          case 'IDLE':
          case 'SYNCED':
            return 'Synced';
          case 'LF_UNSYNCED':
            return 'Un-synced';
          case 'HOLD':
            return 'On hold';
          // Undefined initial state
          default:
            return '';
        }
      };

      this.setPollProjectStatusSuccessCallback =
        function setPollProjectStatusSuccessCallback(callback) {
          pollProjectStatusSuccessCallback = callback;
        };

      function getPollProjectStatus() {
        sendReceiveApi.getProjectStatus(function (result) {
          if (result.ok) {
            if (!result.data) {
              _this.clearState();
              return;
            }

            status = result.data;
            if (_this.isInProgress()) {
              (pollProjectStatusSuccessCallback || angular.noop)();
              _this.setSyncStarted();
            } else {
              (previousSRState == unknownSRState) && _this.clearState();
            }
          }
          previousSRState = status.SRState;
        });
      }

      this.startPollStatusTimer = function startPollStatusTimer() {
        _this.cancelSyncStatusTimer();
        _this.cancelCloneStatusTimer();
        if (angular.isDefined(pollStatusTimer)) return;

        pollStatusTimer = $interval(getPollProjectStatus, pollStatusInterval);
      };

      this.cancelPollStatusTimer = function cancelPollStatusTimer() {
        if (angular.isDefined(pollStatusTimer)) {
          $interval.cancel(pollStatusTimer);
          pollStatusTimer = undefined;
        }
      };

      this.setCloneProjectStatusSuccessCallback =
        function setCloneProjectStatusSuccessCallback(callback) {
          cloneProjectStatusSuccessCallback = callback;
        };

      function getCloneProjectStatus() {
        sendReceiveApi.getProjectStatus(function (result) {
          if (result.ok) {
            if (!result.data) {
              _this.clearState();
              _this.cancelCloneStatusTimer();
              return;
            }

            status = result.data;
            console.log(status);
            if (status.SRState == 'IDLE' ||
              status.SRState == 'HOLD') {
              _this.cancelCloneStatusTimer();
              (cloneProjectStatusSuccessCallback || angular.noop)();
            }
          }
        });
      }

      this.startCloneStatusTimer = function startCloneStatusTimer() {
        _this.cancelPollStatusTimer();
        _this.cancelSyncStatusTimer();
        status.SRState = 'LF_CLONING';
        if (angular.isDefined(cloneStatusTimer)) return;

        cloneStatusTimer = $interval(getCloneProjectStatus, 3000);
      };

      this.cancelCloneStatusTimer = function cancelCloneStatusTimer() {
        if (angular.isDefined(cloneStatusTimer)) {
          $interval.cancel(cloneStatusTimer);
          cloneStatusTimer = undefined;
        }
      };

      // For now, we generate the same S/R string based on the SRState
      this.cloneNotice = this.syncNotice;

      this.cancelAllStatusTimers = function cancelAllStatusTimers() {
        _this.cancelSyncStatusTimer();
        _this.cancelPollStatusTimer();
        _this.cancelCloneStatusTimer();
      };

    }])

  ;

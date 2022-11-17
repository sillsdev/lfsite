<?php

namespace Api\Model\Shared\Dto;

use Api\Library\Shared\Website;
use Api\Model\Shared\Command\LdapiCommands;
use Api\Model\Shared\Rights\Domain;
use Api\Model\Shared\Rights\Operation;
use Api\Model\Shared\Rights\SiteRoles;
use Api\Model\Shared\Rights\SystemRoles;
use Api\Model\Shared\ProjectModel;
use Api\Model\Shared\UserModel;

class RightsHelper
{
    /** @var string */
    private $_userId;

    /** @var ProjectModel */
    private $_projectModel;

    /** @var Website */
    private $_website;

    /**
     * @param UserModel $userModel
     * @param ProjectModel $projectModel
     * @return mixed
     */
    public static function encode($userModel, $projectModel)
    {
        return $projectModel->getRightsArray($userModel->id->asString());
    }

    /**
     * @param string $userId
     * @param int $right
     * @return boolean
     */
    // Note: there is a bug/annoyance in PHP5 whereby you cannot have an object method and a static method named the same
    // I named this static function slightly different from userHasSiteRight to avoid this naming conflict
    // @see http://stackoverflow.com/questions/11331616/php-is-it-possible-to-declare-a-method-static-and-nonstatic
    // @see https://bugs.php.net/bug.php?id=40837
    public static function hasSiteRight($userId, $right)
    {
        return self::_hasSiteRight($userId, $right);
    }

    /**
     * @param int $right
     * @return bool
     */
    public function userHasSiteRight($right)
    {
        return self::_hasSiteRight($this->_userId, $right);
    }

    private static function _hasSiteRight($userId, $right)
    {
        $userModel = new UserModel($userId);
        return SiteRoles::hasRight($userModel->siteRole, $right) || SystemRoles::hasRight($userModel->role, $right);
    }

    /**
     * @param string $userId
     * @param ProjectModel $projectModel
     * @param Website $website
     */
    public function __construct($userId, $projectModel, $website)
    {
        $this->_userId = $userId;
        $this->_projectModel = $projectModel;
        $this->_website = $website;
    }

    /**
     * @param int $right
     * @return bool
     */
    public function userHasSystemRight($right)
    {
        $userModel = new UserModel($this->_userId);

        return SystemRoles::hasRight($userModel->role, $right);
    }

    /**
     * @param int $right
     * @return bool
     */
    public function userHasProjectRight($right)
    {
        return isset($this->_projectModel) ? $this->_projectModel->hasRight($this->_userId, $right) : false;
    }

    /**
     * @param string $methodName
     * @param array $params parameters passed to the method
     * @return bool
     * @throws \Exception
     */
    public function userCanAccessMethod($methodName)
    {
        switch ($methodName) {
            case "project_getJoinRequests":
                return $this->userHasProjectRight(Domain::USERS + Operation::EDIT);
            case "project_sendJoinRequest":
                return true;

            case "project_getInviteLink":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::VIEW);

            case "project_disableInviteToken":
            case "project_createInviteLink":
            case "project_updateInviteTokenRole":
                return $this->userHasProjectRight(Domain::USERS + Operation::EDIT);
            case "project_acceptJoinRequest":
                return $this->userHasProjectRight(Domain::USERS + Operation::EDIT);
            case "project_denyJoinRequest":
                return $this->userHasProjectRight(Domain::USERS + Operation::EDIT);
            case "user_sendInvite":
            case "message_markRead":
            case "project_pageDto":
            case "lex_projectDto":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::VIEW);

            // Project Manager Role (Project Context)
            case "user_createSimple":
                return $this->userHasProjectRight(Domain::USERS + Operation::CREATE);

            case "user_typeahead":
            case "user_typeaheadExclusive":
                return $this->userHasProjectRight(Domain::USERS + Operation::VIEW);

            case "message_send":
            case "project_read":
            case "set_project":
            case "project_settings":
            case "project_updateSettings":
            case "project_readSettings":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::EDIT);

            case "project_update":
            case "lex_project_update":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::EDIT);

            case "project_updateUserRole":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::EDIT);

            case "project_joinProject":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::EDIT);

            case "project_usersDto":
                return $this->userHasProjectRight(Domain::USERS + Operation::VIEW);

            case "project_removeUsers":
                return $this->userHasProjectRight(Domain::USERS + Operation::DELETE);

            // Admin (system context)
            case "user_read":
            case "user_list":
            case "project_insights_csv":
                return $this->userHasSiteRight(Domain::USERS + Operation::VIEW);

            case "user_ban":
            case "user_update":
            case "user_create":
                return $this->userHasSiteRight(Domain::USERS + Operation::EDIT);

            case "user_delete":
                return $this->userHasSiteRight(Domain::USERS + Operation::DELETE);

            case "project_archive":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::ARCHIVE) ||
                    $this->userHasSiteRight(Domain::PROJECTS + Operation::CREATE);

            case "project_archivedList":
            case "project_publish":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::ARCHIVE);

            case "project_list":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::VIEW);

            case "project_create":
            case "project_create_switchSession":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::CREATE);

            case "project_join_switchSession":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::CREATE);

            case "project_delete":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::DELETE) ||
                    $this->userHasSiteRight(Domain::PROJECTS + Operation::CREATE);

            case "projectcode_exists":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::CREATE);

            // User (site context)
            case "user_readProfile":
                return $this->userHasSiteRight(Domain::USERS + Operation::VIEW_OWN);

            case "user_updateProfile":
            case "check_unique_identity":
            case "change_password": // change_password requires additional protection in the method itself
                return $this->userHasSiteRight(Domain::USERS + Operation::EDIT_OWN);
            case "project_list_dto":
            case "activity_list_dto":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::VIEW_OWN);

            case "activity_list_dto_for_current_project":
                return $this->userHasSiteRight(Domain::PROJECTS + Operation::VIEW_OWN);

            case "activity_list_dto_for_lexical_entry":
                return $this->userHasProjectRight(Domain::ENTRIES + Operation::VIEW);

            case "session_getSessionData":
                return true;

            case "valid_activity_types_dto":
                return true;

            // LanguageForge (lexicon)
            case "lex_configuration_update":
            case "lex_upload_importLift":
            case "lex_upload_importProjectZip":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::EDIT);

            case "lex_baseViewDto":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::VIEW);

            case "lex_dbeDtoFull":
            case "lex_dbeDtoUpdatesOnly":
            case "lex_stats":
                return $this->userHasProjectRight(Domain::ENTRIES + Operation::VIEW);

            // case 'lex_entry_read':
            case "lex_entry_update":
                return $this->userHasProjectRight(Domain::ENTRIES + Operation::EDIT);

            case "lex_entry_remove":
                return $this->userHasProjectRight(Domain::ENTRIES + Operation::DELETE);

            case "lex_comment_update":
            case "lex_commentReply_update":
                return $this->userHasProjectRight(Domain::COMMENTS + Operation::EDIT_OWN);

            case "lex_comment_delete":
            case "lex_commentReply_delete":
                return $this->userHasProjectRight(Domain::COMMENTS + Operation::DELETE_OWN);

            case "lex_comment_updateStatus":
                return $this->userHasProjectRight(Domain::COMMENTS + Operation::EDIT);

            case "lex_comment_plusOne":
                return $this->userHasProjectRight(Domain::COMMENTS + Operation::VIEW);

            case "lex_uploadAudioFile":
            case "lex_uploadImageFile":
            case "lex_project_removeMediaFile":
                return $this->userHasProjectRight(Domain::ENTRIES + Operation::EDIT);

            // send receive api
            case "sendReceive_getProjectStatus":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::VIEW);

            case "sendReceive_updateSRProject":
            case "sendReceive_receiveProject":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::EDIT);

            // project management app
            case "project_management_dto":
                return $this->userHasProjectRight(Domain::PROJECTS + Operation::EDIT);

            // Language Depot API access
            case "ldapi_check_user_password":
            case "ldapi_get_user":
            case "ldapi_update_user":
            case "ldapi_search_users":
            case "ldapi_get_all_projects":
            case "ldapi_get_all_users":
            case "ldapi_get_all_roles":
            case "ldapi_get_project":
            case "ldapi_get_projects_for_user":
            case "ldapi_project_updateUserRole":
            case "ldapi_project_removeUser":
            case "ldapi_user_is_manager_of_project":
                return true; // Handled server-side via JWT

            default:
                throw new \Exception(
                    "API method '$methodName' has no security policy defined in RightsHelper::userCanAccessMethod()"
                );
        }
    }

    /**
     * @param string $methodName
     * @param array $params parameters passed to the method
     * @return bool
     * @throws \Exception
     */
    public function userCanAccessMethodWithParams($methodName, $params)
    {
        return true; // Server now handles this via the JWT we'll pass it
    }
}

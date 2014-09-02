<?php
namespace models\shared\rights;

use libraries\shared\palaso\CodeGuard;

class RolesBase
{
    /**
	 * @param array $rights
	 * @param int $domain
	 */
    protected static function grantAllOnDomain(&$rights, $domain)
    {
        foreach (Operation::$operations as $operation) {
            $rights[] = $domain + $operation;
        }
    }

    /**
	 * Returns true if the given $role has the $right in the $realm
	 * @param string $role
	 * @param int $right
	 * @return bool
	 */
    protected static function _hasRight($rightsArray, $role, $right)
    {
        CodeGuard::checkNotFalseAndThrow($role, 'role');
        if (!array_key_exists($role, $rightsArray)) {
            throw new \Exception("Role '$role' does not exist");
        }
        $result = in_array($right, $rightsArray[$role]);

        return $result;
    }

    /**
	 * Returns the array of rights for this $role in the given $realm
	 * @param string $role
	 * @return array
	 */
    protected static function _getRightsArray($rightsArray, $role)
    {
        CodeGuard::checkNotFalseAndThrow($role, 'role');
        if (!array_key_exists($role, $rightsArray)) {
            throw new \Exception("Role '$role' does not exist.");
        }

        return $rightsArray[$role];
    }

}

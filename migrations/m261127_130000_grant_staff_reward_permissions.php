<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Lets staff manage the reward catalog that supplies redeemable menu treats.
 */
class m261127_130000_grant_staff_reward_permissions extends Migration
{
    /** @var list<string> */
    private const PERMISSIONS = [
        'reward.index',
        'reward.view',
        'reward.create',
        'reward.update',
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('staff');
        if (!$role instanceof Role) {
            throw new RuntimeException("Role 'staff' does not exist.");
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new RuntimeException("Could not create permission '{$name}'.");
                }
            }
            if (!$auth->hasChild($role, $permission) && !$auth->addChild($role, $permission)) {
                throw new RuntimeException("Could not grant '{$name}' to 'staff'.");
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('staff');
        if (!$role instanceof Role) {
            return;
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission instanceof Permission && $auth->hasChild($role, $permission)) {
                $auth->removeChild($role, $permission);
            }
        }
    }
}

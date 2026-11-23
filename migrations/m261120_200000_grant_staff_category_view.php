<?php

declare(strict_types=1);

use yii\db\Migration;

class m261120_200000_grant_staff_category_view extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        $permission = $auth->getPermission('category.view');
        if ($staff === null || $permission === null) {
            throw new RuntimeException('The staff role or category.view permission is missing.');
        }
        if (!$auth->hasChild($staff, $permission) && !$auth->addChild($staff, $permission)) {
            throw new RuntimeException('Could not grant category.view to staff.');
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        $permission = $auth->getPermission('category.view');
        if ($staff !== null && $permission !== null && $auth->hasChild($staff, $permission)) {
            $auth->removeChild($staff, $permission);
        }
    }
}

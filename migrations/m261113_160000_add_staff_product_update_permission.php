<?php

use yii\db\Migration;

class m261113_160000_add_staff_product_update_permission extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        $permission = $auth->getPermission('product.update');

        if ($staff === null || $permission === null) {
            throw new \yii\base\InvalidConfigException(
                'The staff role or product.update permission is missing. Run the initial RBAC migration first.'
            );
        }

        if (!$auth->hasChild($staff, $permission)) {
            $auth->addChild($staff, $permission);
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        $permission = $auth->getPermission('product.update');

        if ($staff !== null && $permission !== null && $auth->hasChild($staff, $permission)) {
            $auth->removeChild($staff, $permission);
        }
    }
}

<?php

use yii\db\Migration;

class m261120_090000_grant_staff_product_delete extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        $permission = $auth->getPermission('product.delete');
        if ($staff === null || $permission === null) {
            throw new \RuntimeException('The staff role or product.delete permission is missing.');
        }
        if (!$auth->hasChild($staff, $permission) && !$auth->addChild($staff, $permission)) {
            throw new \RuntimeException('Could not grant product.delete to staff.');
        }
    }

    public function safeDown(): bool
    {
        echo "This migration cannot be reverted without risking an existing permission grant.\n";

        return false;
    }
}

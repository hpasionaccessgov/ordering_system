<?php

use yii\db\Migration;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Handles the update of phase four permissions.
 */
class m261115_012230_update_phase_four_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $archiveProduct = $auth->getPermission('product.archive');
        if ($archiveProduct === null) {
            $archiveProduct = $auth->createPermission('product.archive');
            $archiveProduct->description = 'Archive products';
            $auth->add($archiveProduct);
        }

        $staff = $auth->getRole('staff');
        $admin = $auth->getRole('admin');
        $orderCancel = $auth->getPermission('order.cancel');

        if ($staff instanceof Role && !$auth->hasChild($staff, $archiveProduct)) {
            $auth->addChild($staff, $archiveProduct);
        }

        if ($admin instanceof Role && !$auth->hasChild($admin, $archiveProduct)) {
            $auth->addChild($admin, $archiveProduct);
        }

        if ($staff instanceof Role && $orderCancel instanceof Permission) {
            if (!$auth->hasChild($staff, $orderCancel)) {
                $auth->addChild($staff, $orderCancel);
            }
        }

        // Staff may archive products, but may not permanently delete them.
        $productDelete = $auth->getPermission('product.delete');
        if (
            $staff instanceof Role
            && $productDelete instanceof Permission
            && $auth->hasChild($staff, $productDelete)
        ) {
            $auth->removeChild($staff, $productDelete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $staff = $auth->getRole('staff');
        $archiveProduct = $auth->getPermission('product.archive');
        $orderCancel = $auth->getPermission('order.cancel');

        if (
            $staff !== null && $archiveProduct !== null && $auth->hasChild($staff, $archiveProduct)
        ) {
            $auth->removeChild($staff, $archiveProduct);
        }

        if (
            $staff !== null && $orderCancel !== null && $auth->hasChild($staff, $orderCancel)
        ) {
            $auth->removeChild($staff, $orderCancel);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261115_012230_update_phase_four_permissions cannot be reverted.\n";

        return false;
    }
    */
}

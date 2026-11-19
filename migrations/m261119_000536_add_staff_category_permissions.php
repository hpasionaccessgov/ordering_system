<?php

use yii\db\Migration;

class m261119_000536_add_staff_category_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        foreach (['category.view', 'category.index', 'category.create', 'category.update', 'category.delete'] as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if ($staff !== null && $permission !== null && !$auth->hasChild($staff, $permission)) {
                $auth->addChild($staff, $permission);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m261119_000536_add_staff_category_permissions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261119_000536_add_staff_category_permissions cannot be reverted.\n";

        return false;
    }
    */
}

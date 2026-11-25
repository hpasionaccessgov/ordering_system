<?php

use yii\db\Migration;
use yii\rbac\Permission;
use yii\rbac\Role;

class m261125_140000_add_product_specials_and_customer_favorites extends Migration
{
    private const FAVORITE_PERMISSIONS = ['favorite.index', 'favorite.add', 'favorite.remove'];

    public function safeUp(): void
    {
        $this->addColumn('{{%products}}', 'is_special', $this->boolean()->notNull()->defaultValue(false));
        $this->createIndex('idx-products-special', '{{%products}}', 'is_special');

        $this->update(
            '{{%products}}',
            ['is_special' => true],
            ['name' => ['Cafebara House Latte', 'Dark Chocolate Cookie']],
        );

        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        if (!$staff instanceof Role) {
            return;
        }

        foreach (self::FAVORITE_PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission instanceof Permission && $auth->hasChild($staff, $permission)) {
                $auth->removeChild($staff, $permission);
            }
        }
    }

    public function safeDown(): bool
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        if ($staff instanceof Role) {
            foreach (self::FAVORITE_PERMISSIONS as $name) {
                $permission = $auth->getPermission($name);
                if ($permission === null) {
                    $permission = $auth->createPermission($name);
                    $permission->description = $name;
                    $auth->add($permission);
                }
                if (!$auth->hasChild($staff, $permission)) {
                    $auth->addChild($staff, $permission);
                }
            }
        }

        $this->dropIndex('idx-products-special', '{{%products}}');
        $this->dropColumn('{{%products}}', 'is_special');

        return true;
    }
}

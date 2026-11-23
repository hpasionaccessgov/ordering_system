<?php

declare(strict_types=1);

use yii\db\Migration;

class m261120_190000_generalize_notifications extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->schema;
        $oldTable = '{{%order_notifications}}';
        $newTable = '{{%notifications}}';

        if ($schema->getTableSchema($oldTable, true) !== null && $schema->getTableSchema($newTable, true) === null) {
            $this->renameTable($oldTable, $newTable);
            $schema->refresh();
        }

        $table = $schema->getTableSchema($newTable, true);
        if ($table === null) {
            throw new RuntimeException('The notifications table is missing.');
        }
        $columns = array_keys($table->columns);

        if (!in_array('type', $columns, true)) {
            $this->addColumn($newTable, 'type', $this->string(30)->notNull()->defaultValue('order'));
        }
        if (!in_array('message', $columns, true)) {
            $this->addColumn($newTable, 'message', $this->string(255)->null());
        }
        if (!in_array('target_route', $columns, true)) {
            $this->addColumn($newTable, 'target_route', $this->string(255)->null());
        }
        if (!in_array('target_id', $columns, true)) {
            $this->addColumn($newTable, 'target_id', $this->integer()->null());
        }
        if (!$table->columns['order_id']->allowNull) {
            $this->alterColumn($newTable, 'order_id', $this->integer()->null());
        }
        if (!$table->columns['status']->allowNull) {
            $this->alterColumn($newTable, 'status', $this->string(30)->null());
        }

        $this->execute(
            "UPDATE {{%notifications}} SET [[target_route]] = '/order/view', [[target_id]] = [[order_id]] "
                . 'WHERE [[target_route]] IS NULL AND [[order_id]] IS NOT NULL'
        );
    }

    public function safeDown(): bool
    {
        $generic = (int) $this->db->createCommand(
            'SELECT COUNT(*) FROM {{%notifications}} WHERE [[type]] <> :orderType'
                . ' OR [[order_id]] IS NULL OR [[status]] IS NULL OR [[message]] IS NOT NULL',
            [':orderType' => 'order'],
        )->queryScalar();
        if ($generic > 0) {
            echo "Cannot roll back: generic notifications would lose data.\n";

            return false;
        }

        $table = '{{%notifications}}';
        $this->alterColumn($table, 'order_id', $this->integer()->notNull());
        $this->alterColumn($table, 'status', $this->string(30)->notNull());
        $this->dropColumn($table, 'target_id');
        $this->dropColumn($table, 'target_route');
        $this->dropColumn($table, 'message');
        $this->dropColumn($table, 'type');
        $this->renameTable($table, '{{%order_notifications}}');

        return true;
    }
}

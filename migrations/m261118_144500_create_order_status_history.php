<?php

use yii\db\Migration;

class m261118_144500_create_order_status_history extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%order_status_history}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'changed_by' => $this->integer()->null(),
            'from_status' => $this->string(30)->null(),
            'to_status' => $this->string(30)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'idx-order_status_history-order_id',
            '{{%order_status_history}}',
            'order_id',
        );
        $this->createIndex(
            'idx-order_status_history-changed_by',
            '{{%order_status_history}}',
            'changed_by',
        );
        $this->addForeignKey(
            'fk-order_status_history-order_id',
            '{{%order_status_history}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-order_status_history-changed_by',
            '{{%order_status_history}}',
            'changed_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE',
        );

        $orders = $this->db->createCommand(
            'SELECT id, status, created_at, updated_at FROM {{%orders}} ORDER BY id',
        )->queryAll();
        foreach ($orders as $order) {
            $this->insert('{{%order_status_history}}', [
                'order_id' => $order['id'],
                'changed_by' => null,
                'from_status' => null,
                'to_status' => 'pending',
                'created_at' => $order['created_at'],
            ]);
            if ($order['status'] !== 'pending') {
                $this->insert('{{%order_status_history}}', [
                    'order_id' => $order['id'],
                    'changed_by' => null,
                    'from_status' => 'pending',
                    'to_status' => $order['status'],
                    'created_at' => $order['updated_at'],
                ]);
            }
        }
    }

    public function safeDown(): void
    {
        $this->dropForeignKey(
            'fk-order_status_history-changed_by',
            '{{%order_status_history}}',
        );
        $this->dropForeignKey(
            'fk-order_status_history-order_id',
            '{{%order_status_history}}',
        );
        $this->dropTable('{{%order_status_history}}');
    }
}

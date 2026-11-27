<?php

declare(strict_types=1);

use yii\db\Migration;

class m261127_000000_add_missing_audit_user_columns extends Migration
{
    private const COLUMNS = [
        '{{%activity_log}}' => ['created_by', 'updated_by'],
        '{{%application_settings}}' => ['created_by', 'updated_by'],
        '{{%auth_assignment}}' => ['created_by', 'updated_by'],
        '{{%auth_item_child}}' => ['created_by', 'updated_by'],
        '{{%auth_rule}}' => ['created_by', 'updated_by'],
        '{{%cart_items}}' => ['created_by', 'updated_by'],
        '{{%favorite}}' => ['created_by', 'updated_by'],
        '{{%inventory}}' => ['created_by', 'updated_by'],
        '{{%inventory_transactions}}' => ['updated_by'],
        '{{%loyalty_point_transaction}}' => ['created_by', 'updated_by'],
        '{{%notifications}}' => ['created_by', 'updated_by'],
        '{{%order_status_history}}' => ['created_by', 'updated_by'],
        '{{%product_ingredient}}' => ['created_by', 'updated_by'],
        '{{%product_review}}' => ['created_by', 'updated_by'],
        '{{%reward}}' => ['created_by', 'updated_by'],
    ];

    public function safeUp(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            $schema = $this->db->getTableSchema($table, true);
            if ($schema === null) {
                continue;
            }
            foreach ($columns as $column) {
                if (!isset($schema->columns[$column])) {
                    $this->addColumn($table, $column, $this->integer()->null());
                }
            }
        }
    }

    public function safeDown(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            $schema = $this->db->getTableSchema($table, true);
            if ($schema === null) {
                continue;
            }
            foreach ($columns as $column) {
                if (isset($schema->columns[$column])) {
                    $this->dropColumn($table, $column);
                }
            }
        }
    }
}

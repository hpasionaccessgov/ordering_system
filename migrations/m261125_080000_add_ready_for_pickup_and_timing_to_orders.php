<?php

declare(strict_types=1);

use yii\db\Migration;

class m261125_080000_add_ready_for_pickup_and_timing_to_orders extends Migration
{
    public function safeUp(): void
    {
        $table = '{{%orders}}';
        $schema = $this->db->getTableSchema($table, true);
        if ($schema === null) {
            throw new RuntimeException('The orders table is missing.');
        }

        foreach (['confirmed_at', 'processing_at', 'ready_at', 'completed_at'] as $column) {
            if (!isset($schema->columns[$column])) {
                $this->addColumn($table, $column, $this->integer()->null()->after('updated_at'));
            }
        }

        // Backfill from status history; latest transition wins. ready_at stays null for legacy rows.
        $this->execute(
            'UPDATE {{%orders}} o SET o.[[confirmed_at]] = ('
            . 'SELECT MAX(h.[[created_at]]) FROM {{%order_status_history}} h '
            . 'WHERE h.[[order_id]] = o.[[id]] AND h.[[to_status]] = :status'
            . ') WHERE o.[[confirmed_at]] IS NULL',
            [':status' => 'confirmed'],
        );
        $this->execute(
            'UPDATE {{%orders}} o SET o.[[processing_at]] = ('
            . 'SELECT MAX(h.[[created_at]]) FROM {{%order_status_history}} h '
            . 'WHERE h.[[order_id]] = o.[[id]] AND h.[[to_status]] = :status'
            . ') WHERE o.[[processing_at]] IS NULL',
            [':status' => 'processing'],
        );
        $this->execute(
            'UPDATE {{%orders}} o SET o.[[completed_at]] = ('
            . 'SELECT MAX(h.[[created_at]]) FROM {{%order_status_history}} h '
            . 'WHERE h.[[order_id]] = o.[[id]] AND h.[[to_status]] = :status'
            . ') WHERE o.[[completed_at]] IS NULL',
            [':status' => 'completed'],
        );
    }

    public function safeDown(): void
    {
        $table = '{{%orders}}';
        $schema = $this->db->getTableSchema($table, true);
        if ($schema === null) {
            return;
        }
        foreach (['completed_at', 'ready_at', 'processing_at', 'confirmed_at'] as $column) {
            if (isset($schema->columns[$column])) {
                $this->dropColumn($table, $column);
            }
        }
    }
}

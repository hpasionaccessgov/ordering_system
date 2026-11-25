<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates the loyalty ledger and reward catalog, then seeds the initial rewards.
 */
class m261125_130000_create_loyalty_tables extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->schema;
        if ($schema->getTableSchema('{{%loyalty_point_transaction}}', true) === null) {
            $this->createTable('{{%loyalty_point_transaction}}', [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'order_id' => $this->integer()->null(),
                'points' => $this->integer()->notNull(),
                'transaction_type' => $this->string(10)->notNull(),
                'remarks' => $this->string(255)->null(),
                'created_at' => $this->integer()->notNull(),
            ]);
            $this->createIndex('idx-lpt-user-created', '{{%loyalty_point_transaction}}', ['user_id', 'created_at', 'id']);
            $this->createIndex(
                'uq-lpt-user-order-earn',
                '{{%loyalty_point_transaction}}',
                ['user_id', 'order_id', 'transaction_type'],
                true,
            );
            $this->addForeignKey(
                'fk-lpt-user_id',
                '{{%loyalty_point_transaction}}',
                'user_id',
                '{{%users}}',
                'id',
                'CASCADE',
                'CASCADE',
            );
            $this->addForeignKey(
                'fk-lpt-order_id',
                '{{%loyalty_point_transaction}}',
                'order_id',
                '{{%orders}}',
                'id',
                'SET NULL',
                'CASCADE',
            );
        }

        $this->execute(
            "INSERT INTO {{%loyalty_point_transaction}}
                ([[user_id]], [[order_id]], [[points]], [[transaction_type]], [[remarks]], [[created_at]])
            SELECT [[o]].[[user_id]], [[o]].[[id]],
                FLOOR([[o]].[[total_amount]] / 100), 'EARN',
                CONCAT('Order #', [[o]].[[order_number]], ' completed'),
                COALESCE([[o]].[[completed_at]], [[o]].[[created_at]], UNIX_TIMESTAMP())
            FROM {{%orders}} [[o]]
            WHERE [[o]].[[status]] = 'completed'
              AND NOT EXISTS (
                  SELECT 1 FROM {{%loyalty_point_transaction}} [[l]]
                  WHERE [[l]].[[user_id]] = [[o]].[[user_id]]
                    AND [[l]].[[order_id]] = [[o]].[[id]]
                    AND [[l]].[[transaction_type]] = 'EARN'
              )"
        );

        if ($schema->getTableSchema('{{%reward}}', true) === null) {
            $this->createTable('{{%reward}}', [
                'id' => $this->primaryKey(),
                'name' => $this->string(100)->notNull(),
                'description' => $this->text()->null(),
                'point_cost' => $this->integer()->notNull(),
                'status' => $this->smallInteger()->notNull()->defaultValue(10),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
            $this->createIndex('uq-reward-name', '{{%reward}}', ['name'], true);
            $this->createIndex('idx-reward-status-cost', '{{%reward}}', ['status', 'point_cost']);
        }

        $now = time();
        foreach ([
            ['Free Coffee', 'A freshly brewed coffee, on the house.', 50],
            ['Free Dessert', 'A sweet treat to complete your visit.', 100],
        ] as [$name, $description, $cost]) {
            $exists = (new \yii\db\Query())
                ->from('{{%reward}}')
                ->where(['name' => $name])
                ->exists($this->db);
            if ($exists) {
                continue;
            }
            $this->insert('{{%reward}}', [
                'name' => $name,
                'description' => $description,
                'point_cost' => $cost,
                'status' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%reward}}');
        $this->dropTable('{{%loyalty_point_transaction}}');
    }
}

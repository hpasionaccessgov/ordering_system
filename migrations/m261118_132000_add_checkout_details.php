<?php

use yii\db\Migration;

class m261118_132000_add_checkout_details extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%orders}}', 'order_number', $this->string(40)->null());
        $this->addColumn('{{%orders}}', 'checkout_token', $this->string(64)->null());
        $this->addColumn('{{%order_items}}', 'product_name', $this->string(150)->null());
        $this->addColumn('{{%order_items}}', 'subtotal', $this->decimal(10, 2)->null());

        $this->db->createCommand(
            'UPDATE {{%order_items}} i'
                . ' INNER JOIN {{%products}} p ON p.id = i.product_id'
                . ' SET i.product_name = p.name, i.subtotal = ROUND(i.unit_price * i.quantity, 2)',
        )->execute();
        $rows = $this->db->createCommand(
            'SELECT id, created_at FROM {{%orders}} ORDER BY id',
        )->queryAll();
        foreach ($rows as $row) {
            $date = date('Ymd', (int) ($row['created_at'] ?: time()));
            $this->update('{{%orders}}', [
                'order_number' => sprintf('CB-%s-%06d', $date, (int) $row['id']),
            ], ['id' => $row['id']]);
        }

        $this->alterColumn('{{%orders}}', 'order_number', $this->string(40)->notNull());
        $this->alterColumn('{{%order_items}}', 'product_name', $this->string(150)->notNull());
        $this->alterColumn('{{%order_items}}', 'subtotal', $this->decimal(10, 2)->notNull());
        $this->createIndex('idx-orders-order_number-unique', '{{%orders}}', 'order_number', true);
        $this->createIndex('idx-orders-checkout_token-unique', '{{%orders}}', 'checkout_token', true);
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-orders-checkout_token-unique', '{{%orders}}');
        $this->dropIndex('idx-orders-order_number-unique', '{{%orders}}');
        $this->dropColumn('{{%order_items}}', 'subtotal');
        $this->dropColumn('{{%order_items}}', 'product_name');
        $this->dropColumn('{{%orders}}', 'checkout_token');
        $this->dropColumn('{{%orders}}', 'order_number');
    }
}

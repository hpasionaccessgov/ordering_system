<?php

use yii\db\Migration;

class m261119_120000_add_audit_user_columns extends Migration
{
    private const TABLES = [
        '{{%users}}',
        '{{%category}}',
        '{{%products}}',
        '{{%orders}}',
        '{{%order_items}}',
        '{{%auth_item}}',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (self::TABLES as $table) {
            $schema = $this->db->getTableSchema($table, true);
            if ($schema === null) {
                continue;
            }
            if (!isset($schema->columns['created_by'])) {
                $this->addColumn($table, 'created_by', $this->integer());
            }
            if (!isset($schema->columns['updated_by'])) {
                $this->addColumn($table, 'updated_by', $this->integer());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (self::TABLES as $table) {
            $schema = $this->db->getTableSchema($table, true);
            if ($schema === null) {
                continue;
            }
            if (isset($schema->columns['created_by'])) {
                $this->dropColumn($table, 'created_by');
            }
            if (isset($schema->columns['updated_by'])) {
                $this->dropColumn($table, 'updated_by');
            }
        }
    }
}

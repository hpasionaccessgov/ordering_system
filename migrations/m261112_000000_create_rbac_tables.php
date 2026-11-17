<?php

use yii\db\Migration;

class m261112_000000_create_rbac_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%auth_rule}}', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
        ]);

        $this->createTable('{{%auth_item}}', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
            'CONSTRAINT [[fk-auth_item-rule_name]] FOREIGN KEY ([[rule_name]])'
                . ' REFERENCES {{%auth_rule}} ([[name]]) ON DELETE SET NULL ON UPDATE CASCADE',
            'KEY [[rule_name]] ([[rule_name]])',
        ]);

        $this->createTable('{{%auth_item_child}}', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY ([[parent]], [[child]])',
            'CONSTRAINT [[fk-auth_item_child-parent]] FOREIGN KEY ([[parent]])'
                . ' REFERENCES {{%auth_item}} ([[name]]) ON DELETE CASCADE ON UPDATE CASCADE',
            'CONSTRAINT [[fk-auth_item_child-child]] FOREIGN KEY ([[child]])'
                . ' REFERENCES {{%auth_item}} ([[name]]) ON DELETE CASCADE ON UPDATE CASCADE',
        ]);

        $this->createTable('{{%auth_assignment}}', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY ([[item_name]], [[user_id]])',
            'CONSTRAINT [[fk-auth_assignment-item_name]] FOREIGN KEY ([[item_name]])'
                . ' REFERENCES {{%auth_item}} ([[name]]) ON DELETE CASCADE ON UPDATE CASCADE',
        ]);
    }

    public function safeDown(): bool
    {
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');

        return true;
    }
}

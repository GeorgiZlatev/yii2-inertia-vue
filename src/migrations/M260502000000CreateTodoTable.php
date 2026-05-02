<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

/**
 * Creates the `todo` table used by the authenticated task list.
 */
final class M260502000000CreateTodoTable extends Migration
{
    public function safeDown(): void
    {
        $this->dropForeignKey('fk_todo_created_by_user_id', '{{%todo}}');
        $this->dropIndex('idx_todo_created_by', '{{%todo}}');
        $this->dropTable('{{%todo}}');
    }

    public function safeUp(): void
    {
        $this->createTable(
            '{{%todo}}',
            [
                'id' => $this->primaryKey(),
                'title' => $this->string(255)->notNull(),
                'description' => $this->text()->notNull(),
                'is_completed' => $this->boolean()->notNull()->defaultValue(false),
                'created_by' => $this->integer()->notNull(),
                'completed_at' => $this->integer()->defaultValue(null),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ],
        );

        $this->createIndex('idx_todo_created_by', '{{%todo}}', 'created_by');

        $this->addForeignKey(
            'fk_todo_created_by_user_id',
            '{{%todo}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'RESTRICT',
        );
    }
}

<?php

use yii\db\Migration;

class m230921_155550_create_table_np_users_type extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%np_users_type}}',
            [
                'id' => $this->primaryKey(),
                'desc' => $this->string(50),
                'status' => $this->string(50)->notNull()->defaultValue('0'),
                'note' => $this->string()->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%np_users_type}}');
    }
}

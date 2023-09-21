<?php

use yii\db\Migration;

class m230921_155525_create_table_np_consensus extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%np_consensus}}',
            [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'type' => $this->string(50)->notNull(),
                'timestamp' => $this->integer()->notNull(),
                'type_operation' => $this->integer()->notNull()->defaultValue('0'),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%np_consensus}}');
    }
}

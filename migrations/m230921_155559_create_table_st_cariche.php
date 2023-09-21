<?php

use yii\db\Migration;

class m230921_155559_create_table_st_cariche extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%st_cariche}}',
            [
                'id' => $this->primaryKey(),
                'users_type_id' => $this->integer()->notNull(),
                'description' => $this->string(50)->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%st_cariche}}');
    }
}

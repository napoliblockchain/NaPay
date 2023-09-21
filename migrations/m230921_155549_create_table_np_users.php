<?php

use yii\db\Migration;

class m230921_155549_create_table_np_users extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%np_users}}',
            [
                'id' => $this->primaryKey(),
                'username' => $this->string()->notNull(),
                'users_type_id' => $this->integer()->notNull(),
                'carica_id' => $this->integer()->notNull(),
                'email' => $this->string()->notNull(),
                'password' => $this->string()->notNull(),
                'ga_secret_key' => $this->string(16),
                'first_name' => $this->string(256)->notNull(),
                'last_name' => $this->string(256)->notNull(),
                'corporate' => $this->string(16),
                'denomination' => $this->string(250),
                'vat' => $this->string(250)->notNull(),
                'address' => $this->string(250)->notNull(),
                'cap' => $this->string(250)->notNull(),
                'city' => $this->string(250)->notNull(),
                'country' => $this->string(250)->notNull(),
                'oauth_provider' => $this->string(20)->notNull(),
                'oauth_uid' => $this->string(128)->notNull(),
                'authKey' => $this->string(256)->notNull(),
                'accessToken' => $this->string(2048)->notNull(),
                'jwt' => $this->text()->notNull(),
                'picture' => $this->string(512)->notNull(),
                'privilege_id' => $this->integer()->notNull(),
                'is_active' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%np_users}}');
    }
}

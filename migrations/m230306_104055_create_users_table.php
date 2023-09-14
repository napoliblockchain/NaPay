<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m230306_104055_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(60)->notNull(),
            'email' => $this->string(60)->notNull(),
            'first_name' => $this->string(256)->notNull(),
            'last_name' => $this->string(256)->notNull(),
            'oauth_provider' => $this->string(20)->notNull(),
            'oauth_uid' => $this->string(128)->notNull(),
            'authKey' => $this->string(256)->notNull(),
            'accessToken' => $this->string(2048)->notNull(),
            'jwt' => $this->text()->notNull(),
            'picture' => $this->string(512)->notNull(),
            'privilege_id' => $this->integer()->notNull(),
            'is_active' => $this->integer()->notNull(),
            'merchant_id' => $this->integer()->defaultValue(null),
            'store_id' => $this->integer()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }
}

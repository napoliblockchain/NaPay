<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_consensus}}`.
 */
class m240315_142026_create_user_consensus_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_consensus}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'marketing' => $this->integer()->notNull()->defaultValue(0),
            'dati_personali' => $this->integer()->notNull()->defaultValue(0),
            'timestamp' => $this->integer()->notNull(),
        ]);


        $this->addForeignKey(
            'fk-user_consensus-user_id',
            'user_consensus',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_consensus}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%privileges}}`.
 */
class m230312_123913_create_privileges_table extends Migration
{
    /**
     * ### Ruoli utente dal README
     * 
     * 1 - Webmaster        ROLE_POS_WEBMASTER       50  => Full control su applicazione
     * 3 - Administrator    ROLE_POS_ADMIN           40  => Full control sui dati di tutti gli esercenti
     * 4 - Senior           ROLE_POS_SENIOR_VIEWER   30  => Visualizza tutti i propri negozi/pos/invoices
     * 2 - Junior           ROLE_POS_JUNIOR_VIEWER    0  => Visualizza solo il negozio/pos/invoice assegnati
     */

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%privileges}}', [
            'id' => $this->primaryKey(),
            'description' => $this->string(50)->notNull(),
            'level' => $this->integer()->notNull(),
            'codice_ruolo' => $this->string(50)->notNull(),
        ]);

        $this->insert('privileges', [
            'description' => 'Administrator',
            'level' => 50,
            'codice_ruolo' => 'ROLE_POS_ADMIN'
        ]);

        $this->insert('privileges', [
            'description' => 'Junior',
            'level' => 0,
            'codice_ruolo' => 'ROLE_POS_JUNIOR_VIEWER'
        ]);

        $this->insert('privileges', [
            'description' => 'Senior',
            'level' => 30,
            'codice_ruolo' => 'ROLE_POS_SENIOR_VIEWER'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%privileges}}');
    }
}

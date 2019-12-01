<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m190826_183942_start
 */
class m190826_183942_start extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(11),
            'username' => $this->string(64)->notNull(),
            'email' => $this->string(64)->notNull(),
            'displayname' => $this->string(64)->notNull(),
            'password' => $this->string(64)->notNull(),
            'authKey' => $this->string(64)->notNull(),
            'resetKey' => $this->string(64)->notNull(),
        ]);

    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190826_183942_start cannot be reverted.\n";

        return false;
    }
}
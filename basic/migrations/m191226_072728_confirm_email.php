<?php

use yii\db\Migration;

/**
 * Class m191226_072728_confirm_email
 */
class m191226_072728_confirm_email extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'confirm_token', $this->string(64));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191226_072728_confirm_email cannot be reverted.\n";

        return false;
    }
}

<?php

use yii\db\Migration;

/**
 * Class m191201_004903_perms
 */
class m191201_004903_perms extends Migration
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $admin = $auth->createPermission('admin');
        $admin->description = 'Admin';
        $auth->add($admin);

        $active = $auth->createPermission('active');
        $active->description = 'Active';
        $auth->add($active);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191201_004903_perms cannot be reverted.\n";

        return false;
    }
}

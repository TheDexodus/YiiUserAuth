<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

/**
 * Class RbacController
 */
class RbacController extends Controller
{
    /**
     * @return void
     * @throws \Exception
     */
    public function actionInit(): void
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
     * @throws \Exception
     */
    public function actionActive(): void
    {
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getPermission('active'), 2);
    }
}

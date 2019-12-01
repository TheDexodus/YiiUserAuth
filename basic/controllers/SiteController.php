<?php

namespace app\controllers;

use app\models\Auth;
use app\models\ChangePasswordForm;
use app\models\RegisterForm;
use app\models\RetrievePasswordForm;
use app\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['login', 'signup', 'logout'],
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['login', 'signup'],
                        'roles'   => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

            'auth' => [
                'class'           => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'oAuthSuccess'],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render(
            'login',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * @return Response|string
     */
    public function actionRetrievePassword()
    {
        $model = new RetrievePasswordForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->recovery();

            return $this->goHome();
        }

        return $this->render(
            'recovery',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * @param string|null $rec
     *
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionChangePassword(string $rec)
    {
        $user = User::findByResetKey($rec);

        if (null !== $user) {
            $model = new ChangePasswordForm();
            if ($model->load(Yii::$app->request->post()) && $model->change($user)) {
                return $this->goHome();
            }

            return $this->render(
                'changePassword',
                [
                    'model' => $model,
                ]
            );
        }

        throw new NotFoundHttpException();
    }

    /**
     * @return Response|string
     * @throws Exception
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render(
            'register',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * @param $client
     *
     * @throws Exception
     */
    public function oAuthSuccess(ClientInterface $client)
    {
        $userAttributes = $client->getUserAttributes();
        $id = ArrayHelper::getValue($userAttributes, 'id');
        $source = $client->getId();
        $email = ArrayHelper::getValue($userAttributes, 'email') ?? 'user' . sha1(microtime()).'@email.com';

        $auth = Auth::findOne(['source' => $source, 'source_id' => $id]);

        if (null !== $auth) {
            $user = $auth->user;
            Yii::$app->user->login($user, 3600 * 24 * 30);
        } else {
            if ($email !== null && null !== User::findOne(['email' => $email])) {
                Yii::$app->getSession()->setFlash(
                    'error',
                    [
                        printf('User with the same email as in %s account already exists but isn\'t linked to it. Login using email first to link it.', $client->getTitle()),
                    ]
                )
                ;
            } else {
                $user = new User(['scenario' => USER::SCENARIO_CREATE]);
                $user->email = $email;
                $user->displayname = $email;
                $user->username = $email;
                $user->generateResetKey();
                $user->generateAuthKey();
                $user->setPassword(Yii::$app->security->generateRandomString(6));

                if ($user->save()) {
                    $auth = new Auth(
                        [
                            'user_id'   => $user->id,
                            'source'    => $client->getId(),
                            'source_id' => (string) $id,
                        ]
                    );
                    if ($auth->save()) {
                        Yii::$app->user->login($user, 3600 * 24 * 30);
                    } else {
                        Yii::$app->getSession()->setFlash(
                            'error',
                            [
                                printf('Unable to save %s account', $client->getTitle()),
                            ]
                        )
                        ;
                    }
                } else {
                    Yii::$app->getSession()->setFlash(
                        'error',
                        [
                            printf('Unable to save user'),
                        ]
                    )
                    ;
                }
            }
        }
    }
}

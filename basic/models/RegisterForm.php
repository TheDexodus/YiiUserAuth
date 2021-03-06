<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Url;

/**
 * @property User|null $user This property is read-only.
 */
class RegisterForm extends Model
{
    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var User|bool
     */
    private $_user = false;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password', 'email', 'displayName'], 'required'],
            ['email', 'email'],
            ['username', 'validateUsername'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @param string     $attribute
     * @param null|array $params
     *
     * @return void
     */
    public function validatePassword(string $attribute, ?array $params): void
    {
        if (!$this->hasErrors()) {
            if (preg_match_all('/[a-z]/i', $this->password) < 2
                || preg_match_all('/[0-9]/', $this->password) < 2
                || preg_match_all("/[!#$%&'()*+,\\-.\\/:;<=>?@[\\\\\]^_`{|}~\"]/", $this->password) < 2
            ) {
                $this->addError($attribute, 'Incorrect password ');
            }
        }
    }

    /**
     * @param string     $attribute
     * @param null|array $params
     *
     * @return void
     */
    public function validateUsername(string $attribute, ?array $params): void
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[a-zA-Z0-9]+$/i', $this->username)) {
                $this->addError($attribute, 'Incorrect username');
            }
        }
    }

    /**
     * @return mixed
     *
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User(['scenario' => User::SCENARIO_REGISTER]);
        $user->loadDefaultValues();
        $user->username = $this->username;
        $user->displayname = $this->displayName;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateResetKey();
        $user->generateAuthKey();
        $user->generateConfirmToken();
        $this->_user = $user->save();

        $link = sprintf('%s/confirm-email?email=%s&confirmToken=%s', Url::base('http'), $user->email, $user->getConfirmToken());
        $text = sprintf('Confirm password: <a href="%s">%s</a>', $link, $link);

        Yii::$app->mailer->compose()
            ->setFrom(Yii::$app->params['senderEmail'])
            ->setTo($this->email)
            ->setSubject('Confirm password')
            ->setHtmlBody($text)
            ->send()
        ;

        return $this->_user;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return 'register';
    }
}

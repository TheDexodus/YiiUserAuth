<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * @property User|null $user This property is read-only.
 *
 */
class RegisterForm extends Model
{
    public $displayName;
    public $email;
    public $username;
    public $password;

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'email', 'displayName'], 'required'],
            ['email', 'email'],
            ['username', 'validateUsername'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
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

    public function validateUsername($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[a-zA-Z0-9]+$/i', $this->username)) {
                $this->addError($attribute, 'Incorrect username');
            }
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
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
        $this->_user = $user->save();

        $user->addPermission('active');

        return $this->_user;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
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

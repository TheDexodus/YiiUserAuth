<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property int        $id
 * @property string     $username
 * @property string     $email
 * @property string     $displayname
 * @property string     $password
 * @property string     $authKey
 * @property string     $resetKey
 * @property AuthItem[] $permissions
 */
class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_LOGIN    = 'login';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_CREATE = 'create';

    public function rules()
    {
        return [
            [['username', 'password', 'permissions'], 'required', 'on' => self::SCENARIO_LOGIN],
            [
                ['username', 'displayname', 'email', 'password'],
                'required',
                'on' => self::SCENARIO_REGISTER,
            ],
            [
                ['username', 'displayname', 'email', 'password', 'authKey', 'resetKey'],
                'required',
                'on' => self::SCENARIO_CREATE,
            ],
        ];
    }

    public function fields()
    {
        return [
            'displayname' => 'displayname',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    public static function findByResetKey($resetKey)
    {
        return static::findOne(['resetKey' => $resetKey]);
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     *
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->authKey = Yii::$app->security->generateRandomString();
    }

    /**
     * @return string
     */
    public function getResetKey()
    {
        return $this->resetKey;
    }

    /**
     * @param $resetKey
     *
     * @return bool
     */
    public function validateResetKey($resetKey)
    {
        return $this->getResetKey() === $resetKey;
    }

    public function generateResetKey()
    {
        $this->resetKey = Yii::$app->security->generateRandomString();
    }

    public function getPermissions()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'item_name'])
            ->viaTable('auth_assignment', ['user_id' => 'id'])
            ;
    }

    public function removePermission($permName): void
    {
        (new Query())->createCommand()
            ->delete('auth_assignment', ['user_id' => $this->getId(), 'item_name' => $permName])->execute()
        ;
    }

    public function removeAllPermissions(): void
    {
        (new Query())->createCommand()
            ->delete('auth_assignment', ['user_id' => $this->getId()])->execute()
        ;
    }

    public function addPermission($permName): void
    {
        (new Query())->createCommand()
            ->insert('auth_assignment', ['user_id' => $this->getId(), 'item_name' => $permName, 'created_at' => time()])
            ->execute()
        ;
    }

    public function delete()
    {
        $auths = Auth::findAll(['user_id' => $this->getId()]);
        foreach ($auths as $auth) {
            $auth->delete();
        }

        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }
}

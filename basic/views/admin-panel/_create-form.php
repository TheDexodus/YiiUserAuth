<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
/* @var $perms */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(['id' => 'register']); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'id' => 'register-username']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'id' => 'register-email']) ?>

    <?= $form->field($model, 'displayname')->textInput(['maxlength' => true, 'id' => 'register-displayname']) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'id' => 'register-password']) ?>

    <?= $form->field($model, 'permissions')->checkboxList(ArrayHelper::map($perms, 'name', 'name')) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

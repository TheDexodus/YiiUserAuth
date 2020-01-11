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

    <?php $form = ActiveForm::begin(['id' => 'update']); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'id' => 'update-username']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'id' => 'update-email']) ?>

    <?= $form->field($model, 'displayname')->textInput(['maxlength' => true, 'id' => 'update-displayname']) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'id' => 'update-password']) ?>

    <?= $form->field($model, 'authKey')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'resetKey')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'permissions')->checkboxList(ArrayHelper::map($perms, 'name', 'name')) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

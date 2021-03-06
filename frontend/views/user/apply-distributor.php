<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'user';
\Yii::$app->params['title'] = '加入喀客KAKE';
?>

<div class="content">
    <div class="form-group">
        <label>手机号码</label>
        <input class="form-control" ng-model="apply.phone">
    </div>
    <div class="form-group">
        <label>昵称</label>
        <input class="form-control" ng-model="apply.name" placeholder="32个字符内">
    </div>
    <input type="hidden" ng-model="apply.attachment">
    <div class="form-group" kk-ajax-upload="div#file" data-action="user/upload-avatar" data-callback="handleUpload">
        <label>头像图片文件</label>
        <div id="file" class="form-control">{{apply.tip}}</div>
        <p class="help-block">文件大小 ≤3MB</p>
    </div>
    <button class="btn btn-default" kk-tap="submitApply()">申请加入</button>
</div>
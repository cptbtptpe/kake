<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\helpers\Url;

AppAsset::register($this);

$params = \Yii::$app->params;

$controller = \Yii::$app->controller->id;
$action = \Yii::$app->controller->action->id;

$ngApp = empty($params['ng_app']) ? 'kkApp' : $params['ng_app'];
$ngCtl = empty($params['ng_ctrl']) ? null : (' ng-controller="' . $params['ng_ctrl'] . '"');

$title = empty($params['title']) ? $params['app_title'] : $params['title'];
$keywords = empty($params['keywords']) ? $params['app_keywords'] : $params['keywords'];
$description = empty($params['description']) ? $params['app_description'] : $params['description'];

$keywords = str_replace('"', '“', $keywords);
$description = str_replace('"', '“', $description);

$cover = empty($params['cover']) ? $params['frontend_source'] . '/img/logo.png' : $params['cover'];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html ng-app="<?= $ngApp ?>" lang="<?= \Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="keywords" content="<?= $keywords ?>">
    <meta name="description" content="<?= $description ?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= $title ?></title>
    <?php $this->head() ?>
</head>

<script type="text/javascript">
    var baseUrl = '<?= $params["frontend_url"];?>';
    var requestUrl = '<?= $params["frontend_url"];?>/?r=';
</script>

<body<?= $ngCtl ?>>

<!-- Loading -->
<div id="loading" class="kk-animate kk-show hidden">
    <div class="loading-bar loading-bounce kk-animate kk-t2b-show">
        <div class="in"></div>
        <div class="out"></div>
    </div>
</div>

<!-- Message -->
<div id="message" class="kk-animate kk-show hidden">
    <div class="message-bar kk-animate kk-t2b-show">
        <p class="message-box"></p>
    </div>
</div>

<!-- Hit -->
<div id="hit" class="hidden">
    <div class="hit-bar hit-bounce kk-animate">
        <div class="in"></div>
        <div class="out"></div>
    </div>
</div>

<!-- Menu -->
<div id="menu">
    <div class="triangle"></div>
    <div>
        <a href="<?= Url::to(['site/index']) ?>" class="hr">
            <img src="<?= $params['frontend_source'] ?>/img/site.svg"/>
            首页
        </a>
        <a href="<?= Url::to(['order/index']) ?>" class="hr">
            <img class="order-center" src="<?= $params['frontend_source'] ?>/img/order-center.svg"/>
            订单中心
        </a>
        <a href="tel:<?= Yii::$app->params['company_tel'] ?>">
            <img src="<?= $params['frontend_source'] ?>/img/phone.svg"/>
            咨询客服
        </a>
    </div>
</div>

<!-- Body -->
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>

<?php
$minDirectory = (YII_ENV == 'dev' ? null : '_min');
$suffix = (YII_ENV == 'dev' ? time() : VERSION);

$items = [
    'css',
    'js'
];
foreach ($items as $item) {
    $variable = 'source' . ucfirst($item);
    $register = 'register' . ucfirst($item) . 'File';

    if (is_null($this->context->{$variable}) || 'auto' == $this->context->{$variable}) {
        $source = "/{$item}{$minDirectory}/{$controller}/{$action}.{$item}";
        $this->{$register}($params['frontend_source'] . $source . "?version=" . $suffix);
    } elseif (is_array($this->context->{$variable})) {
        foreach ($this->context->{$variable} as $value) {
            if (strpos($value, '/') === 0) {
                $source = "{$value}.{$item}";
            } else {
                $source = "/{$item}{$minDirectory}/{$value}.{$item}";
            }
            $this->{$register}($params['frontend_source'] . $source . "?version=" . $suffix);
        }
    }
}
?>

<!-- Footer -->
<div class="hidden">
    <span ng-init="common()"></span>
    <span ng-init='wxSDK(<?= Yii::$app->wx->js->config([
        'hideMenuItems',
        'onMenuShareTimeline',
        'onMenuShareAppMessage'
    ]) ?>, "<?= $title ?>", "<?= $description ?>", "<?= $cover ?>")'></span>
</div>
</body>
<script>
    var _hmt = _hmt || [];
    (function () {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?0dbdcd4d413051d54182fbda00151c4a";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
</html>
<?php $this->endPage() ?>

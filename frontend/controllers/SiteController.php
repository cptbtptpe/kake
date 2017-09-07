<?php

namespace frontend\controllers;

use common\components\Helper;
use Yii;

/**
 * Site controller
 */
class SiteController extends GeneralController
{
    /**
     * Displays homepage.
     */
    public function actionIndex()
    {
        $result = Yii::$app->wx->group('官方推广');
        $this->dump($result);

        $this->sourceCss = null;
        $this->sourceJs = null; // ['/node_modules/vconsole/dist/vconsole.min'];

        $params = Yii::$app->params;

        // 焦点图
        $focusList = $this->listProductFocus($params['site_focus_limit']);
        $focusList = array_merge($focusList, $this->listAd(0, $params['site_ad_focus_limit']));
        $focusList = Helper::arraySort($focusList, 'sort', 'ASC');

        // 板块
        $plateList = $this->listPlate();

        // 闪购模块
        $flashSalesList = $this->listProduct(1, $params['site_sale_limit'], 0, [
            'manifestation' => 2
        ]);

        // banner 模块
        $bannerList = $this->listAd(1, $params['site_ad_banner_limit']);

        // 精品推荐
        list($standardHtml, $over) = $this->renderListPage(1);

        $producer = null;
        if ($channel = Yii::$app->request->get('channel')) {
            $producer = $this->getProducer(Helper::integerDecode($channel));
            if (!empty($producer)) {
                $this->seo(['title' => $producer['name']]);
            }
        }

        return $this->render('index', compact('focusList', 'plateList', 'flashSalesList', 'bannerList', 'standardHtml', 'over', 'producer'));
    }

    /**
     * ajax 获取下一页列表
     */
    public function actionAjaxList()
    {
        $page = Yii::$app->request->post('page');

        list($html, $over) = $this->renderListPage($page);
        $this->success(compact('html', 'over'));
    }

    /**
     * 渲染列表视图并返回 html
     *
     * @access private
     *
     * @param integer $page
     *
     * @return array
     */
    private function renderListPage($page)
    {
        $pageSize = Yii::$app->params['site_product_limit'];
        $list = $this->listProduct($page, $pageSize, DAY, [
            'manifestation' => 0
        ]);
        $content = $this->renderPartial('list', compact('list'));

        return [
            $content,
            count($list) < $pageSize
        ];
    }
}

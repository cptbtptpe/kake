<?php

namespace frontend\controllers;

use Yii;

/**
 * Detail controller
 */
class DetailController extends GeneralController
{
    /**
     * @var array 产品列表查询条件
     */
    public static $productListCondition = [
        'join' => [
            [
                'table' => 'product_package',
                'sub' => [
                    'select' => [
                        'product_id',
                        'min(price) AS price'
                    ],
                    'where' => [
                        ['product_package.bidding' => 1],
                        ['product_package.state' => 1]
                    ],
                    'group' => 'product_id'
                ],
                'left_on_field' => 'id',
                'right_on_field' => 'product_id'
            ],
            [
                'table' => 'attachment',
                'as' => 'cover',
                'left_on_field' => 'attachment_cover',
                'right_on_field' => 'id'
            ],
            [
                'table' => 'hotel',
                'left_on_field' => 'hotel_id',
                'right_on_field' => 'id'
            ],
            [
                'left_table' => 'hotel',
                'table' => 'hotel_region'
            ]
        ],
        'select' => [
            'product.id',
            'product.title',
            'product.attachment_cover',
            'product.sale_type',
            'product.sale_rate',
            'product.sale_from',
            'product.sale_to',
            'product.virtual_sales',
            'product.real_sales',
            'product_package.price',
            'cover.deep_path AS cover_deep_path',
            'cover.filename AS cover_filename',
            'hotel.name',
            'hotel_region.name AS region'
        ],
        'where' => [
            ['product.state' => 1]
        ],
        'order' => [
            'ISNULL(product.sort), product.sort ASC',
            'product.update_time DESC'
        ],
    ];

    /**
     * Displays detail.
     */
    public function actionIndex()
    {
        $this->sourceCss = null;
        $this->sourceJs = null;

        $detail = $this->getProduct(Yii::$app->request->get('id'));
        if (empty($detail)) {
            $this->error(Yii::t('common', 'product data error'));
        }

        $detail['slave_preview_url'] = array_merge($detail['cover_preview_url'], $detail['slave_preview_url']);
        if ($detail['min_price'] <= 0) {
            $this->error(Yii::t('common', 'product price error'));
        }

        $this->seo([
            'title' => '商品详情',
            'share_title' => $detail['title'],
            'description' => $detail['hotel_name'] . ' - ' . $detail['title'],
            'share_description' => $detail['hotel_name'] . ' - ' . $detail['title'],
            'share_cover' => $detail['slave_preview_url'][0]
        ]);

        return $this->render('index', compact('detail'));
    }

    /**
     * 选择套餐
     */
    public function actionChoosePackage()
    {
        $this->sourceCss = null;
        $this->sourceJs = ['detail/index'];

        $productId = Yii::$app->request->get('id');
        $packageList = $this->listProductPackage($productId);
        $contact = $this->service('order.get-last-order-contact', ['user_id' => $this->user->id]);

        $this->seo(['title' => '商品支付']);

        return $this->render('choose-package', compact('packageList', 'productId', 'contact'));
    }

    /**
     * 支付前处理
     */
    public function actionPrefixPayment()
    {
        // 联系人信息
        $contacts = Yii::$app->request->get('user_info');
        if (!is_numeric($contacts)) {
            $contacts = $this->service('order.add-contacts', [
                'real_name' => $contacts['name'],
                'phone' => $contacts['phone'],
                'captcha' => $contacts['captcha']
            ]);

            if (is_string($contacts)) {
                $this->error(Yii::t('common', $contacts));
            }
        }

        // 支付方式
        $paymentMethod = Yii::$app->request->get('payment_method');
        if (!in_array($paymentMethod, OrderController::$paymentMethod)) {
            $this->error(Yii::t('common', 'payment link illegal'));
        }

        $url = $this->createSafeLink([
            'product_id' => Yii::$app->request->get('product_id'),
            'package' => Yii::$app->request->get('package'),
            'order_contacts_id' => $contacts
        ], 'order/' . $paymentMethod, $paymentMethod == 'ali' ? false : true);

        return $this->redirect($url);
    }
}

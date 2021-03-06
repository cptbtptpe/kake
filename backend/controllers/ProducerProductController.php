<?php

namespace backend\controllers;

use common\components\Helper;
use Yii;

/**
 * 分销产品管理
 *
 * @auth-inherit-except front sort
 */
class ProducerProductController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerProduct';

    // 模型描述
    public static $modelInfo = '分销产品';

    public static $uid;

    /**
     * @inheritDoc
     */
    public function pageDocument()
    {
        return array_merge(parent::pageDocument(), [
            'add-my' => [
                'title_icon' => 'plus',
                'title_info' => '新增',
                'button_info' => '新增',
                'action' => 'add-my-form'
            ],
            'edit-my' => [
                'title_icon' => 'pencil',
                'title_info' => '编辑',
                'button_info' => '编辑',
                'action' => 'edit-my-form'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增分销产品',
                'value' => 'producer-product/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function myOperations()
    {
        $operations = self::indexOperations();
        $operations[0]['value'] = 'producer-product/add-my';

        return $operations;
    }

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '二维码',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => function ($record) {
                    $url = "${record['link_url']}&channel=${record['channel']}";

                    return [$url];
                },
                'level' => 'success',
                'icon' => 'qrcode'
            ],
            [
                'alt' => '排序',
                'level' => 'default',
                'icon' => 'sort-by-attributes',
                'type' => 'script',
                'value' => '$.sortField',
                'params' => function ($record) {
                    return [
                        'producer-product.sort',
                        $record['id'],
                        $record['sort']
                    ];
                },
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function myOperation()
    {
        $operation = self::indexOperation();
        $operation[0]['value'] = 'edit-my';
        $operation[1]['value'] = 'front-my';

        return $operation;
    }

    /**
     * @inheritDoc
     */
    public static function myFilter()
    {
        return [
            'product_id' => [
                'elem' => 'input',
                'equal' => true
            ],
            'title' => [
                'elem' => 'input',
                'title' => '产品',
                'table' => 'product'
            ],
            'name' => [
                'elem' => 'input',
                'title' => '酒店',
                'table' => 'hotel'
            ],
            'type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexFilter()
    {
        $filter = self::myFilter();
        $filter['username'] = [
            'elem' => 'input',
            'title' => '分销商',
            'table' => 'user'
        ];

        return $filter;
    }

    /**
     * @inheritDoc
     */
    public static function indexSorter()
    {
        return [
            'sort'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function myAssist()
    {
        return [
            'product_id' => 'code',
            'title' => [
                'title' => '产品',
                'max-width' => '180px'
            ],
            'name' => [
                'title' => '酒店',
                'max-width' => '180px'
            ],
            'type' => [
                'code',
                'info',
                'color' => [
                    0 => 'default',
                    1 => 'primary'
                ]
            ],
            'commission' => [
                'html',
                'title' => '分佣档次'
            ],
            'add_time' => 'tip',
            'update_time' => 'tip',
            'sort' => 'code',
            'state' => [
                'code',
                'info',
                'color' => [
                    0 => 'danger',
                    1 => 'info',
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        $assist = self::myAssist();
        $assist['username'] = [
            'title' => '分销商'
        ];

        return $assist;
    }

    /**
     * @inheritDoc
     */
    public static function editMyAssist()
    {
        return [
            'product_id' => [
                'readonly' => true,
                'same_row' => true,
                'label' => 2
            ],
            'select_product' => [
                'title' => false,
                'elem' => 'button',
                'value' => '选择产品',
                'script' => '$.showPage("product.list-producer", {state: 1})'
            ],
            'type' => [
                'elem' => 'select',
                'value' => 0
            ],
            'sort' => [
                'placeholder' => '大于零的整数，越小越靠前'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function addMyAssist()
    {
        return self::editMyAssist();
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        $assist = self::editMyAssist();
        $assist['producer_id'] = [
            'readonly' => true,
            'same_row' => true,
            'label' => 2
        ];
        $assist['select_producer'] = [
            'title' => false,
            'elem' => 'button',
            'value' => '选择分销商',
            'script' => '$.showPage("producer-setting.list", {state: 1})'
        ];

        return $assist;
    }

    /**
     * @inheritDoc
     */
    public function myCondition()
    {
        return [
            'join' => [
                ['table' => 'product'],
                [
                    'table' => 'user',
                    'left_on_field' => 'producer_id'
                ],
                [
                    'left_table' => 'product',
                    'table' => 'hotel'
                ]
            ],
            'select' => [
                'product.title',
                'hotel.name',
                'producer_product.*',
                'user.username'
            ],
            'where' => [['producer_id' => self::$uid]],
            'order' => [
                'producer_product.state DESC',
                'ISNULL(producer_product.sort), producer_product.sort ASC',
                'producer_product.update_time DESC'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function indexCondition($as = null)
    {
        $condition = $this->myCondition();
        unset($condition['where']);

        return array_merge(parent::indexCondition(), $condition);
    }

    /**
     * 我的分销产品
     *
     * @auth-pass-all
     * @return object
     */
    public function actionMy()
    {
        return $this->showList();
    }

    /**
     * 新增分销产品
     *
     * @auth-pass-all
     * @return object
     */
    public function actionAddMy()
    {
        return $this->showForm();
    }

    /**
     * @auth-pass-all
     */
    public function actionAddMyForm()
    {
        $post = Yii::$app->request->post();
        $post['producer_id'] = self::$uid;

        $this->actionAddForm([
            'fail' => $this->getControllerName('add-my'),
            'success' => $this->getControllerName('my')
        ], null, $post);
    }

    /**
     * 编辑分销产品
     *
     * @auth-pass-all
     * @return object
     */
    public function actionEditMy()
    {
        return $this->showFormWithRecord();
    }

    /**
     * 列表指定用户的可结算分销订单
     *
     * @access public
     *
     * @param integer $userId
     *
     * @return array
     */
    public function listLog($userId)
    {
        $log = $this->service(self::$apiGeneralList, [
            'table' => 'producer_log',
            'join' => [
                [
                    'table' => 'order',
                    'left_on_field' => 'id',
                    'right_on_field' => 'producer_log_id'
                ]
            ],
            'where' => [
                ['order.payment_state' => 1],
                ['order.state' => 1],
                ['producer_log.producer_id' => $userId],
                ['producer_log.state' => 1]
            ],
            'select' => [
                'producer_log.*',
                'order.id AS order_id',
            ],
            'size' => 0
        ]);

        $controller = $this->controller('producer-log');
        $log = $this->callMethod('sufHandleListBeforeField', $log, [$log], $controller);

        foreach ($log as $key => $item) {
            if (empty($item['sub_counter'])) {
                unset($log[$key]);
            }
        }

        return $log;
    }

    /**
     * @auth-pass-all
     */
    public function actionEditMyForm()
    {
        if (!empty($this->listLog(self::$uid))) {
            Yii::$app->session->setFlash('warning', '更改产品分佣策略前需确保无可结算分销订单');
            $this->goReference($this->getControllerName('my'));
        }

        $post = Yii::$app->request->post();
        $post['producer_id'] = self::$uid;

        $this->actionEditForm([
            'fail' => $this->getControllerName('edit-my'),
            'success' => $this->getControllerName('my')
        ], null, $post);
    }

    /**
     * 记录前置
     *
     * @auth-pass-all
     */
    public function actionFrontMy()
    {
        return parent::actionFront($this->getControllerName('my'));
    }

    /**
     * @inheritDoc
     */
    public function preHandleField($record, $action = null)
    {
        if (in_array($action, [
            'add',
            'add-my',
            'edit',
            'edit-my'
        ])) {
            $controller = $this->controller('product');
            $data = $this->callMethod('sufHandleField', [], [
                ['id' => $record['product_id']],
                'ajaxModalListProducer'
            ], $controller);

            if (empty($data['commission_data_' . ProductController::$type[$record['type']]])) {
                Yii::$app->session->setFlash('warning', '该产品没有设置该分佣类型');
                Yii::$app->session->setFlash('list', $record);
                $this->goReference('producer-product/' . $action);
            }
        }

        return parent::preHandleField($record, $action);
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        if (in_array($action, [
            'index',
            'my'
        ])) {
            $record = $this->createLinkUrl($record, 'product_id', function ($id) {
                return [
                    'detail/index',
                    'id' => $id
                ];
            });
            $controller = $this->controller('product');
            $data = $this->callMethod('sufHandleField', [], [
                ['id' => $record['product_id']],
                'ajaxModalListProducer'
            ], $controller);

            $key = $record['type'] ? 'commission_table_percent' : 'commission_table_fixed';
            $record['commission'] = isset($data[$key]) ? $data[$key] : null;
            $record['channel'] = Helper::integerEncode($record['producer_id']);
        }

        return parent::sufHandleField($record, $action, $callback);
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        parent::beforeAction($action);
        self::$uid = $this->user->id;

        return true;
    }
}

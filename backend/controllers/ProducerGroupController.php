<?php

namespace backend\controllers;

use backend\components\ViewHelper;
use common\components\Helper;

/**
 * 分销商分组
 *
 * @auth-inherit-except front
 */
class ProducerGroupController extends GeneralController
{
    // 模型
    public static $modelName = 'ProducerGroup';

    // 模型描述
    public static $modelInfo = '分销商分组';

    public static $ajaxModalListTitle = '选择分销商分组';

    public static $ajaxModalListRecordFilterValueName = 'id';

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增分销商分组',
                'value' => 'producer-group/add',
                'icon' => 'plus'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListOperations()
    {
        return [
            [
                'text' => '选定',
                'script' => true,
                'value' => '$.modalRadioValueToInput("radio", "producer_group_id")',
                'icon' => 'flag'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexOperation()
    {
        return array_merge(parent::indexOperation(), [
            [
                'text' => '新品微信消息推送',
                'type' => 'script',
                'value' => '$.alert("功能待完善")',
                'level' => 'info',
                'icon' => 'link'
            ],
            [
                'text' => '新增/编辑分销产品',
                'type' => 'script',
                'value' => '$.alert("功能待完善")',
                'level' => 'info',
                'icon' => 'link'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function indexFilter()
    {
        return [
            'name' => 'input',
            'state' => [
                'value' => parent::SELECT_KEY_ALL
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListFilter()
    {
        return self::indexFilter();
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'name' => [
                'code'
            ],
            'remark',
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function ajaxModalListAssist()
    {
        return self::indexAssist();
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'name' => [
                'placeholder' => '32个字以内'
            ],
            'remark' => [
                'elem' => 'textarea',
                'placeholder' => '可留空'
            ],
            'state' => [
                'elem' => 'select',
                'value' => 1
            ]
        ];
    }

    /**
     * 分销商分组列表 - 弹出
     *
     * @auth-same producer-group/index
     */
    public function actionAjaxModalList()
    {
        return $this->showList();
    }
}

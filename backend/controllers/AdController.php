<?php

namespace backend\controllers;

/**
 * 广告管理
 *
 * @auth-inherit-except front
 */
class AdController extends GeneralController
{
    // 模型
    public static $modelName = 'Ad';

    // 模型描述
    public static $modelInfo = '广告';

    /**
     * @var array Hook
     */
    public static $hookDateSectionDouble = [''];

    /**
     * @inheritDoc
     */
    public static function indexOperations()
    {
        return [
            [
                'text' => '新增广告',
                'value' => 'ad/add',
                'icon' => 'plus'
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
                'text' => '二维码',
                'type' => 'script',
                'value' => '$.showQrCode',
                'params' => ['link_url'],
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
                        'ad.sort',
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
    public static function indexFilter()
    {
        return [
            'type' => [
                'value' => parent::SELECT_KEY_ALL
            ],
            'target' => [
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
    public static function indexSorter()
    {
        return [
            'from',
            'to',
            'sort'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function indexAssist()
    {
        return [
            'type' => [
                'code',
                'info'
            ],
            'target' => [
                'code',
                'info'
            ],
            'remark',
            'from',
            'to',
            'sort' => 'code',
            'state' => [
                'code',
                'color' => 'auto',
                'info'
            ],
            'preview_url' => [
                'img' => [
                    'pos' => 'left'
                ],
                'width' => '128px',
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function editAssist($action = null)
    {
        return [
            'type' => [
                'elem' => 'select',
                'value' => 1
            ],
            'target' => [
                'elem' => 'select',
                'value' => 0,
                'tip' => [
                    '_self' => '当前窗口打开',
                    '_blank' => '新窗口打开',
                ],
            ],
            'url' => [
                'label' => 4,
                'tip' => [
                    '格式1' => 'site/index 表示网站域名 + ?r=site/index',
                    '格式2' => '以 http(s):// 开头的完整地址串',
                    '格式3' => '脚本代码，如：javascript:void(null)'
                ],
            ],
            'remark' => [
                'elem' => 'textarea',
                'placeholder' => '128个字以内'
            ],
            'from' => [
                'type' => 'datetime-local',
                'label' => 3,
                'tip' => [
                    'AM' => '上午',
                    'PM' => '下午'
                ]
            ],
            'to' => [
                'type' => 'datetime-local',
                'label' => 3,
                'tip' => [
                    'AM' => '上午',
                    'PM' => '下午'
                ]
            ],

            'attachment_id' => [
                'hidden' => true
            ],
            'old_attachment_id' => [
                'value_key' => 'attachment_id',
                'hidden' => true
            ],
            'preview_url' => [
                'elem' => 'img',
                'img_label' => 4,
                'upload_name' => 'upload'
            ],
            'upload' => [
                'type' => 'file',
                'tag' => 1,
                'rules' => [
                    'suffix' => 'jpg,jpeg,png',
                    'pic_sizes' => '750*160-500',
                    'max_size' => 512
                ],
                'preview_name' => 'preview_url',
                'field_name' => 'attachment_id'
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
    public function indexCondition($as = null)
    {
        return array_merge(parent::indexCondition(), [
            'join' => [
                ['table' => 'attachment']
            ],
            'select' => [
                'attachment.deep_path',
                'attachment.filename',
                'ad.*'
            ],
            'order' => [
                'ad.state DESC',
                'ISNULL(ad.sort), ad.sort ASC',
                'ad.update_time DESC'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function editCondition()
    {
        $condition = $this->indexCondition();
        unset($condition['order']);

        return $condition;
    }

    /**
     * @inheritDoc
     */
    public function sufHandleField($record, $action = null, $callback = null)
    {
        $record = $this->createAttachmentUrl($record, 'attachment_id');
        $record = $this->createLinkUrl($record, 'url');

        return parent::sufHandleField($record, $action);
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        $this->sourceJs = [
            'jquery.ajaxupload',
            '/node_modules/cropper/dist/cropper.min'
        ];
        $this->sourceCss = ['/node_modules/cropper/dist/cropper.min'];

        return parent::beforeAction($action);
    }
}

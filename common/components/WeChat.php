<?php

namespace common\components;

use yii;
use yii\base\Object;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

/**
 * WeChat SDK
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-12-21 10:09:41
 */
class WeChat extends Object
{
    /**
     * @var object SDK instance
     */
    public $app;

    /**
     * __constructor
     *
     * @access public
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $request = Yii::$app->request;

        if ($valid = $request->get('echostr')) {
            $signatureArray = [
                $config['token'],
                $request->get('timestamp'),
                $request->get('nonce')
            ];
            sort($signatureArray);

            if (sha1(implode($signatureArray)) == $request->get('signature')) {
                header('content-type:text');
                exit($valid);
            }
        }

        $config['payment']['cert_path'] = Yii::getAlias($config['payment']['cert_path']);
        $config['payment']['key_path'] = Yii::getAlias($config['payment']['key_path']);
        $config['oauth']['callback'] = Yii::$app->params['wechat_callback'];

        $this->app = new Application($config);

        parent::__construct();
    }

    /**
     * Listen message
     *
     * @param callable $replyEvent
     * @param callable $replyText
     *
     * @return object
     */
    public function listen($replyEvent, $replyText)
    {
        $this->server->setMessageHandler(function ($message) use ($replyEvent, $replyText) {

            $reply = null;
            switch ($message->MsgType) {

                case 'event':
                    $reply = call_user_func($replyEvent, $message);
                    break;

                case 'text':
                    $reply = call_user_func($replyText, $message);
                    break;
            }

            return $reply;
        });

        return $this->server->serve()->send();
    }

    /**
     * Set and get config
     *
     * @access public
     *
     * @param null $name
     * @param null $value
     *
     * @return object
     */
    public function config($name = null, $value = null)
    {
        $config = $this->app['config'];
        if (!empty($name) && !empty($value)) {
            $config->set($name, $value);
        }

        return $config;
    }

    /**
     * Auth
     *
     * @access public
     * @return void
     */
    public function auth()
    {
        $this->oauth->redirect()->send();
    }

    /**
     * Get user info
     *
     * @access public
     * @return array
     */
    public function user()
    {
        return $this->oauth->user()->getOriginal();
    }

    /**
     * Create order
     *
     * @access public
     *
     * @param array $params
     *
     * @return mixed
     */
    public function order($params)
    {
        $attributes = array_merge(['trade_type' => 'JSAPI'], $params);
        $order = new Order($attributes);

        $result = $this->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            return $result->prepay_id;
        }

        return $result;
    }

    /**
     * __getter
     *
     * @access public
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->app->{$name};
    }
}
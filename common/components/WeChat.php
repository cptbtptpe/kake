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

        if (method_exists($request, 'get') && $valid = $request->get('echostr')) {
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

        // Payment
        if (isset($config['payment']) && isset($config['oauth'])) {
            $config['payment']['cert_path'] = Yii::getAlias($config['payment']['cert_path']);
            $config['payment']['key_path'] = Yii::getAlias($config['payment']['key_path']);
            $config['oauth']['callback'] = Yii::$app->params['wechat_callback'];
        }
        $this->app = new Application($config);

        parent::__construct();
    }

    /**
     * Listen message
     *
     * @param array $fnArray
     *
     * @return object
     */
    public function listen($fnArray)
    {
        $this->server->setMessageHandler(function ($message) use ($fnArray) {

            $reply = null;
            $type = strtolower($message->MsgType);

            if (empty($fnArray[$type]) && $fnArray[$type] === false) {
                $function = 'reply' . ucfirst($type);
                $fnArray[$type] = [
                    $this,
                    $function
                ];
            }

            if ($fnArray[$type]) {
                $reply = call_user_func($fnArray[$type], $message);
            }

            return $reply;
        });

        return $this->server->serve()->send();
    }

    /**
     * Listen event
     *
     * @param object $message
     *
     * @return string
     */
    public function replyEvent($message)
    {
        $reply = null;
        switch (strtolower($message->Event)) {
            case 'subscribe' :
                $reply = 'welcome subscribe us.';
                break;

            case 'click' :
                switch ($message->EventKey) {
                    case 'key_code' :
                        $reply = 'do something.';
                        break;
                }
                break;
        }

        return $reply;
    }

    /**
     * Listen text
     *
     * @param object $message
     *
     * @return string
     */
    public function replyText($message)
    {
        // return 'you say: ' . $message->Content;
        return null;
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
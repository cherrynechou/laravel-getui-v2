<?php
namespace CherryneChou\GeTui;

use Illuminate\Support\Facades\Cache;
use CherryneChou\GeTui\Contracts\PushInterface;

require_once dirname(__FILE__) . '/Sdk/GTClient.php';
require_once dirname(__FILE__) . '/Sdk/request/push/GTPushMessage.php';
require_once dirname(__FILE__) . '/Sdk/request/push/GTNotification.php';
require_once dirname(__FILE__) . '/Sdk/request/push/GTSettings.php';
require_once dirname(__FILE__) . '/Sdk/request/push/GTStrategy.php';
require_once dirname(__FILE__) . '/Sdk/request/push/ios/GTIos.php';
require_once dirname(__FILE__) . '/Sdk/request/push/ios/GTAps.php';
require_once dirname(__FILE__) . '/Sdk/request/push/ios/GTAlert.php';
require_once dirname(__FILE__) . '/Sdk/request/push/ios/GTMultimedia.php';
require_once dirname(__FILE__) . '/Sdk/request/push/android/GTAndroid.php';
require_once dirname(__FILE__) . '/Sdk/request/push/android/GTThirdNotification.php';
require_once dirname(__FILE__) . '/Sdk/request/push/android/GTUps.php';

/**
 * Class GeTui
 * @package CherryneChou\GeTui
 */
class GeTui implements PushInterface
{
    /**
     * @var array|null
     */
    protected $config;

    /**
     * @var mixed
     */
    protected $domain_url = "https://restapi.getui.com";

    /**
     * @var mixed
     */
    protected $gt_appid;

    /**
     * @var mixed
     */
    protected $gt_appkey;

    /**
     * @var mixed
     */
    protected $gt_appsecret;

    /**
     * @var mixed
     */
    protected $gt_mastersecret;

    /**
     * GeTui constructor.
     * @param array|null $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
          $this->config = $config;

          if (empty($config)) {
              throw new \Exception('config is empty');
          }

          $this->config['gt_domainurl'] = $this->config['gt_domainurl'] ?? $this->domain_url;

          $this->gt_appid = $config['gt_appid'];
          $this->gt_appkey = $config['gt_appkey'];
          $this->gt_appsecret = $config['gt_appsecret'];
          $this->gt_mastersecret = $config['gt_mastersecret'];
    }

    /**
     * @return \GTClient|mixed
     */
    public function getInstance()
    {
        $sObject = Cache::get(config('getui.object_class'));
        if($sObject){
            $object = unserialize($sObject);
        }else{
            $object = new \GTClient($this->config['gt_domainurl'],  $this->gt_appkey, $this->gt_appid,  $this->gt_mastersecret);
            $sObject = serialize($object);
            Cache::forever(config('getui.object_class'), $sObject);
        }
        return $object;
    }

    /**
     * @param $msg   ['title' => "通知标题",'content' => "通知内容" , 'payload' => "通知去干嘛这里可以自定义"]
     * @param $to    ['device_cid' => "" , platform=""]   platform 1为ios  2为android
     * @return mixed
     * @throws \Exception
     */
    public function push($msg, $to)
    {
        if (empty($to['device_cid'])) {
            throw new \Exception('device_id not empty');
        }

        if (!isset($msg['content']) || !isset($msg['title']) || !isset($msg['payload'])) {
            throw new \Exception('content and title not empty');
        }

        $client = $this->getInstance();

        $title = $msg['title'];
        $content = $msg['content'];
        $payload = $msg['payload'];

        $push = new \GTPushRequest();
        $osn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $push->setRequestId((string)$osn);

        $set = new \GTSettings();
        $set->setTtl(3600000);

        $strategy = new \GTStrategy();
        $strategy->setDefault(\GTStrategy::STRATEGY_THIRD_FIRST);
        $set->setStrategy($strategy);
        $push->setSettings($set);

        $message = new \GTPushMessage();

        //厂商推送消息参数
        $channel = new \GTPushChannel();
        //通知
        $notify = new \GTNotification();
        $notify->setTitle($title);
        $notify->setBody($content);

        //安卓
        if($to['platform'] == 2){

            $gtAndroid = new \GTAndroid();

            $ups = new \GTUps();
            $thirdnotify = new \GTThirdNotification();

            if(is_array($payload)){
                $pj =  json_encode($payload,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }else{
                $pj = $payload;
            }

            $notify-> setPayload($pj);
            $thirdnotify->setTitle($title);
            $thirdnotify->setBody($content);
            $thirdnotify-> setPayload($pj);

            $package = config('getui.package_name');

            $intent = "intent:#Intent;launchFlags=0x14000000;action=android.intent.action.oppopush;component={$package}/io.dcloud.PandoraEntry;S.UP-OL-SU=true;S.title={$title};S.content={$content};S.payload={$pj};end";
            $notify->setClickType("intent");
            $notify->setIntent($intent);

            $thirdnotify->setClickType("intent");
            $thirdnotify->setIntent($intent);

            $notify->setIntent($intent);
            //透传 ，与通知、撤回三选一
            $message->setTransmission(json_encode($payload,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            //$message->setNotification($notify);
            //    $message->setRevoke($revoke);

            $upsback= $ups->setNotification($thirdnotify);
            //$upsback= $ups->setTransmission(json_encode($touchuan));//厂商透传

            $gtAndroid->setUps($ups);
            $channel->setAndroid($gtAndroid);
            $push->setPushMessage($message);
            $push->setPushChannel( $channel );
            $push->setCid($to['device_cid']);

        }else{

        }

        //处理返回结果
        return $client->pushApi()->pushToSingleByCid($push);
    }

    public function pushToApp($data=[])
    {

    }
}

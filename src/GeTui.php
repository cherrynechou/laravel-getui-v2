<?php
namespace CherryneChou\GeTui;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

require_once dirname(__FILE__) . '/getui/GTClient.php';
require_once dirname(__FILE__) . '/getui/request/push/GTPushMessage.php';
require_once dirname(__FILE__) . '/getui/request/push/GTNotification.php';

class GeTui implements PushInterface
{
    /**
     * @var array|null
     */
    protected $config;

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
    public function __construct(array $config = null)
    {
          $this->config = $config;

          if (empty($config)) {
              throw new \Exception('config is empty');
          }

          $this->gt_appid = $config['gt_appid'];
          $this->gt_appkey = $config['gt_appkey'];
          $this->gt_appsecret = $config['gt_appsecret'];
          $this->gt_mastersecret = $config['gt_mastersecret'];
    }


    public function getInstance()
    {
        $sObject = Cache::get('getuiClass');
        if($obj){
            $object = unserialize($sObject);
        }else{
            $object = new GTClient($this->config['gt_domainurl'],  $this->gt_appkey,  $this->gt_mastersecret);
            $sObject = serialize($object);
            Cache::rememberForever('getuiClass', $sObject);
        }

        return $object;
    }

    public function pushToOne($deviceId, array $data, $isNotice = true)
    {
        if (empty($deviceId)) {
            throw new \Exception('device_id not empty');
        }

        if (!isset($data['content']) || !isset($data['title'])) {
          throw new \Exception('content and title not empty');
        }

        $client = $this->getInstance();
        //设置推送参数
        $push = new \GTPushRequest();
        $push->setRequestId(strtoupper(Uuid::uuid4()->toString()));
        $message = new \GTPushMessage();
        $notify = new \GTNotification();
        $notify->setTitle($data['title']);
        $notify->setBody($data['content']);

        //点击通知后续动作，目前支持以下后续动作:
        //1、intent：打开应用内特定页面url：打开网页地址。2、payload：自定义消息内容启动应用。3、payload_custom：自定义消息内容不启动应用。4、startapp：打开应用首页。5、none：纯通知，无后续动作
        $notify->setClickType("none");
        $message->setNotification($notify);
        $push->setPushMessage($message);
        $push->setCid($deviceId);
        //处理返回结果
        return $api->pushApi()->pushToSingleByCid($push);
    }

    public function batchPush($deviceId, array $data)
    {

    }

    public function clearObject()
    {
        Cache::forget('getuiClass');
    }

}

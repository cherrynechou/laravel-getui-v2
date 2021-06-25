<?php
namespace CherryneChou\GeTui;


interface PushInterface
{
  public function push($deviceId, array $data);

  public function batchPush($deviceIds, array $data);

  public function pushToApp(array $data);
}

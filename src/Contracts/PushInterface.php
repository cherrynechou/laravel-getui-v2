<?php
namespace CherryneChou\GeTui\Contracts;

/**
 * Interface PushInterface
 * @package CherryneChou\GeTui\Contracts
 */
interface PushInterface
{
  public function push($msg, array $to);

  public function pushToApp(array $data);
}

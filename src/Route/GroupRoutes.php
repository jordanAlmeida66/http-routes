<?php
namespace HttpRoutes\Route;
use HttpRoutes\Route\GroupRouteMethods;

class GroupRoutes
{
  private $GroupRoutesMethods;

  public function add($routes)
  {
    $this->GroupRoutesMethods = new GroupRouteMethods($routes);
    return $this->GroupRoutesMethods;
  }

  public function getAllRoutes()
  {
    return $this->GroupRoutesMethods->getAllRoutes();
  }
}
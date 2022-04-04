<?php 
namespace HttpRoutes;
use HttpRoutes\Route\{GroupRoutes, RouteMethods};

class Route
{
  private $routes = [];
  private $group = null;

  public function set(string $http_method, string $uri)
  {
    $this->routes[] = new RouteMethods($uri, $http_method);
    return $this->routes[count($this->routes)-1];
  }

  public function group()
  {
    $this->group = new GroupRoutes;
    return $this->group;
  }

  public function getAllRoutes()
  {    
    return is_null($this->group) ? $this->routes : array_merge($this->routes, $this->group->getAllRoutes());
  }
}
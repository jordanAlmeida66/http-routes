<?php 
namespace HttpRoutes\Route;

class GroupRouteMethods
{
  protected $routes;

  public function __construct($routes)
  {
    $this->routes = $routes;
  }

  public function controller($controller, array $action)
  {
    foreach($this->routes as $key => $route) {
      $route->controller($controller, $action[$key]);
    }

    return $this;
  }

  public function path(string $path)
  {
    foreach($this->routes as $routes) {
      $routes->uri = $path.$routes->uri;
    }

    return $this;
  }

  public function getAllRoutes()
  {
    return $this->routes;
  }
}
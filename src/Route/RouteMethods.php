<?php 
namespace HttpRoutes\Route;

class RouteMethods
{
  public $uri;
  private $arr;
  private $name;
  private $http_method;

  public function __construct($uri, $http_method)
  {
    $this->uri = $uri; 
    $this->http_method = $http_method;
  }

  public function controller(string $controller, string $action) : self
  {
    $base_controller = "App\\Controller\\";

    $controller = preg_match('/\\\/', $controller) ? $controller : $base_controller.$controller;
    
    $this->arr['controller'] = ['controller' =>  $controller, 'action' =>$action];
    
    return $this;
  }

  public function name(string $name) : self
  {
    $this->name = $name;

    return $this;
  } 

  public function callback(callable $callback) : self
  {
    $this->arr['callback'] = $callback;

    return $this;
  }

  public function get(&$routes, &$routes_name)
  {
    $routes[$this->uri][$this->http_method] = $this->arr;
    
    if (!empty($this->name)) {
      $routes_name[$this->name] = preg_replace('/\/\{.*/', '' , $this->uri);
    }
  }
}
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
    $this->arr['middleware'] = [];
  }

  public function controller(string $controller, string $action, $absolutive_path = false) : self
  {
    $base_controller = "App\\Controller\\";

    $controller = $absolutive_path ? $controller : $base_controller.$controller;
    
    $this->arr['controller'] = ['controller' =>  $controller, 'action' => $action];
    
    return $this;
  }

  public function middleware(array $middlewares,  bool $prioridade = false, string $namespace = 'App\Middleware\\') : self
  {
    foreach ($middlewares as $class => $methods) {
      foreach ($methods as $key => $method) {
        if($prioridade){
          //Adciona o elemento no início do array
          array_unshift($this->arr['middleware'], [$namespace.$class => $method]);
        } else {
          //Adciona o elemento no final do array
          array_push($this->arr['middleware'], [$namespace.$class => $method]);
        }
      }
    }

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
    $uri = preg_replace('/^\//', '', $this->uri);
    $uri = preg_replace('/\/$/', '', $uri);
    $uri = "/{$uri}"; 

    $routes[$uri][$this->http_method] = $this->arr;
    
    if (!empty($this->name)) {
      $routes_name[$this->name] = preg_replace('/\/\{.*/', '' , $uri);
    }
  }
}
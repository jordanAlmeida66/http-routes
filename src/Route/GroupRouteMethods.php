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
    // $path = preg_replace('/^\//', '', preg_replace('/\/$/', '',$path) );
 
    foreach($this->routes as $routes) {
      $routes->uri = $path.$routes->uri;
    }

    return $this;
  }

  public function middleware(array $middlewares,  bool $prioridade = false, string $namespace = 'App\Middleware\\') : self
  {
    foreach($this->routes as $key => $routes) {
      $routes->middleware($middlewares, $prioridade, $namespace);
    }

    return $this;
  }


  public function getAllRoutes()
  {
    return $this->routes;
  }
}
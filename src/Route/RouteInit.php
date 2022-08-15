<?php
namespace HttpRoutes\Route;

use HttpRoutes\Exception\BootstrapException;

class RouteInit
{
  private $controller = [];
  private $callback;
  private $arguments = [];
  private $middleware = [];
  private $function = [];

  public function setController($controller, $action)
  {
    $this->controller['controller'] = $controller;
    $this->controller['action'] = $action;
  }

  public function setFunction(array $function)
  {
    foreach ($function as $key => $value) {
      $this->function[$key] = $value;
    }
  }

  public function setCallback(callable $callback)
  {
    $this->callback = $callback;
  }

  /**
   * Execulta os middlewares da rota
   * 
   * @param array $middlewares
   * @return void
   */
  public function setMiddleware(array $middleware)
  {
    $this->middleware = $middleware;
  }

  public function setArguments(array $arguments)
  {
    $this->arguments = $arguments;
  }

  public function run()
  {
    if (!empty($this->middleware)) {
      $this->execMiddleware($this->middleware);
    }

    if (!empty($this->controller)) {
      $controller = $this->controller['controller'];
      $action = $this->controller['action'];

      $this->execController($controller, $action);
    } else if (!is_null($this->callback)) {
      $this->execCallback($this->callback);
    } else {
      throw new BootstrapException("Nenhum Controller ou callback encontrado");
    }
  }

  private function execMiddleware(array $middlewares): void
  {  
    foreach ($middlewares as $key => $middleware) {

      $class = key($middleware);
      $action = current($middleware);
      $m = new $class;

      $r = $m->$action();
  
      if (is_array($r)) {
        header('Content-Type: application/json');
        die(json_encode($r));

      } else if (!is_null($r)) {
        die($r);
      }  
    }  
  }

  private function execCallback($callback) : void
  {
    $response = !empty($this->arguments) ? $callback($this->function, $arguments) : $callback($this->function);

    is_null($response) ? die() :'';

    if (is_array($response)) {
      header('Content-Type: application/json');
      die(json_encode($response));
    }
       
    die($response); 
  }

  private function execController($controller, $action) : void
  {
    $c = new $controller($this->function);
    $response = isset($this->arguments) ? $c->$action($this->arguments): $c->$action();

    is_null($response) ? die() :'';

    if (is_array($response)) {
      header('Content-Type: application/json');
      die(json_encode($response));
    }
       
    die($response); 
  } 
}
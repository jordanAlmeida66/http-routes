<?php
use HttpRoutes\{Route, Bootstrap};
use HttpRoutes\Exception\BootstrapException;

$route = new HttpRoutes\Route;

// ----------------- ROUTE -----------------
$route->set('get', 'foo')->callback(function($functions){
  $getUriByName = $functions['getUriByName'];
  $blog_uri = $getUriByName('blog');
  return "It's work!<br>Go to blog<a href= {$blog_uri}>Blog</a>";
});

$route->set('get', 'bar')->controller('Controller', 'action');

// ----------------- GROUP -----------------
$route->group()->add([
  $route->set('get', 'foo')->callback(function($functions){
    return "It's work!";
  })->name('foo'),

  $route->set('get', 'blog/{post_id}')->callback(function($functions, $params){
    $post_id = $params['post_id'];

    //search post by id...

    return "It's work!";
  })->name('blog'),

  $route->set('get', 'foo/bar')->callback(function($functions){

    return "It's work!";

  })->middleware(['Examples\Foo' => ['foo', 'foo2'], 'Examples\Bar' => ['bar']])->name('bar')
])->middleware(['Examples\Bar' => ['bar2'], 'Examples\Foo' => ['foo3']])->path('pages/');

// ------------------------------------------

try {
  //Iniciando a aplicação
  $app = new Bootstrap($route, getenv('URL_BASE'));

} catch (BootstrapException $e) {
  //Erros relacionados à rota
  http_response_code($e->getCode());
  if ($e->getCode() == 404 | $e->getCode() == 405) {
    die($e->getMessage());
  } else {
    die("Ocorreu um erro inesperado, lamentamos ocorrido.<br> Detalhes DEBUG: '{$e->getMessage()}'");
  }
}
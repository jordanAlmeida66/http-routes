<?php 
namespace HttpRoutes;
use HttpRoutes\Route;
use HttpRoutes\Route\RouteInit;
use HttpRoutes\Exception\BootstrapException;

class Bootstrap
{
  /** @var array Contem todas as rotas com seus respectivos atributos */
  private $routes = [];
  /** @var array Contem todas as rotas nomeadas */
  private $routes_name = [];
  /** @var string Url base do projeto */
  private $url_base;
  /** @var array funções que podem ser utilizados pelos controllers ou callback */
  private $functions = [];

  /**
   * Bob the constructor.
   * Inicializa o core, obtem todas as rotas e rotas nomeadas
   *
   * @param Routes $rotas Contem o objeto Routes que será utilizado para obter as rotas
   * @param string $url_base url base do projeto
   */
  public function __construct(Route $rotas, string $url_base)
  {
    $r = filter_var($url_base, FILTER_VALIDATE_URL);

    if (!$r) {
      throw new BootstrapException("url base inválida");
    }

    $this->url_base = preg_replace('/\/$/','',$r);

    foreach ($rotas->getAllRoutes() as $key => $params) {
      $params->get($this->routes, $this->routes_name);
    }

    $this->functions();
    $this->init();
  }

  private function functions() : void
  {
    //funcoes que podem ser utilizadas pelo controlador ou callback
    $this->functions['getUriByName'] =  function(string $route, $absolute = true) {    
      return array_key_exists($route, $this->routes_name) ? ($absolute ? $this->url_base : '').$this->routes_name[$route] : throw new BootstrapException("rota nomeada '{$route}' inesistente");
    }; 
  }

  private function init()
  {
    //PARSE URL BASE, SEPARANDO O ROTAS, SCHEMA, E O PATH
    $url_parse = parse_url($this->url_base); 
   
    //VERFICA SE A URL BASE POSSUI ALGUM PATH
    $path_url = $url_parse['path'] ?? $url_parse['host'];

    //URI SOLICITADA PELO USER
    $uri = $_SERVER['REQUEST_URI'];

    //PARSE DA URI, SEPRANDO PATH DE POSSIVEIS QUERIES
    $parse_uri = parse_url($uri);

    //PATH SOLICITADO PELO CLIENTE
    $path_uri = $parse_uri['path'];

    //obtendo o path digitado pelo cliente
    $path = str_replace($path_url, '', $path_uri);

    if ($path != '/') {
      $path = preg_replace("/\/$/", '', $path);
    }   

    //decodifica caracteres da url para seus respectivos  valores originas. ex: decodifca caracteres acentuados que são modificados na url
    $path = urldecode($path);
    
    
    //mapear as rota e substituir possiveis indices por regex
    $r = array_map(function($uri){ 
      //filtro de entrada para parametros adcionais
      $preg = preg_match_all('/\/\{\??[a-z\-\_0-9]+\}/i', $uri, $matches);
      $arr = [];

      //Possui argumentos opcionais
      if($preg) {
        foreach(current($matches) as $key => $value){
          
          $r = preg_match('/^\/\{\?.+\}$/i', $value);

          if ($r) {
            //argumento opcional     
            $arr[$value] = '(?:/([^/]+))?';
            //obs: validações e filtros devem ser realizados por conta própria
          } else {
            //argumento obrigatório
            $r1 = preg_match('/^\/\{.+\}$/i', $value);
            
            if ($r1) {
              //obs: validações e filtros devem ser realizados por conta própria
              $arr[$value] = '(?:/([^/]+))';
            }
          }          
        }

        foreach ($arr as $key => $value) {
          $uri = str_replace($key,$value, $uri);
        }
      }

      //escapando barras
      $uri = preg_replace('/\//', '\/', $uri);
      return $uri;
    }, array_keys($this->routes));

    //percorrer todas as rotas e verificar se a uri informado pelo usuario existe
    foreach ($r as $key => $pattern) {
      /*path : uri digitada pelo usuario
      * pathern : regex padrão das rotas
      * matches : rota n oprimeiro indice e parametros da rota forncido pelo usuario, se houver
      */
      $a = preg_match('/^'.$pattern.'$/', $path, $matches);
      
      if($a) {
        //uri encontrada

        //obtendo a nome da rota através do id
        $rota = array_keys($this->routes)[$key];

        //agrs da rota
        $is_args = preg_match_all('/\/\{\??([a-z\-\_0-9]+)\}/i', $rota, $params_route);

        $route_init = new RouteInit;
        $route_init->setFunction($this->functions);

        if($is_args){
          $params_route = $params_route[1];
          //a rota possui parametros adcionais

          //remover o uri completa, preservar somente os paramtros, se houver
          unset($matches[0]);

          //parametros adcionais informado pelo user
          $params_in = array_values($matches);

          //unindo os valores passados pelo usuario à suas respectivas chaves  
           $params_in = array_map(function($value){
            return  is_null($value) ? '' : $value;
          }, $params_in, $params_route);

          //argumento da rota de acordo com o id informado em 'routes.php'
          $args = array_combine($params_route, $params_in);
          $route_init->setArguments($args);
        }   

        //verificar o método
        $methods = $this->routes[$rota];

        foreach ($methods as $method => $att) {

          $http_method = $_SERVER['REQUEST_METHOD'];
          
          if ($http_method == strtoupper($method)) {
            

            if (isset($att['middleware'])) {
              //executar os middlewares
              $route_init->setMiddleware($att['middleware']);
            }
            
            if (isset($att['callback'])) {
              $route_init->setCallback($att['callback']);
            } else if (isset($att['controller']) ) {
             $route_init->setController($att['controller']['controller'], $att['controller']['action']); 
            }

            $route_init->run();
          }
        }
        
        //method not allowed
        throw new BootstrapException('Method Not Allowed', 405);
      }
    }

    //pagina não encontrada
    throw new BootstrapException('Not Found', 404);
  } 
}
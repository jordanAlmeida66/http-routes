# http-routes @jordan

## Sobre
  http-routes é um roteador http que identifica as rotas passadas via url e responde às solicitações por meio de controllers ou callback.

## Créditos

- [Jórdan Almeida](https://github.com/jordanAlmeida66) (Developer)

## Highlights

- Fácil integração com a arquitetura MVC
- Sem dependencias
- Uri intuitiva para o usuário
- Middleware
- Aceita parâmetros opcionais e obrigatórios
- Símples e fácil de usar

## Instalação via composer

```bash
composer jordan/http-routes dev-master
```

## Começando

Crie um arquivo que será acessado pelo usuário em seu site. recomenda-se que seja criado um "index.php" pois o servidor irá chamá-lo automaticamente ao acessar a raiz do seu site.

No arquivo criado, inicie o autoload, e logo em seguida crie um arquivo que será responsável por iniciar uma instância de Routes e Bootstrap.

Em "index.php":

```php
//index.php
require_once "/vendor/autoload.php";
require_once "foo/bar/arquivo-rotas-exemplo.php";
```

Em "arquivo-rotas-exemplo.php":

```php
//arquivo-rotas-exemplo.php
use HttpRoutes\{Route, Bootstrap};
use HttpRoutes\Exception\BootstrapException;

$route = new Route;


//insira suas rotas aqui


try {
  //iniciando a aplicação
  $app = new Bootstrap($route, "http:\\url-base-projeto");

} catch (BootstrapException $e) {
  //erros relacionados à rota
  http_response_code($e->getCode());
  if ($e->getCode() == 404 | $e->getCode() == 405) {
    //erros causado pelo usuário
    die($e->getMessage());
  } else {
    //erros internos
    die("Ocorreu um erro inesperado, lamentamos ocorrido.<br> Detalhes DEBUG: '{$e->getMessage()}'");
  }
}
```

## Métodos

Os exemplos dos métodos abaixo irão considerar que você já fez os passos acima. Todos os métodos devem ser chamados através da instância de "HttpRoutes\Route".

```php
$route
->method();

$route
->method1()
->method2('foo', 'bar');
```
### Métodos de rota

#### Criando uma rota 

Para criar uma rota, basta chamar o método "set", o primeiro argumento será preenchido com o método http da rota, e o segundo com o endpoint desejado.

**obs: Barras no início e no final dos endpoints serão ignoradas; Nome de métodos http serão convertidos para maiusculos.**

```php
->set('get', 'foo');

->set('post', '/bar');
```
**obs: Esta função retorna a instância de "HttpRoutes\Route\RouteMethods", classe que contém os métodos individuais para cada rota.**

#### Argumentos de rota

As rotas podem aceitar argumentos adcionais que podem ser preenchidos pelo usuário no momento de solicitação(request). Esses parâmetros são classificados como "obrigatórios" e "opcionais".

Argumentos devem ser escritos entre chaves "{}", sendo que argumentos opcionais devem ser antecedidos por um sinal de interrogação "{?}".

**obs: Um argumento obrigatório pode ser seguido por um opcional, mas o oposto não funciona muito bem. Utilize apenas a sequencia recomendada.**

```php
->set('get', 'foo/{obrigatório}');//Correto

->set('post', 'foo/{obrigatório}/{?opcional}');//Correto

->set('DELETE', 'foo/{?opcional}/{obrigatório}');//Essa sequência não funciona muito bem

->set('GET', 'foo/{?opcional}/');//Correto
```

Argumentos passados nas rotas serão lançados automaticamente aos métodos responsáves por responderem às solictações em caso de sucesso. Veja abaixo os tópicos "Callback" e "Controller" para mais detalhes.

#### Nomeando uma rota

Nomear uma rota pode ser útil em casos em que a rota precise ser socilitada em muitos locais da aplicação. A partir do nome da rota é possivel obter seu endereço, isso permite que qualquer alteração na rota possa ser obtida em todos os locais que a invocam, sem a necessidade de altereção manual, uma vez que o nome permanece o mesmo e somente o endereço que sofre possíveis alterações.

```php
->name('nome-da-rota');
```

##### Obtendo uma rota nomeada

O método responsável por obter as rotas a partir do seu respectivo nome estará disponível em ambos os métodos "controller" e "callback". 

A função espera dois parâmetros, o primeiro deverá ser preenchido com o nome da rota e o segundo com um valor boleano. Sendo "true", valor padrão, o método retornará a rota antecedida da url base. ex: http://meu-site/rota; Caso "false", o método retornará somente a rota nomeada. ex: /rota.

**obs: Caso a rota nomeada não exista, será lançada uma exceção em "BootstrapException" informando que não foi encontrada nenhuma rota correspondente ao nome.**

```php
$getUriByName('Nome-da-rota', true); //O método retornará a rota antecedida da url base. ex: http://meu-site/rota
$getUriByName('Nome-da-rota', false); //O método retornará somente a rota nomeada. ex: /rota
```

#### Callback
  
O método "callback" espera que uma função anônima seja passada como parâmetro; Essa função será executada como resposta à uma solicitação http em caso de sucesso.

A função anônima passada precisa está pronta para receber em seu primeiro parâmetro um array contendo funções passadas pela aplicação. Por enquanto há apenas uma unica função de nome "getUriByName" que é responsável por obter rotas a partir de seus respectivos nomes caso essas tenham sido nomeadas(Consulte o tópico "Obtendo uma rota nomeada" para mais detalhes). Esse parâmetro é obrigatório mesmo em caso de não utilização das funções ao longo do contexto.

```php
->callback(function($functions){
  return "Resposta a ser retornada";
});

->callback(function($functions){
  $getUriByName = $functions['getUriByName'];
  return $getUriByName('Nome-da-rota'); 
});
```
##### Callback e Argumentos de rota

Para obter argumentos passados via url no momento da solicitação feita por um usuário, basta passar uma variável como segundo parâmetro da função anônima que será preenchida com um array associativo(chave/valor). As chaves serão representadas pelos nomes de cada argumento e conterá seus respectivos valores passados pelo usuário no momento da requisição. Para argumentos opcionais, caso nenhum valor seja informado o valor da chave será nulo(ex: argumento1=NULL).

```php
->callback(function($functions, $params){
  return $params['obrigatório'];
});

->set('get', 'bar/{?opcional}')
->callback(function($functions, $params){
  return !is_null($params['opcional']) ? "parâmetro opcional: {$params['opcional']}" : 'nenhum parâmetro opcional foi informado';
});
```
### Controller

Em MVC os controlladores são responsáveis por atender às socitacitações http enviando-as uma resposta. Você pode optar por criar controladores para cada rota ou conjunto de rotas.

Com o método Controller é possivel informar o controlador e seu métodos que serão responsáveis por mediar tais solicitações. Não se preocupe, basta informar um namespace que o Core da aplicação irá instanciá-lo para você

```php
->controller('Controller', 'action', false)//false = Defaut; O nome do controller será antecedido do namespace App\Controller\
->controller('Foo\Bar\Controller', 'action', true)//O nome do controller será interpretado como namespace completo
```

#### Classe Controller

Toda classe Controller deve possuir um construtor que em seu primeiro parâmetro será passad um array contendo uma lista de funções geradas pelo Core da aplicação, Para acessar as funções basta acessar o indice do array. Atualmente apenas a função "getUriById" está disponível.

```php
//Controller.php
namespace App\Controller;

Class Controller{
  public function __construct($functions)
  {

  }

  public function action()
  {
    return 'done';
  }
}
```
```php
//Controller.php
namespace Foo\Bar;

Class Controller{
  public function __construct($functions)
  {

  }

  public function action()
  {
    return 'done';
  }
}
```
#### Argumentos de rota em Controller

Argumentos de rota serão enviados em forma de array para o primeiro métodos do controlador correspondente à rota. Para isso o método deve está pronto para receber tais valores. 

**obs: Para mais detalhes sobre argumentos de rotas visite o tópico "Argumentos de rota".**

```php
//Controller.php
namespace App\Controller;

Class Controller{
  public function action($args)
  {
    echo '<pre>';
    print_r($args);
    echo '</pre>';
  }
}
```
### Middleware

Os Middlewares compôem instrução que serão executadas antes dos controlladores ou callback, os middlewares são uteis para validação de dados, e a depender dessa resultado pode-se tomar uma decisão.

Cada método de um middleware deve retornar nulo em caso de sucesso, caso algum valor diferente de nulo seja retornado, a aplicação irá encerrar seu ciclo e exibir como resposta o retorna da função do middleware.

#### Declarando um Middleware

Para delcarar um middleware basta chamar a função "middleware" que espera receber um array como primeiro argumento, onde o indice deve corresponder à classe do middleware, e seu valor um array contendo um ou mais métodos da classe. Um segundo argumento opcional pode ser informado contento o namespace do middleware, o namespace padrão é "App\Middleware".

```php
->middleware(['MiddlewareClass' => ['action'] ]);
->middleware(['MiddlewareClass' => ['action'] ], 'App\Mynamespace\\');
->middleware(['MiddlewareClass' => ['action1', 'action2'] ]);
->middleware(['MiddlewareClass1' => ['action1', 'action2'], 'MiddlewareClass2' => ['action1'] ]);
```

#### Classe Middleware

É necessário criar uma classe que deve conter um ou mais métodos. cada método deverá conter uma logica de acordo com sua necessidade e retornar uma resposta.

```php
Class Middleware
{
  public function action()
  {
    //Validando...

    //Se nenhum valor for retornado, a aplicação receberá um valor nulo como resposta. é facultativo o uso de "return null".

    //Caso algum valor seja retornado, além de nulo, o fluxo da aplicação será encerrado e o retorno será exbido como resposta ao usuário.
  }
}
```
### Métodos de grupo de rotas

#### Agrupamento de rotas

Agrupar rotas pode ser útil para diminuir a repetição d comandos em seu arquivo, como o agrupamento de rotas é possível executar uma mesma função em rotas as rotas do grupo, para utilizar essa funcionalidade basta chamar o método "group".

#### Group métodos

Para iniciar um grupo de rotas basta chamar a função "group". 

```php
$route->group()
```
Após declaração do método, a função "add" deverá ser chamada em sequencia e após ela os demais métodos podem ser utilizados.

##### Adcionando rotas ao grupo

Para adcionar uma ou mais rotas ao grupo basta chamar o método "add" e passar um array de rotas em seu como argumento. Você pode utilizar todos os métodos das rotas normalemnte dentro de um grupo, a unica exeção é que atualmente não é possível chamar um grupo dentro de um grupo.

```php
->add([
  $route->set('get', 'foo')->controller('ControllerClass', 'action')->name('foo-route'),
  $route->set('post', 'bar')->controller('ControllerClass', 'action')->name('bar-route')
]);
```
**obsEsta função retorna uma instancia de "GroupRoutesMethods" contendo todos os métodos de grupos de rotas que podem ser utilizados.**


##### Especificando um path base para todas as rotas

Você pode especificar um path no qual irá anteceder todas as rotas do grupo, para isso chame o método "path".

```php
->path('foo/bar/');
```

##### Controller

Um grupo pode pertencer a uma mesma classe. os métodos do controller devem ser informados em forma de array na ordem de disposição das rotas dentro do grupo.

```php
->controller("ControllerClass", ['action1', 'action2']);
```
##### Middleware

A utilização desta função se dá da mesma forma que é utlizada nas rotas individualmente.
Os middlewares são executandos sempre em ordem de definição, primeiro são executados os middlewares indivíduais de cada rota, por utimo são execuytados os middlewares do grupo.

```php
->middleware(['Examples\Bar' => ['action'], 'Examples\Foo' => ['action']]);
```
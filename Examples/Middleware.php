<?php 
namespace Foo\Bar;

class Middlware
{
  public function action()
  {
    //return false caso queira continuar o fluxo
  }

  public function is_autenticate()
  {
    return "você não está autenticado";
    //o fluxo será interrompido
  }
}
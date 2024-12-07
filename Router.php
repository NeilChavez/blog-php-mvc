<?php

namespace MVC;

class Router
{

  private $routesGet = [];
  private $routesPost = [];


  public function get($url, $fn)
  {
    $this->routesGet[$url] = $fn;
  }

  public function post($url, $fn)
  {
    $this->routesPost[$url] = $fn;
  }


  public function checkRoutes()
  {

    $method = $_SERVER["REQUEST_METHOD"];
    $ulrWithoutParams = strtok($_SERVER["REQUEST_URI"], "?");
    $url =  $ulrWithoutParams === "/" ? "/home" :  $ulrWithoutParams;
    $fn = "";

    if ($method === "GET") {
      $fn = $this->routesGet[$url] ?? null;
    }
    if ($method === "POST") {
      $fn = $this->routesPost[$url] ?? null;
    }

    if ($fn) {
      call_user_func($fn, $this);
    } else {
      echo "NO ROUTE FOUNDED";
    }
  }

  public function render($view, $args = [])
  {
    foreach ($args as $key => $value) {
      $$key = $value;
    }

    ob_start();
    include __DIR__ . "/views/pages/$view.php";
    $content = ob_get_clean();
    include __DIR__ . "/views/layout.php";
  }
}

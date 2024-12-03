<?php

namespace MVC;

class Router
{

  private $routesGet = [];


  public function get($url, $fn)
  {
    $this->routesGet[$url] = $fn;
  }


  public function checkRoutes()
  {

    $method = $_SERVER["REQUEST_METHOD"];
    $url = strtok($_SERVER["REQUEST_URI"], "?") ?? "/";
    $fn = "";
    if ($method === "GET") {
      $fn = $this->routesGet[$url] ?? null;
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

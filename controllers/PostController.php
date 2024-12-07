<?php

namespace Controller;

use Model\Post;
use MVC\Router;

class PostController
{
  public static function create(Router $router)
  {
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      
      $_POST["content"] = trim($_POST["content"]);
  
      $post = new Post($_POST);


     $errors =  $post->getErrors();

      if (empty($errors)) {

        $post->create();
      }

    }

    $router->render("/posts/create", [
      "post" => $post,
      "errors" => $errors
    ]);
  }
}

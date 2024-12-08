<?php 
namespace Controller;
use MVC\Router;
use Model\Post;

class PageController
{

  public static function index(Router $router)
  {
    $posts = Post::getAll();
 
    $router->render("/home", [
      "posts" => $posts
    ]);
  }

  public static function singlePost(Router $router)
  {

    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if (!$id)
      exit;
     
    $post = Post::findById($id);

    $router->render( "/post",[
      "post" => $post
    ]);

  }
}
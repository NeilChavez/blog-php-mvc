<?php

namespace Controller;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
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

      $tempFile = $_FILES["feature_image"]["tmp_name"];

      if (!empty($tempFile)) {

        // create a manager with a driver
        $manager = new ImageManager(Driver::class);

        $image = $manager->read($tempFile);

        $imagesDirectory = $_SERVER["DOCUMENT_ROOT"] . "/images/";
        
        // check if there is a directory images. If is not, create a directory
        if (!is_dir($imagesDirectory)) {

          mkdir($imagesDirectory);
        }

        $imageName = md5(uniqid(rand(), true)) . ".jpeg";

        $imagepath = $imagesDirectory . $imageName;

        $image->save($imagepath);

        $post->feature_image = $imageName;

      }

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

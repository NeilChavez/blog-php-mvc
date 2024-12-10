<?php

namespace Controller;

use Model\Post;
use MVC\Router;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostController
{

  public static function admin(Router $router)
  {
    $posts = Post::getAll();

    $router->render("/posts/admin", [
      "posts" => $posts
    ]);
    
  }
  public static function create(Router $router)
  {
    $post = new Post();
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      $_POST["content"] = trim($_POST["content"]);

      $post = new Post($_POST);

      $tempFile = $_FILES["feature_image"]["tmp_name"];

      //user put a new image to upload
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

        // there is a image in the hidden input, so user we delete it
        if (!empty($_POST["feature_image"])) {

          $previusImage = $_POST["feature_image"];
          unlink($imagesDirectory . $previusImage);
        }
      }
      // user hasn't uploaded a new image, we have one in the input hidden
      if (empty($tempFile) && !empty($_POST["feature_image"])) {

        $post->feature_image = $_POST["feature_image"];
      }

      $errors =  $post->getErrors();

      if (empty($errors)) {

        $result = $post->create();

        if ($result) {
          header("Location: /post/create?message=1");
        }
      }
    }

    $router->render("/posts/create", [
      "post" => $post,
      "errors" => $errors
    ]);
  }
}

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

  public static function update(Router $router)
  {
    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if (!$id) {
      header("Location: /");
    };

    /** @var \Model\Post $post **/
    $post = Post::findPostById($id);
    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

      $args = $_POST;

      // we take the new values comming from the $_POST, we need to update the properties in the object
      $post->sincronize($args);

      $image = $_FILES["feature_image"]["tmp_name"];

      if (!empty($image)) {

        $manager =  new ImageManager(Driver::class);

        $image = $manager->read($_FILES["feature_image"]["tmp_name"]);

        $dirImages = $_SERVER["DOCUMENT_ROOT"] . "/images/";

        if (!is_dir($dirImages)) {
          mkdir($dirImages);
        }

        $nameImage = md5(uniqid(rand(), true)) .".jpeg";

        $pathImage = $dirImages . $nameImage;

        $image->save($pathImage);

        $post->feature_image = $nameImage;
        
        // if we have a hidden input with a value image, it means the user has subtitued this image with the one above. So we need to remove from the server
        if ($_POST["feature_image"]) {

          $pathPreviousImage = $dirImages . $_POST["feature_image"];

          file_exists($pathPreviousImage) && unlink($pathPreviousImage);
        }
      }

      $errors = $post->getErrors();

      if (empty($errors)) {

        $result = $post->update($id);

        if ($result) {

          header("Location: /posts/admin?message=2");

        } else {

          echo "Something went wrong";

        }
      }
    }

    $router->render("/posts/update", [
      "post" => $post,
      "errors" => $errors
    ]);
  }
}

<?php

namespace Model;

class Post extends ActiveRecord
{
  protected static $table = "posts";
  private static $columns = ["post_id", "title", "feature_image", "content", "status", "created_at", "updated_at", "user_id"];

  private static $manyToOne = "users";

  public $post_id;
  public $title;
  public $feature_image;
  public $content;
  public $status;
  public $created_at;
  public $updated_at;
  public $user_id;

  private $errors = [];

  public function __construct($parametres = [])
  {

    $this->post_id = $parametres["post_id"] ?? null;
    $this->title = $parametres["title"] ?? "";
    $this->feature_image = $parametres["feature_image"] ?? "";
    $this->content = $parametres["content"] ?? "";
    $this->status = $parametres["status"] ?? null;
    $this->created_at = $parametres["created_at"] ?? date("Y-m-d H:i:s");
    $this->updated_at = $parametres["updated_at"] ?? null;
    $this->user_id = $parametres["user_id"] ?? 1;
  }

  public function getErrors()
  {
    if (!$this->title) {
      $this->errors["title"] = "Your need to insert a title";
    }
    if (!$this->content) {
      $this->errors["content"] = "Your need to insert a content";
    }

    if (!$this->feature_image) {
      $this->errors["feature_image"] = "Your need to insert an image";
    }

    return $this->errors;
  }


  public static function getAll()
  {

    return self::select();
  }

  public static function findPostById($id): Post
  {
    return self::findById($id);
  }

  public function removePost()
  {
    $this->removeImage();
    return $this->delete();
  }
  public function getImage()
  {
    return $this->feature_image;
  }

  public function setImage($image)
  {

    // remove previous image from the server when there is one
    $this->removeImage();

    //And set et new image name to the property feature_image
    $this->feature_image = $image;
  }
  public function removeImage()
  {
    $pathImages = $_SERVER["DOCUMENT_ROOT"] . "/images/";
    $previousImage = $this->getImage() ?? "";

    //if this file exists then remove it
    if (is_file($pathImages . $previousImage)) {

      unlink($pathImages . $previousImage);
    }
  }
}

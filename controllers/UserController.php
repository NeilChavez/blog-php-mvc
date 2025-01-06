<?php

namespace Controller;

use Model\User;
use MVC\Router;

class UserController
{
  public static function readUser(Router $router)
  {

    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if (!$id) {
      header("Location: /home");
      exit;
    }

    $user = User::findUserBy("user_id", $id);

    $router->render("/user-profile", [
      "user" => $user
    ]);
  }

  public static function allUsers(Router $router)
  {
    $users = User::select();

    $router->render("/users/all-users", [
      "users" => $users
    ]);
  }
  public static function update()
  {
    $user = new User();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      session_start();
      if (!isset($_SESSION["email"])) {

        header('Location: /login');
        exit;
      }

      $userEmail = $_SESSION["email"];

      /** @var \Model\User $user **/
      $user = User::findUserBy('email', $userEmail);

      $args = [];

      if ($user->role === "admin") {

        $args = [
          "username" => $_POST["username"],
          "email"    => $_POST["email"],
          "avatar"   => $_POST["avatar"]
        ];
      } else {

        $args = [
          "username" => $_POST["username"],
          "avatar"   => $_POST["avatar"]
        ];
      };

      $user->sincronize($args);

      $user->update();
    }
  }
}

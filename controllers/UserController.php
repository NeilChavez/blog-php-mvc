<?php

namespace Controller;

use Model\User;
use MVC\Router;

class UserController
{
  public static function readUser(Router $router)
  {

    session_start();

    $emailUser = $_SESSION["email"];

    $user = User::findUserBy("email", $emailUser);

    $router->render("/user-profile", [
      "user" => $user
    ]);
  }
}

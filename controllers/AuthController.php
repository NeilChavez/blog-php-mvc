<?php

namespace Controller;

use Model\User;
use MVC\Router;

class AuthController
{
  static public function signUp(Router $router)
  {
    $errors = [];
    $user = new User();
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      // sanitize the data
      $password = $_POST["password"];
      $email = $_POST["email"];

      $emailSanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
      $isTrustedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

      if ($emailSanitized !== $email or !$isTrustedEmail) {
        new \ErrorException("Not Valid email format");
      }

      $args = [
        "email" => $email,
        "password" => $password
      ];

      $user = new User($args);

      $errors = $user->validate();

      //check if the user already exists
      $userAlreadyExists = User::findUserByMail($email);

      if ($userAlreadyExists) {
        $errors["email"] = "User already exists";
      }

      if (empty($errors)) {

        // Hash a new password for storing in the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // generate token one use
        $token = bin2hex(random_bytes(50));

        //create user
        $user->setPassword($hashedPassword)
          ->setToken($token)
          ->create();
      }
    }

    $router->render("/sign-up", [
      "errors" => $errors,
      "user" => $user
    ]);
  }
}

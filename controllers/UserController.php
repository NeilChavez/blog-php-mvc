<?php

namespace Controller;

use Exception;
use Model\User;
use MVC\Router;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

class UserController
{
  public static function allUsers(Router $router)
  {
    $users = User::select();

    $router->render("/users/all-users", [
      "users" => $users
    ]);
  }
  public static function update(Router $router)
  {
    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if (!$id) {
      header("Location: /home");
      exit;
    }

    /** @var \Model\User $user **/
    $user = User::findUserBy("user_id", $id);
    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      session_start();
      if (!isset($_SESSION["email"])) {

        header('Location: /login');
        exit;
      }

      $userEmail = $_SESSION["email"];

      /** @var \Model\User $LoggedUser **/
      $LoggedUser = User::findUserBy('email', $userEmail);

      $args = [];

      if ($LoggedUser->role === "admin") {

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
      $errors = $user->validateRegistrationFields();

      if (empty($errors)) {

        $result = $user->update();

        if ($result) {

          header("Location: /users/all-users");
        }
      }
    }


    $router->render("/users/edit", [
      "user" => $user,
      "errors" => $errors
    ]);
  }


  static public function create(Router $router)
  {
    $errors = [];
    $user = new User();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      // sanitize the data
      $password = $_POST["password"];
      $email = $_POST["email"];
      $username = $_POST["username"];

      $emailSanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
      $isTrustedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

      if ($emailSanitized !== $email or !$isTrustedEmail) {
        new \ErrorException("Not Valid email format");
      }

      $args = [
        "email" => $email,
        "password" => $password,
        "username" => $username
      ];

      $user = new User($args);

      $errors = $user->validateRegistrationFields();

      if (empty($errors)) {

        // Hash a new password for storing in the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // generate token one use
        $token = bin2hex(random_bytes(50));

        //create user
        $result = $user
          ->setPassword($hashedPassword)
          ->setToken($token)
          ->create();

        if ($result) {

          self::sendVerificationEmail($email, $token);
          header("Location: /welcome?username=" . $username);
          exit;
        } else {

          new \ErrorException("Something went wrong with the creation of the user");
        }
      }
    }

    $router->render("/users/create", [
      "errors" => $errors,
      "user" => $user
    ]);
  }

  static public function sendVerificationEmail($email, $verificationCode)
  {
    $mail = new PHPMailer(true);

    try {

      // Server settings
      $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to DEBUG_SERVER for debugging
      $mail->isSMTP();

      $mail->Host = 'sandbox.smtp.mailtrap.io'; // Mailtrap SMTP server host 
      $mail->SMTPAuth = true;
      $mail->Username = $_ENV['MAILTRAP_USER']; // Your Mailtrap SMTP username
      $mail->Password = $_ENV['MAILTRAP_PASS']; // Your Mailtrap SMTP password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
      $mail->Port = 2525; // TCP port to connect to

      //Recipients
      $mail->setFrom('neil-blog@myblog.com', "Neil"); //Sender's email and name
      $mail->addAddress($email); // Recipient's email

      //Content
      $mail->isHTML(false); //Set to true if sending HTML email
      $mail->CharSet = 'UTF-8';
      $mail->Subject = 'Email Verification';

      //content
      $html = "<html>";
      $html .= "Your verification code is: " . $verificationCode;
      $html .= "<a href= " . $_ENV["DOMAIN_APP"] . "/email-verification?token=" . $verificationCode . ">Clik here to verify your account</a>";
      $html .= "";
      "</html>";
      $mail->Body = $html;
      $mail->AltBody = "Content without HTML";

      $mail->send();
      return true;
    } catch (Exception $e) {

      return false;
    }
  }
  static public function emailVerification(Router $router)
  {
    $token = htmlspecialchars($_GET["token"]);

    $user = User::findUserBy("token", $token);

    if (!$user) {

      header("Location: /");
      exit;
    }


    /** @var \Model\User $user **/
    $result = $user
      ->setToken("actived")
      ->update();

    if (!$result) {

      throw new \ErrorException("Something went grown with the valudation of token");
    }

    session_start();
    $_SESSION["user"] = $user;

    header("Location: /user-profile");
    exit;
  }

  static public function delete()
  {
    $id = filter_var($_POST["user_id"], FILTER_VALIDATE_INT);

    if (!$id) {
      header("Location: /");
      exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      $user = User::findById($id);

      if ($user) {

        $result = $user->delete();
     
        if ($result) {
          header("Location: /users/all-users");
        }
      }
    }
  }
}

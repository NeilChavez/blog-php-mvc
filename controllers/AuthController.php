<?php

namespace Controller;

use Exception;
use Model\User;
use MVC\Router;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

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

    $router->render("/sign-up", [
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

  public static function logout()
  {
    if (!isset($_SESSION)) {
      session_start();
    }
    unset($_SESSION["user"]);

    header("Location: /");
    exit;
  }

  public static function login(Router $router)
  {

    $user = new User();
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

      $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
      $password = $_POST["password"];
      $args = [
        "email" => $email,
        "password" => $password
      ];

      $user = new User($args);
      $errors = $user->validationLogin();

      $user =  $user->findUserBy("email", $email);

      if (!$user) {
        $errors["email"] = "User doesn't exists";
      }
      /** @var \Model\User $user **/
      $isLoggedIn = $user->checkPassword($password);

      if (!$isLoggedIn) {
        $errors["password"] = "Email or Password is not correct";
      }
      if (empty($errors)) {

        if (!isset($_SESSION)) {
          session_start();
        }

        $_SESSION["user"] = $user;

        header("Location: /user-profile");
        exit;
      }
    }

    $router->render("/login", [
      "user" => $user,
      "errors" => $errors
    ]);
  }
}

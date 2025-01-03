<?php

namespace Model;

class User extends ActiveRecord
{
  static public $table = "users";
  static public $columns = ["user_id", "username", "avatar", "email", "password", "role", "token", "created_at", "updated_at"];
  public $user_id;
  public $username;
  public $avatar;
  public $email;
  public $password;
  public $role;
  public $token;
  public $created_at;
  public $updated_at;
  public $errors = [];

  public function __construct($args = [])
  {
    $this->email = $args["email"] ?? "";
    $this->password = $args["password"] ?? "";
    $this->username = $args["username"] ?? "";
  }

  public function validateRegistrationFields()
  {

    if (!$this->username) {
      $this->errors["username"] = "You need to insert an username";
    }

    //check if the username is already in user
    $usernameAlreadyInUse = self::findUserBy("username", $this->username);

    if ($usernameAlreadyInUse) {
      $this->errors["username"] = "User already exists";

      return $this->errors;
    }

    //check if the user already exists
    $emailAlreadyExists = self::findUserBy("email", $this->email);

    if ($emailAlreadyExists) {
      $this->errors["email"] = "Email already exists";

      return $this->errors;
    }
  }

  public function validationLogin()
  {

    //Minimum eight characters, at least one letter and one number:
    $regexPassword = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/";

    if (!$this->email) {
      $this->errors["email"] = "You need to insert a email";
    }

    if (!$this->password) {
      $this->errors["password"] = "You need to insert a password";

      return $this->errors;
    }

    $isValidPassword = preg_match($regexPassword, $this->password);

    if (!$isValidPassword) {
      $this->errors["password"] = "This is not a valid password, you need to insert minimum eight characters, at least one letter and one number";

      return $this->errors;
    }

    return $this->errors;
  }

  public function setToken($token)
  {
    $this->token = $token;

    return $this;
  }

  public function setPassword($hashedPassword)
  {
    $this->password = $hashedPassword;

    return $this;
  }

  static public function findUserBy($column, $value)
  {
    $query = "SELECT user_id, username, avatar, email, role, token FROM " . self::$table . " WHERE " . $column . " = '$value';";

    $result = static::querySQL($query);

    return array_shift($result);
  }

  public function checkPassword($password)
  {

    $query = "SELECT password FROM " . static::$table . " WHERE email = '" . $this->email . "';";

    $result = self::$db->query($query);

    $result = $result->fetch_assoc();

    $hashedPassword = $result["password"];

    return password_verify($password, $hashedPassword);
  }
}

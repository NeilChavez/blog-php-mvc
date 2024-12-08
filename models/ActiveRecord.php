<?php

namespace Model;

use mysqli;

class ActiveRecord
{
  protected static $table;
  protected static mysqli $db;

  public static function setDB(mysqli $connection)
  {
    self::$db = $connection;
  }

  public static function querySQL($query)
  {

    $response = self::$db->query($query);

    $result = [];

    while ($row = $response->fetch_assoc()) {

      $result[] = self::instanceObject($row);
    }

    return $result;
  }


  public static function select()
  {
    $querySQL = "SELECT * FROM " . static::$table;

    $result = self::querySQL($querySQL);

    return $result;
  }


  public static function instanceObject($row)
  {
    $instance = new static;

    foreach ($row as $key => $value) {

      if (property_exists($instance, $key) && $value !== null) {

        $instance->$key = self::escapeHtml($value);
      }
    }

    return $instance;
  }
  public static function findById($id)
  {
    $query = "SELECT * FROM " . static::$table . " WHERE " . self::modelName(static::class) . "_id = $id";

    $result = self::querySQL($query);

    return array_shift($result);
  }

  public function create()
  {

    $proprerties = $this->getObjectKeysAndValues();

    $keys = join(", ", array_keys($proprerties));
    $values = join("', '", array_values($proprerties));

    $query = "INSERT INTO " . static::$table . " (" . $keys . ") VALUES ('" . $values . "');";
 
    $result = self::$db->query($query);

    return $result;
  }
  public function getObjectKeysAndValues()
  {
    $keysAndValues = [];

    foreach ($this as $key => $value) {
      if ($key === self::modelName(static::class) . "_id" or empty($value) or is_null($value)) continue;

      $keysAndValues[$key] = $value;
    }
    return $keysAndValues;
  }

  public static function escapeHtml($input)
  {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
  }

  public static function modelName($model)
  {

    $model = strtolower(strrchr($model, "\\"));

    return substr($model, 1);
  }

  public static function toSingular($class)
  {
    $result = "s" === strlen($class) - 1 ? rtrim($class) : $class;

    return strtolower($result);
  }
}

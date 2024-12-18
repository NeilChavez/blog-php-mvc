<?php

function getConnectionDB()
{
  $connection = new mysqli("localhost", "root", "", "blog-php");
  
  $connection->set_charset("utf8");

  if ($connection->connect_error) {
    //todo
    echo "ERROR IN CONNECTION";
  }
  return $connection;
}
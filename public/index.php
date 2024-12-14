<?php

require "../vendor/autoload.php";
require "../includes/app.php";

use MVC\Router;
use Controller\PageController;
use Controller\PostController;

$router = new Router();


$router->get("/home", [PageController::class, "index"]);
$router->get("/post", [PageController::class, "singlePost"]);
$router->get("/post/create", [PostController::class, "create"]);
$router->post("/post/create", [PostController::class, "create"]);
$router->get("/posts/admin", [PostController::class, "admin"]);
$router->get("/post/update", [PostController::class, "update"]);
$router->post("/post/update", [PostController::class, "update"]);

$router->checkRoutes();

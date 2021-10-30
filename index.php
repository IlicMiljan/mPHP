<?php
    require_once 'vendor/autoload.php';

    use Core\Router;
    use Core\Template;

    Router::Add('/', function() {
         Template::View('index.html', ['Title' => APP_NAME]);
    });

    Router::Run();
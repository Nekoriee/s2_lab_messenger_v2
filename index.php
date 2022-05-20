<?php

use Pcat\MessengerV2\Controller;
use Twig\Loader\FilesystemLoader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . "/vendor/autoload.php";

echo '
    <form action="/" method="post">
        <label> Username:
            <input name="name" type="text">
        </label> <br>
        <label> Password:
            <input name="pass" type="password">
        </label> <br>
        <label> Message:
            <input name="mess" type="text">
        </label> <br>
        <input type="submit" value="Submit">
    </form>
    ';

$loader = new FilesystemLoader(__DIR__ . '/templates/');
$twig = new Twig\Environment($loader);

$controller = new Controller($twig);

$logger = new Logger('messenger_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/messenger.log', INFO));

$logger->info('hello');

$name = $_POST['name'];
$pass = $_POST['pass'];
$message = $_POST['mess'];

$connection = new PDO('mysql:dbname=messenger;host=127.0.0.1', 'pcat', 'basedcat');
$sql = 'SELECT * from users where name=:nam';
$stmt = $connection->prepare($sql);
$stmt->bindParam('nam', $name, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetchAll();

if (!is_null($user[0]["name"]) && $user[0]["password"] == $pass) {
    $logger->info("Пользователь $name вошёл в систему");
    if (!is_null($message) && $message != '') {
        $logger->info("Пользователь $name отправил сообщение");
        $datetime = date("Y-m-d H:i:s");
        $sql = 'insert into messages (mes_datetime, mes_user, mes_message) values (:datetime, :user, :message)';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam('user', $name, PDO::PARAM_STR);
        $stmt->bindParam('datetime', $datetime, PDO::PARAM_STR);
        $stmt->bindParam('message', $message, PDO::PARAM_STR);
        $stmt->execute();
    }
}
elseif (!is_null($user[0]["name"]) && $user[0]["name"] == $name)  {
    $logger->info("Введён неверный пароль для пользователя $name");
}

$sql = 'SELECT * from messages';
$stmt = $connection->prepare($sql);
$stmt->execute();
$messages = $stmt->fetchAll();

echo $controller->showMessages($messages);

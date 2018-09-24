<?php
require_once("Includes/db.php");

/**
 * После реорганизации эти строки не нужны 
 *
  // эти переменные осуществляют передачу параметров доступа к базе данных
  $dbHost = "localhost";
  $dbUsername = "phpuser";
  $dbPasswd = "phpuserpw";
 */
//other variables
$userNameIsUnique = true;
$passwordIsValid = true;
$userIsEmpty = false;
$passwordIsEmpty = false;
$password2IsEmpty = false;

/** Выполняет проверку того, что страница была запрошена из нее самой 
 * посредством метода POST. Если это не так, дальнейшие проверки допустимости 
 * не выполняются, и на экран выводится страница с пустыми полями */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    /** Позволяюет проверить, ввел ли пользователь имя автора пожелания в 
     * поле. Если текстовое поле "user" является пустым, значение 
     * $userIsEmpty меняется на "true".  */
    if ($_POST['user'] == "") {
        $userIsEmpty = true;
    }

    /**
     * До реорганизации код выглядел так:
     *
      /**
     * // Создать соединение с базой данных. Если установить подключение 
     * //невозможно, то выводится ошибка /
     * $con = mysqli_connect("localhost", "phpuser", "phpuserpw");
     *      
     *     if (!$con) {   
     *    exit('Connect Error (' . mysqli_connect_errno() . ') '     
     *           . mysqli_connect_error());   
     * }    
     * //установить кодировку клиента по умолчанию
     * mysqli_set_charset($con, 'utf-8');
     * //Проверяет, существует ли пользователь, имя которого соответствует 
     * //полю "user". Эта задача выполняется путем поиска идентификационного      
     * //номера автора пожелания в соответствии с именем, указанным в поле      
     * //"user". Если такой номер существует, значение $userNameIsUnique      
     * //меняется на "false".     
     * mysqli_select_db($con, "wishlist");    
     * $user = mysqli_real_escape_string($con, $_POST['user']);    
     * $wisher = mysqli_query($con, "SELECT id FROM wishers WHERE name='".$user."'");
     * $wisherIDnum=mysqli_num_rows($wisher);    
     * if ($wisherIDnum) {    
     *     $userNameIsUnique = false;    
     * }
     */
    /**
     * После реорганизации код выглятит так:
     */
    /**
     * Объект WishDB существует до тех пор, пока обрабатывается текущая 
     * страница. Если обработка завершена или прервана, этот объект 
     * уничтожается. Код для открытия подключения к базе данных не 
     * является необходимым, поскольку подключение выполняется 
     * посредством функции WishDB. Код для закрытия подключения также не 
     * является необходимым, поскольку подключение будет закрыто сразу же 
     * после уничтожения объекта WishDB.
     */
    $wisherID = WishDB::getInstance()->get_wisher_id_by_name($_POST["user"]);
    if ($wisherID) {
        $userNameIsUnique = false;
    }


    /** Проверка правильности ввода и подтверждения пароля. Код выполняет 
     * проверку того, что поля "Password" ("password") и "Confirm Password" 
     * ("password2") заполнены и идентичны друг другу. В противном случае 
     * значения соответствующих логических переменных также изменяются.  */
    if ($_POST['password'] == "")
        $passwordIsEmpty = true;
    if ($_POST['password2'] == "")
        $password2IsEmpty = true;
    if ($_POST['password'] != $_POST['password2']) {
        $passwordIsValid = false;
    }

    /**
     * До реорганизации код выглядел так:

      // Проверка  того, что имя пользователя указано однозначно и что пароль
      // введен и подтвержден правильно. Если эти условия выполнены, код
      //извлекает значения "user" и "password" из формы HTML и вставляет их
      // соответственно в столбцы "Name" и "Password", относящиеся к новой
      //строке в базе данных "Wishers". После добавления новой записи код
      //закрывает подключение к базе данных и переадресует приложение на
      //страницу editWishList.php.
      //
      if (!$userIsEmpty && $userNameIsUnique && !$passwordIsEmpty && !$password2IsEmpty && $passwordIsValid) {
      $password = mysqli_real_escape_string($con, $_POST['password']);
      mysqli_select_db($con, "wishlist");
      mysqli_query($con, "INSERT wishers (name, password) VALUES ('" . $user . "', '" . $password . "')");
      mysqli_free_result($wisher);
      mysqli_close($con);
      header('Location: editWishList.php');
      exit;
      }
     * 
     */
    /**
     * После реорганизации код выглятит так:
     */
    /**
     * Код вызывает функцию create_wisher. 
     */
    if (!$userIsEmpty && $userNameIsUnique && !$passwordIsEmpty && !$password2IsEmpty && $passwordIsValid) {
        WishDB::getInstance()->create_wisher($_POST["user"], $_POST["password"]);
        session_start(); //начинает сеанс, что означает открытие массива $_SESSION для ввода или извлечения данных.
        $_SESSION['user'] = $_POST['user']; //добавляет элемент к массиву $_SESSION. Добавляемый элемент содержит значение и идентификатор. Значение – это имя недавно созданных пользователей, а "user" является идентификатором. 
        header('Location: editWishList.php'); //переадресует пользователя на страницу editWishList.php. 
        exit;
    }
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head><meta charset=UTF-8"></head>
    <body>
        Добро пожаловать!<br>
        <form action="createNewWisher.php" method="POST">
            Ваше имя: <input type="text" name="user"/><br/>
            <?php
            /** Отображать сообщения об ошибках, если поле" user " пустое или 
             * уже есть пользователь с таким именем */
            if ($userIsEmpty) {
                echo ("Пожалуйста, введите ваше имя!");
                echo ("<br/>");
            }
            if (!$userNameIsUnique) {
                echo ("Человек с таким именем уже существует. Измените имя и повторите попытку");
                echo ("<br/>");
            }
            ?>
            Пароль: <input type="password" name="password"/><br/>
            <?php
            /** Отображать сообщения об ошибках, если поле "пароль" пустое */
            if ($passwordIsEmpty) {
                echo ("Введите пароль");
                echo ("<br/>");
            }
            ?>
            Пожалуйста, подтвердите Ваш пароль: <input type="password" name="password2"/><br/>
            <input type="submit" value="Регистрация"/>
            <?php
            /** Отображать сообщения об ошибках, если поле "password2" пустое
             * или его содержимое не соответствует полю "password" */
            if ($password2IsEmpty) {
                echo ("Подтвердите пароль");
                echo ("<br/>");
            }
            if (!$password2IsEmpty && !$passwordIsValid) {
                echo ("<div>Пароли не совпадают!</div>");
                echo ("<br/>");
            }
            ?>

        </form>

    </body>
</html>

<!--
Вход с использованием страницы index.php состоит из двух действий:
    Ввод имени пользователя и пароля в форму ввода HTML и передача данных 
на страницу index.php для проверки достоверности.
    Проверка допустимости входа
      Проверка допустимости входа включает следующие действия:
        Проверка местоположения пользователя до переадресации.
        Проверка имени пользователя и пароля.
        Сохранение имени пользователя в сеансе и переадресация пользователя 
на страницу editWishList.php или Отображение сообщения об ошибке.
-->

<?php
require_once("Includes/db.php"); //разрешает использование файла db.php
$logonSuccess = false;

/**
 * Проверяет учетные данные пользователя. Сперва проверяет, является ли 
 * методом запроса POST. Если POST является методом запроса, то пользователь 
 * был перенаправлен после подачи формы входа. В таком случае блок кода 
 * вызывает функцию verify_wisher_credentials, используя имя и пароль, 
 * введенные в форме входа. 
 *  
 * Функция verify_wisher_credentials, которая находится в файле db.php
 * проверяет есть ли запись в таблице пользователей, где имя 
 * пользователя и пароль совпадают со значениями, поданными в форме входа. 
 * Если функция verify_wisher_credentials возвращает true, то в базе 
 * данных есть пользователь с указанной комбинацией имени и пароля. Это 
 * значит, что проверка успешна и значение $logonSuccess меняется на true. 
 * В таком случае начинается сеанс и открывается массив $_SESSION. Код 
 * добавляет новый элемент к массиву $_SESSION. Этот элемент содержит 
 * значение и идентификатор (ключ). Значение является именем пользователя, 
 * а идентификатором является "user". Затем код перенаправляет пользователя 
 * к странице editWishList.php для редактирования списка желаний. 
 * 
 * Если функция verify_wisher_credentials возвращает false, то значением 
 * переменной $logonSuccess останется false. Значение переменной 
 * используется для отображения сообщения об ошибке. 
 */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $logonSuccess = (WishDB::getInstance()->verify_wisher_credentials($_POST['user'], $_POST['userpassword']));
    if ($logonSuccess == true) {
        session_start();
        $_SESSION['user'] = $_POST['user'];
        header('Location: editWishList.php');
        exit;
    }
}
?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link href="wishlist.css" type="text/css" rel="stylesheet" media="all" />
    </head>
    <body>
        <div class="showWishList">
            <input type="submit" name="showWishList" value="Показать список пожеланий >>" onclick="javascript:showHideShowWishListForm()"/>
            <form name="wishList" action="wishlist.php" method="GET" style="visibility:hidden">
                <input type="text" name="user" value="" />
                <input type="submit" value="Дальше" />
            </form>
        </div>
        <br>Все еще нет списка пожеланий?! <a href="createNewWisher.php">Создать сейчас</a>
        <div class="logon">
            <!--
            Код реализует кнопку с текстом "Мой список пожеланий >>". Кнопка отображается 
            вместо формы "logon". При нажатии кнопки вызывается функция "showHideLogonForm".
            -->
            <input type="submit" name="myWishList" value="Мой список пожеланий >>" onclick="javascript:showHideLogonForm()"/>
            <!--
            форма HTML позволяет вводить имя и пароль пользователя в текстовые 
            поля. Если пользователь нажимает кнопку "Редактировать пожелания", 
            данные передаются на эту же страницу – index.php.         
            Атрибут style определяет, является форма скрытой или нет. Блок php
            используется для поддержания отображения формы до тех пор, пока не будет 
            выполнен успешный ввод данных пользователем.
            -->
            <form name="logon" action="index.php" method="POST" style="visibility:<?php
            if ($logonSuccess)
                echo "hidden";
            else
                echo "visible";
            ?>">
                Имя пользователя: <input type="text" name="user">
                Пароль  <input type="password" name="userpassword">
                <div class="error">
                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        if (!$logonSuccess)
                            echo "Неверное имя и/или пароль";
                    }
                    ?>
                </div>
                <input type="submit" value="Редактировать пожелания">
            </form>
        </div>  
        <script>
            function showHideLogonForm() {
                if (document.all.logon.style.visibility == "visible") {
                    document.all.logon.style.visibility = "hidden";
                    document.all.myWishList.value = "Мой список пожеланий >>";
                } else {
                    document.all.logon.style.visibility = "visible";
                    document.all.myWishList.value = "<< Мой список пожеланий";
                }
            }
            function showHideShowWishListForm() {
                if (document.all.wishList.style.visibility == "visible") {
                    document.all.wishList.style.visibility = "hidden";
                    document.all.showWishList.value = "Показать список пожеланий >>";
                } else {
                    document.all.wishList.style.visibility = "visible";
                    document.all.showWishList.value = "<< Показать список пожеланий";
                }
            }
        </script>	
    </body>
</html>

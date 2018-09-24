<!--
При переходе пользователя на страницу editWishList.php приложение должно 
выдавать подтверждение того, что страница используется лицом, только что 
зарегистрированным на странице createNewWisher.php.

Реализация этих функциональных возможностей состоит из двух действий:
    Извлечение имени пользователя из сеанса
    Переадресация пользователя на страницу index.php, в случае если имя 
пользователя не было извлечено из сеанса

-->

<?php
/**
 * Блок кода открывает массив $_SESSION для извлечения данных и 
 * проверки того, что в $_SESSION содержится элемент с 
 * идентификатором "user". Если проверка выполнена успешно, код 
 * выводит на экран приветственное сообщение. 
 */
session_start();
if (array_key_exists("user", $_SESSION)) {
    echo "Привет " . $_SESSION['user'];
} else {
    header('Location: index.php'); //Переадресация пользователя, не зарегистрированного в системе на страницу index.php
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <!--
        Таблица, в которой отображаются пожелания данного лица
        -->
        <table border="black">
            <tr><th>Продукт</th><th>Срок выполнения</th></tr>
            <?php
            require_once("Includes/db.php");
            $wisherID = WishDB::getInstance()->get_wisher_id_by_name($_SESSION["user"]);
            $result = WishDB::getInstance()->get_wishes_by_wisher_id($wisherID);
            /**
             * Таблица реализуется с помощью цикла (оператор while), который 
             * выводит на экран строки с пожеланиями, в то время как пожелания 
             * выбираются из базы данных.
             */
            while ($row = mysqli_fetch_array($result)):
                echo "<tr><td>" . htmlentities($row['description']) . "</td>";
                echo "<td>" . htmlentities($row['due_date']) . "</td>";
                $wishID = $row['id']; //идентификатор пожелания 
                echo "<td>WishID=" . $wishID . "</td>";
                ?>
                <!--
                Эта форма содержит компонент кнопки редактирования и скрытый 
                компонент, который при нажатии кнопки отправляет значение $wishID.
                -->
                <td>
                    <form name="editWish" action="editWish.php" method="GET">
                        <input type="hidden" name="wishID" value="<?php echo $wishID; ?>">
                        <input type="submit" name="editWish" value="Редактировать">
                    </form>
                </td>
                <td>
                    <form name="deleteWish" action="deleteWish.php" method="POST">
                        <input type="hidden" name="wishID" value="<?php echo $wishID; ?>"/>
                        <input type="submit" name="deleteWish" value="Удалить"/>
                    </form>
                </td>
                <?php
                echo "</tr>\n";
            endwhile;
            mysqli_free_result($result);
            ?>
        </table>

        <!--
        Форма содержит поле ввода "Добавить пожелание" типа submit. Это 
        поле реализует кнопку "Добавить пожелание". При нажатии кнопки 
        "Добавить пожелание" пользователь перенаправляется на страницу 
        editWish.php. Метод запроса к серверу не используется, т.к. данные 
        посредством этой формы не передаются.
        -->
        <form name="addNewWish" action="editWish.php">            
            <input type="submit" value="Добавить пожелание">
        </form>
        <!--
        Возврат к первой странице "index.php" 
        Пользователь должен иметь возможность, нажав кнопку, в любой момент 
        вернуться на первую страницу приложения.
        Форма перенаправляет пользователя на первую страницу "index.php" 
        после нажатия кнопки "Вернуться на главную". 
        -->
        <form name="backToMainPage" action="index.php">
            <input type="submit" value="Вернуться на главную"/>
        </form>
    </body>
</html>
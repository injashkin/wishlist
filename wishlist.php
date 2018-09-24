<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        Список пожеланий <?php echo htmlentities($_GET["user"]) . "<br/>"; ?>
        <?php
        require_once("Includes/db.php");

        /**
         * До реорганизации код выглядел так:
         * 
         * //Этот код открывает подключение к базе данных. В соответствии
         * //с кодом производится попытка подключения к базе данных и         
         * //выдается сообщение об ошибке в случае неудачи.        
         * $con = mysqli_connect("localhost", "phpuser", "phpuserpw");        
         * if (!$con) {         
         *    exit('Connect Error (' . mysqli_connect_errno() . ') '         
         *            . mysqli_connect_error());        
         * }        
         * mysqli_set_charset($con, 'utf-8'); //set the default client character set         
         * //Этот код получает идентификатор автора пожеланий, чей список был 
         * //запрошен. Если автор пожеланий отсутствует в базе данных, код        
         * //уничтожает/завершает процесс и отображает сообщение об ошибке.        
         * mysqli_select_db($con, "wishlist");        
         * $user = mysqli_real_escape_string($con, htmlentities($_GET["user"]));        
         * $wisher = mysqli_query($con, "SELECT id FROM wishers WHERE name='" . $user . "'");        
         * if (mysqli_num_rows($wisher) < 1) {        
         *     exit("Пользователь " . htmlentities($_GET["user"]) . " не найден. Проверьте правописание и повторите попытку");        
         * }        
         * $row = mysqli_fetch_row($wisher);        
         * $wisherID = $row[0];        
         * mysqli_free_result($wisher);
         */
        /**
         * Вышележащий закоментированный код подключался к базе данных и
         * получал идентификатор пожелания. В результате реорганизации кода
         * вышележащий закоментированный код заменен вызовом функции 
         * get_wisher_id_by_name. 
         * 
         * Новый код сначала вызывает функцию getInstance в WishDB. Функция 
         * getInstance возвращает экземпляр WishDB, а код вызывает функцию 
         * get_wisher_id_by_name в пределах данного экземпляра. Если 
         * требуемое пожелание в базе данных не найдено, код завершает 
         * процесс и отображает сообщение об ошибке.
         * 
         * Для открытия подключения к базе данных наличие кода не является 
         * необходимым. Открытие подключения выполняется конструктором класса 
         * WishDB. Если имя и/или пароль изменяются, необходимо обновить 
         * только соответствующие переменные класса WishDB.
         */
        $wisherID = WishDB::getInstance()->get_wisher_id_by_name($_GET["user"]);
        if (!$wisherID) {
            exit("Пользователь " . $_GET["user"] . " не найден. Проверьте правописание и повторите попытку");
        }
        ?>


        <!--Код для отображения таблицы HTML пожеланий, связанных с автором 
        пожеланий. Автор пожеланий определяется идентификатором, полученным 
        в коде файла index.php-->
        <table border="black">
            <tr>
                <th>Item</th>
                <th>Due Date</th>
            </tr>
            <!--Внутри кода: 
                Посредством запроса SELECT пожелания со сроками 
            их выполнения для указанного пользователя извлекаются в 
            соответствии с идентификатором, который, в свою очередь был 
            извлечен в действии 4; кроме того, пожелания и соответствующие 
            сроки выполнения сохраняются в массиве $result.
                С помощью цикла отдельные элементы массива $result выводятся 
            на экран в качестве строк таблиц, пока массив непуст.
                Теги <tr></tr> формируют строки, теги <td></td> – ячейки 
            внутри строк, а после символа \n начинается новая строка.
                Функция htmlentities преобразует все символы, имеющие 
            эквивалентные сущности HTML, в сущности HTML. Это помогает 
            предотвратить межсайтовые сценарии.
                В конце функции освобождают все ресурсы (результаты mysqli 
            и выражения OCI8) и закрывают подключение к базе данных. Имейте в 
            виду, что для физического закрытия подключения необходимо 
            освободить все ресурсы, использующие подключение. В противном 
            случае внутренняя система подсчета ссылок PHP сохранит нижележащее 
            подключение к базе данным открытым, даже если подключение 
            неприменимо после вызова oci_close() или mysqli_close().
            <?php
            /**
             * Данный код получал пожелания для автора пожеланий, идентифицированного 
             * с помощью кода
             * $result = mysqli_query($con, "SELECT description, due_date FROM wishes WHERE wisher_id=" . $wisherID);
             * после реорганизаци заменен на код, который вызывает функцию 
             * get_wishes_by_wisher_id
             */
            $result = WishDB::getInstance()->get_wishes_by_wisher_id($wisherID);

            while ($row = mysqli_fetch_array($result)) {
                echo "<tr><td>" . htmlentities($row["description"]) . "</td>";
                echo "<td>" . htmlentities($row["due_date"]) . "</td></tr>\n";
            }

            mysqli_free_result($result);

            /**
             * После реорганизаци удалена строка, которая закрывает подключение к 
             * базе данных. 
             * 
             * mysqli_close($con);
             * 
             * Код не нужен, потому что подключение к базе данных автоматически 
             * закрывается при уничтожении объекта WishDB. Однако рекомендуем 
             * сохранять код, освобождающий ресурс. Вам необходимо освободить 
             * все ресурсы, которые используют подключение, чтобы убедиться в том, 
             * что оно закрыто, даже при вызове функции close или уничтожении 
             * экземпляра с подключением к базе данных.
             */
            ?>
        </table>
    </body>
</html>
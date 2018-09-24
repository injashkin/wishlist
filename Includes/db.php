<?php

/**
 * Для создания класса WishDB необходимо инициализировать переменные класса и 
 * реализовать конструктор класса. Обратите внимание, что класс WishDB 
 * расширяет mysqli. Это означает, что WishDB наследует функции и другие 
 * характеристики класса PHP mysqli. Вы убедитесь в важности этого при 
 * добавлении функций mysqli к классу.
 * В данном классе объявляются переменные настройки базы данных для хранения 
 * имени и пароля собственника базы данных (пользователя), имени и машины 
 * размещения базы данных. Все объявления переменных являются закрытыми: это 
 * означает, что начальные значения в этих объявлениях недоступны вне класса 
 * WishDB (см. php.net). Также объявляется закрытая статическая переменная 
 * $instance, которая хранит экземпляр WishDB. Ключевое слово "статический" 
 * означает, что функции в классе имеют доступ к переменной даже при 
 * отсутствии экземпляра класса.
 */
class WishDB extends mysqli {

    // single instance of self shared among all instances
    //один экземпляр self, общий для всех экземпляров
    private static $instance = null;
    // db connection config vars
    private $user = "phpuser";
    private $pass = "phpuserpw";
    private $dbName = "wishlist";
    private $dbHost = "localhost";

    /**
     * При использовании функций класса WishDB в других файлах PHP должна 
     * быть вызвана функция, позволяющая создать объект ("создать экземпляр") 
     * класса WishDB. WishDB разработан в качестве одноэкземплярного класса; 
     * это означает, что в любой определенный момент времени может 
     * существовать только один экземпляр класса. Поэтому рекомендуется 
     * предотвращать создание экземпляра WishDB, которое осуществляется 
     * извне и способствует появлению дублирующихся экземпляров.
     * 
     * Функция getInstance является общедоступной и статической.     *  
     * Общедоступность означает возможность свободного доступа извне класса. 
     * Статическая функция доступна даже в том случае, если для класса не 
     * было создано экземпляров. Поскольку функция getInstance вызывается 
     * для создания экземпляров класса, она является статической. 
     * Обратите внимание, что эта функция имеет доступ к статической 
     * переменной $instance и устанавливает ее значение как экземпляр класса.
     * 
     * Двойное двоеточие (::), или "оператор разрешения диапазона" 
     * (Scope Resolution Operator), и ключевое слово self используются для 
     * получения доступа к статическим функциям. Self в рамках определения 
     * класса используется в качестве ссылки на данный класс. Если двойное 
     * двоеточие находится вне определения класса, вместо self используется 
     * имя класса. */

    /**
     * Этот метод должен быть статическим, и должен возвращать экземпляр 
     * объекта, если объект уже не существует.
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Методы clone и wakeup предотвращают создание внешних экземпляров копий 
     * Одноэлементного класса, тем самым исключая возможность дублирования объектов.
     */
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }

    /**
     * Класс может содержать в себе специальный метод, известный как 
     * "конструктор", который выполняется автоматически каждый раз при создании 
     * экземпляра этого класса. В данном руководстве рассматривается добавление 
     * к классу WishDB конструктора, который подключается к базе данных каждый 
     * раз при создании экземпляра WishDB.
     * 
     * Следует учитывать, что вместо переменных $con, $dbHost, $user или $pass 
     * используется псевдопеременная $this. Псевдопеременная $this используется 
     * при вызове метода внутри контекста объекта. Она ссылается на значение 
     * переменной внутри этого объекта.
     */
// private constructor
    private function __construct() {
        parent::__construct($this->dbHost, $this->user, $this->pass, $this->dbName);
        if (mysqli_connect_error()) {
            exit('Connect Error (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());
        }
        parent::set_charset('utf-8');
    }

    /**
     * Функция get_wisher_id_by_name
     * Эта функция возвращает идентификатор пользователя, а в качестве входного 
     * параметра для ее выполнения требуется имя пользователя. 
     * 
     * Блок кода выполняет запрос SELECT ID FROM wishers WHERE name = [переменная 
     * для имени пожелания]. Результат запроса - массив идентификаторов из 
     * записей, соответствующих запросу. Если массив не пустой, это по умолчанию 
     * означает, что он содержит один элемент, поскольку при создании таблицы 
     * имя поля было определено как UNIQUE. В этом случае функция возвращает 
     * первый элемент массива $result (элемент под номером ноль). Если массив 
     * пуст, функция возвращает значение "null". 
     * 
     * Примечание к безопасности. Для базы данных MySQL строка $name используется 
     * с с escape-символом для предотвращения атак SQL-инъекций. См. статью 
     * энциклопедии Wikipedia о введении SQL http://en.wikipedia.org/wiki/SQL_injection
     * и документацию mysql_real_escape_string http://us3.php.net/mysql_real_escape_string.
     * 
     */
    public function get_wisher_id_by_name($name) {

        $name = $this->real_escape_string($name);

        $wisher = $this->query("SELECT id FROM wishers WHERE name = '"
                . $name . "'");
        if ($wisher->num_rows > 0) {
            $row = $wisher->fetch_row();
            return $row[0];
        } else
            return null;
    }

    /**
     * Функция get_wishes_by_wisher_id
     * Эта функция возвращает зарегистрированные пожелания пользователя, и для 
     * ее выполнения в качестве входного параметра требуется идентификатор 
     * пользователя. 
     * Блок кода выполняет запрос "SELECT id, description, due_date FROM 
     * wishes WHERE wisherID=" . $wisherID и возвращает набор результатов, 
     * который является массивом записей, соответствующих запросу. Выделение 
     * выполняется с помощью wisherID, который является внешним ключом для 
     * таблицы wishes. 
     */
    public function get_wishes_by_wisher_id($wisherID) {
        return $this->query("SELECT id, description, due_date FROM wishes WHERE wisher_id=" . $wisherID);
    }

    /**
     * Функция create_wisher
     * Функция создает новую запись в таблице "Wishers". Эта функция не 
     * возвращает каких-либо данных, и в качестве входных параметров для ее 
     * выполнения требуется имя и пароль нового пользователя.
     * 
     * Блок кода выполняет запрос "INSERT wishers (Name, Password) VALUES 
     * ([переменные представляющие имя и пароль нового пожелания]). При 
     * выполнении запроса добавляется новая запись в таблицу "Wishers" с 
     * полями "name" и "password", заполненными значениями $name и $password 
     * соответственно. 
     */
    public function create_wisher($name, $password) {
        $name = $this->real_escape_string($name);
        $password = $this->real_escape_string($password);
        $this->query("INSERT INTO wishers (name, password) VALUES ('" . $name . "', '" . $password . "')");
    }

    /**
     * Функция verify_wisher_credentials
     * Проверка учетных данных пользователя. Входными параметрами 
     * являются имя и пароль; функция возвращает значение 0 или 1.
     * 
     * Проверяет есть ли в таблице пользователей базы данных запись 
     * с таким именем и паролем. Если есть, то функция возвращает 1.
     * В противном случае 0.
     *  
     * @param type $name
     * @param type $password
     * @return type
     */
    public function verify_wisher_credentials($name, $password) {
        $name = $this->real_escape_string($name);

        $password = $this->real_escape_string($password);
        $result = $this->query("SELECT 1 FROM wishers
 	           WHERE name = '" . $name . "' AND password = '" . $password . "'");
        return $result->data_seek(0);
    }

    /**
     * Функция insert_wish добавляет новую запись в таблицу пожеланий.
     * Эта функция требует в качестве входных параметров идентификатор 
     * пользователя $wisherID, описание нового пожелания $description и 
     * срок выполнения пожелания $duedate, после чего добавляет эти 
     * данные к базе данных как новую запись. 
     * Функция не возвращает какого-либо значения.
     * 
     * Из этой функции вызывается функция format_date_for_sql для 
     * преобразования введенного срока выполнения в формат, который 
     * может быть обработан сервером базы данных. Затем для ввода 
     * нового пожелания в базу данных выполняется запрос 
     * "INSERT INTO wishes (wisher_id, description, due_date)". 
     * 
     * @param type $wisherID
     * @param type $description
     * @param type $duedate
     */
    function insert_wish($wisherID, $description, $duedate) {
        $description = $this->real_escape_string($description);
        if ($this->format_date_for_sql($duedate) == null) {
            $this->query("INSERT INTO wishes (wisher_id, description)" .
                    " VALUES (" . $wisherID . ", '" . $description . "')");
        } else
            $this->query("INSERT INTO wishes (wisher_id, description, due_date)" .
                    " VALUES (" . $wisherID . ", '" . $description . "', "
                    . $this->format_date_for_sql($duedate) . ")");
    }

    /**
     * В качестве входного параметра требуется строка, в которой указана 
     * дата. Эта функция возвращает дату в формате, который может быть 
     * обработан сервером базы данных, или null, если входная строка пустая. 
     * Примечание. Функция в этом примере использует функцию PHP date_parse. 
     * Эта функция работает только с англоязычными датами, такими как 
     * "December 25, 2010", и только с арабскими цифрами. На 
     * профессиональном веб-сайте следует использовать управляющий элемент 
     * выбора даты.
     * При пустой входной строке код возвращает значение "NULL". В 
     * противном случае внутренняя функция date_parse вызывается с 
     * входным параметром $date. Функция date_parse возвращает массив, 
     * состоящий из трех элементов с именами $dateParts["year"], 
     * $dateParts["month"] и $dateParts["day"]. Окончательная строка 
     * вывода создается из элементов массива $dateParts. 
     * Важно! Функция date_parse распознает только англоязычные даты. 
     * Например, она воспринимает и интерпретирует дату "February 2, 
     * 2016" но не дату "2 Unora, 2016".
     * 
     * @param type $date
     * @return type
     */
    function format_date_for_sql($date) {
        if ($date == "")
            return null;
        else {
            $dateParts = date_parse($date);
            return $dateParts["year"] * 10000 + $dateParts["month"] * 100 + $dateParts["day"];
        }
    }

    /**
     * Обновление пожелания в базе данных 
     * @param type $wishID
     * @param type $description
     * @param type $duedate
     */
    public function update_wish($wishID, $description, $duedate) {
        $description = $this->real_escape_string($description);
        if ($duedate == '') {
            $this->query("UPDATE wishes SET description = '" . $description . "',
             due_date = NULL WHERE id = " . $wishID);
        } else
            $this->query("UPDATE wishes SET description = '" . $description .
                    "', due_date = " . $this->format_date_for_sql($duedate)
                    . " WHERE id = " . $wishID);
    }
    
    /**
     * 
     * @param type $wishID
     * @return type
     */

    public function get_wish_by_wish_id($wishID) {
        return $this->query("SELECT id, description, due_date FROM wishes WHERE id = " . $wishID);
    }
    
    /**
     * Функция удаляет пожелания из базы данных
     * @param type $wishID
     */
    function delete_wish ($wishID){
    $this->query("DELETE FROM wishes WHERE id = " . $wishID);
}

}

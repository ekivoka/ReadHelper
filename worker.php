<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$level = $_POST['level'] ;
$host = 'localhost'; // адрес сервера
$database = 'test'; // имя базы данных
$user = 'root'; // имя пользователя
$password = ''; // пароль

$link = mysqli_connect($host, $user, $password, $database)
    or die("Ошибка " . mysqli_error($link));




//генератор куки
function cookieGen(){
    $cookie = hash('ripemd160', date('h-i-s, j-m-y, it is w Day'));
    return $cookie;
}

//сохранить книгу на диске
function saveBook($cookie){
    $bookName = $cookie.'.txt';
    move_uploaded_file($_FILES['file']['tmp_name'], 'books/'.$bookName);
    return $bookName;
    
}


//обновляем книгу в бд
function updateBookDB($cookie){
    $bookname = saveBook($cookie);
    $date = date('Y-m-d');
    $query = "UPDATE sessions SET lastDate='$date' WHERE cookie='$cookie';";
    queryDB($query);
    $query = "UPDATE sessions SET lastPage=1 WHERE cookie ='$cookie';";
    queryDB($query);
}


//создаем новую сессию с записью в бд
function addNewCookieDB($cookie){    
    
    $bookName = saveBook($cookie);
    $date = date('Y-m-d');
    $query = "INSERT INTO sessions (cookie, book,lastPage, lastDate) VALUES ('$cookie', '$bookName', 1, '$date')";
    queryDB($query);
       
}

//запрос к бд
function queryDB($query){
    
    $level = $_POST['level'] ;
    $host = 'localhost'; // адрес сервера
    $database = 'test'; // имя базы данных
    $user = 'root'; // имя пользователя
    $password = ''; // пароль

    $link = mysqli_connect($host, $user, $password, $database)
    or die("Ошибка " . mysqli_error($link));
    $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
    return $result;
}


function getPage($cookie){
    $query ="SELECT lastPage FROM sessions WHERE cookie = \"$cookie\"";
    $res = queryDB($query);
    $row = mysqli_fetch_row($res);
    return $row[0];
}


//загрузка книги
function loadBook($cookie){
    $bookname = 'books/'.$cookie.'.txt';
    $page = getPage($cookie);
    $level = 5;
    
    $text = file_get_contents($bookname, FALSE, NULL, ($page-1)*1000, 1010);
    echo $page."bgbg";

    $get  = mb_detect_encoding($text, array('utf-8','UTF-8','cp1251'));
    $text =  iconv($get,'UTF-8',$text);

    $result = '';
    $word = '';
    

    for ($i = 0; $i < strlen($text); $i++) {
        if((ord($text[$i])>96 && ord($text[$i])<123)
        ||(ord($text[$i])>64 && ord($text[$i])<90)
        ||(ord($text[$i])== 45)
        ||(ord($text[$i])== 39)){
        $word .= $text[$i];
        }else{
            if ($word){
            $result .= "<span class = ";
            $result .= 'word>';
            $result .= $word;
            $translate = wrapTranslate($word, $level); //оборачиваем перевод
            $result .= $translate;
            $result .= "</span>";
            $word = '';
            $result .= $text[$i];
         } else {
             $result .= $text[$i];
          }
        }
      }
    
    include('readerTemplate.html');  
}

function lemmatization($word){
       //return $word;
  require_once( 'phpmorphy/src/common.php');

  // Укажите путь к каталогу со словарями
  $dir = 'phpmorphy\dicts';

  // Укажите, для какого языка будем использовать словарь.
  // Язык указывается как ISO3166 код страны и ISO639 код языка,
  // разделенные символом подчеркивания (ru_RU, uk_UA, en_EN, de_DE и т.п.)

  $lang = 'en_EN';

  // Укажите опции
  // Список поддерживаемых опций см. ниже
  $opts = array(
      'storage' => PHPMORPHY_STORAGE_FILE,
  );

  // создаем экземпляр класса phpMorphy
  // обратите внимание: все функции phpMorphy являются throwable т.е.
  // могут возбуждать исключения типа phpMorphy_Exception (конструктор тоже)
  try {
      $morphy = new phpMorphy($dir, $lang, $opts);
  } catch(phpMorphy_Exception $e) {
      die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
  }
  $word = strtoupper($word);
  //$w = $morphy->lemmatize($word, phpMorphy::NORMAL);
  $w = $morphy->getBaseForm($word);

  // далее под $morphy мы подразумеваем экземпляр класса phpMorphy


  return $w[0];
//  return $word;
}



function findWordFrequency($word)
{

  global $link;
  $word = lemmatization($word);
  $query ="SELECT * FROM vocab WHERE word = \"$word\"";
  $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));

  if($result)
  {	$row = mysqli_fetch_row($result);
    return array($row[2], $row[3]);
  }else {
    return array('', 0 );
  }
}

function wrapTranslate($word, $level)
{
    $res = "<span class = ";
    list($translate, $wordFrequency) = findWordFrequency($word);
     //если частотность подходит
     $searchFrequency = 0;

     switch ($level) {
     case 1:
         $searchFrequency = 1222421;
         break;
     case 2:
         $searchFrequency = 1035;
         break;
     case 3:
         $searchFrequency = 350;
         break;
     case 4:
         $searchFrequency = 260;
         break;
     case 5:
         $searchFrequency = 210;
         break;
     case 6:
         $searchFrequency = 160;
         break;
     case 7:
         $searchFrequency = 120;
         break;
     case 8:
         $searchFrequency = 80;
         break;
     case 9:
         $searchFrequency = 50;
         break;
     case 10:
         $searchFrequency = 40;
         break;
    }


     if (($wordFrequency < $searchFrequency) && $translate != ''){
         $res .= 'on>';
     }else {
        $res .= 'off>';
     }
      $res .= '(';
      $res .= $translate;
      $res .= ')';
      $res .= "</span>";



    return $res;
}

//----------------------------------------------//
//----------------------------------------------//

if (isset($_COOKIE['cookie'])){
    $cookie = $_COOKIE['cookie'];
    echo $cookie;
    if($_FILES['file']){
        updateBookDB($cookie);
        loadBook($cookie);
    }else{
        echo 'Что-то ты долго читаешь эту книгу';
        loadBook($cookie);
    }   
}else{
    if(is_uploaded_file($_FILES['file']['tmp_name'])){
        echo 'Здравствуй, новый пользователь';
        $cookie = cookieGen();
        SetCookie("cookie",$cookie,time()+3600,1);
        addNewCookieDB($cookie);
        loadBook($cookie);
        
    }else{
        echo 'загрузите, пожалуйста, книгу, молодой человек';
    } 
}

?>
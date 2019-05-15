<?php
$text = $_GET['text'];

$fd = fopen("hello.txt", 'a+') or die("не удалось открыть файл");
fseek($fd, 0, SEEK_END); // поместим указатель в конец
fwrite($fd, "\r\n" .$text); // запишем в конце еще одну строку
fclose($fd);
?>

var container = document.getElementsByClassName('text');
var wordList = document.getElementsByClassName('wordList');
var nextPage = document.getElementsByClassName('pageSwitchRight');
var prevPage = document.getElementsByClassName('pageSwitchLeft');
var pageNum = document.getElementsByClassName('pageNum'); //элемент номер страницы
var pageNumber = 1; //текущая страница
var level = document.getElementsByClassName('slider'); //уровень
/*ajax*/
var xhttp;
if (window.XMLHttpRequest){
   xhttp=new XMLHttpRequest();
}
else {
   xhttp=new ActiveXObject("Microsoft.XMLHTTP");
}


/*---events---------*/

//скрытие и показ перевода по клику
container[0].onclick = function(event) {

 if (!event.target.classList.contains('word')) return;

	if ((event.target.lastChild.classList.contains('off'))
  &&(event.target.lastChild.innerText!== "()")){

		event.target.lastChild.className = "on";
		event.target.lastChild.style.display = "inline";
    var word = event.target.firstChild.textContent;

    var translate = event.target.lastChild.innerText;
    AddWordInList(word + ' ' + translate); //заносим в словарь на странице

	}else if(event.target.lastChild.classList.contains('on')){

		event.target.lastChild.className = "off";
		event.target.lastChild.style.display = "none";
	}
}

//перелистывание страницы
nextPage[0].onclick = function(event) {
  incPage();
}
nextPage[1].onclick = function(event) {
  incPage();
}
prevPage[0].onclick = function(event) {
  decPage();;
}
prevPage[1].onclick = function(event) {
  decPage();
}

//изменение уровня на странице

level[0].onchange = function(event) {
  getTextPageAjax(pageNumber)
}


//сказать привет разработчику



function sayHello(){

  var mouth = document.getElementsByClassName('mouth');
  var button = document.getElementsByClassName('sayHelloButton');
  mouth[0].style.transition = "width 1s, border-radius 1s, left 1s"
  mouth[0].style.width = "20px";
  mouth[0].style.left = "30px";
  mouth[0].style.borderRadius = "20px 20px 50px 50px";
  button[0].style.backgroundColor = "#a855d9";

  var now = new Date();
  xhttp.open("GET","ajaxHello.php?text=" + 'hello! ' + now,true);
  xhttp.send();
  xhttp.onreadystatechange=function(){
   if (xhttp.readyState==4){
   }
  }
}

/*---main-----*/


/*---functions-----*/

//Добавить неизвестное слово в список слов на странице
function AddWordInList(word){
  var newLi = document.createElement('li');
  newLi.innerHTML = '<pre>'+ word + '</pre>';
  wordList[0].insertBefore(newLi, wordList[0].firstChild);
}

//Перейти к следующей странице
function incPage(){
  //проверить не последняя ли страница
  //изменить текущую
  pageNumber++;
  pageNum[0].innerText = 'Page '+pageNumber;
  pageNum[1].innerText = 'Page '+pageNumber;
  getTextPageAjax(pageNumber);
}

//Вернуться на предыдущую страницу
function decPage(){
  //проверить не первая ли это страница
  //изменить текущую страницу
  pageNumber--;
  pageNum[0].innerText = 'Page '+pageNumber;
  pageNum[1].innerText = 'Page '+pageNumber;
  getTextPageAjax(pageNumber);
}


//запросить и загрузить страничку
function getTextPageAjax(pageNumber){

  xhttp.open("GET","ajaxHandler.php?page=" + pageNumber + "&level=" + (+level[0].value) ,true);
  xhttp.send();
  xhttp.onreadystatechange=function(){
   if (xhttp.readyState==4){
      loadTextInPage(xhttp.responseText); //вставить в ДОМ страницу
   }
  }
}

function loadTextInPage(text){
  document.getElementsByClassName('t')[0].innerHTML = text;
  //document.getElementsByClassName('wordList')[0].innerHTML = text;
}

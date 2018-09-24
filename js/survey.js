/*@cc_on @if (@_win32 && @_jscript_version >= 5) if (!window.XMLHttpRequest)
window.XMLHttpRequest = function() { return new ActiveXObject('Microsoft.XMLHTTP') }
@end @*/

var sessionHash;
var questions = [];
var buttonClicked = "no idea";
var needToConfirm = true;
var qleft;
var qtotal;
var emptyVar = "empty";
var slidePage = 1 //slideshow tracker;
var slideFrontier = 1 //furthest slide so far;
var slideMax = 15 //number of slides;

var hovers = [];

window.onbeforeunload = confirmExit;
function confirmExit()
{
	if(needToConfirm){
  		return 'Note that the "back" and "refresh" buttons do not work during the study.\n\nPlease choose "stay on this page" if you want to continue the study.';
	}
}

function setSession(hash){
	this.sessionHash = hash;
}

function postAnswers(answerVars){
	ajax("./controllers/answerController.php","session=" + this.sessionHash + answerVars, updateInterface);
}

function updateInterface(code){
	document.getElementById("debug").innerHTML = ('<pre>'+code+'</pre>');
	//request interface
	ajax('./controllers/surveyController.php','session=' + this.sessionHash, buildInterface);
}

function buildInterface(code){
	if(code == " postsurvey done"){
		done("postsurvey");
	}else if(code == " presurvey done"){
		done("presurvey");
	}else{
		document.getElementById("surveyContent").innerHTML = code;
		
		questions = [];
		var form = document.getElementById('questions');
		if(form){
			var qdivs = getElementsByClassName(form,'div','question');
			for(var x=0;x<qdivs.length;x++){
				var id = qdivs[x].id;
				id = id.substring(2);
				questions[x] = new Object();
				questions[x].id = id;
				questions[x].questionDiv = qdivs[x];
				questions[x].required = qdivs[x].className.indexOf('optional') == -1
				
				questions[x].answerType = document.getElementById(id).className;	
				var qarr = questions[x].answerType.split("_");
				questions[x].answerType = qarr[0];
				
				questions[x].answerDiv = document.getElementById(id + '_text');
				questions[x].options = qdivs[x].getElementsByTagName('input');
				questions[x].allOptions = [];
			
				if(questions[x].answerType == "dropdown"){
					questions[x].options = qdivs[x].getElementsByTagName('option');
				}
				questions[x].answer = '';
				questions[x].status = 'empty';
			}
			if(questions[0] != null){
				if(questions[0].answerDiv != null){
					questions[0].answerDiv.focus();
				}
			}
		}
		if(document.getElementById("counter") != null){
			var qstring = document.getElementById("counter").className;
			qleft = qstring.split("-")[0];
			qtotal = qstring.split("-")[1];
		}
		
		checkForm();
		scroll(0,0);
	}
}

function ajax(url, vars, callbackFunction) {
  var request =  new XMLHttpRequest();
  request.open("POST", url, true);
  request.setRequestHeader("Content-Type",
                          "application/x-www-form-urlencoded");
 
  request.onreadystatechange = function() {
    var done = 4, ok = 200;
    if (request.readyState == done && request.status == ok) {
      if (request.responseText) {
        callbackFunction(request.responseText);
      }
    }
  };
  request.send(vars);
}

function checkForm(){
	getData();
	updateDisplay();
}

function getStatus(question){
	if(question.answerType == 'other'){
		return 'ok';
	}
	if(question.answer.length == 0 || (question.answerType == "dropdown" && question.answer == "none")){
		return emptyVar;
	}
	switch(question.answerType){
		case 'int':
			var ok = /^-?\d+$/.test(question.answer);
			if(question.answer < 0 || !ok){
				return 'bad';
			}
			break;
		case '1-10':
			var ok = /^-?\d+$/.test(question.answer);
			if(question.answer > 10 || question.answer < 1 || !ok){
				return 'bad';
			}
			break;
		case '1-100':
			var ok = /^-?\d+$/.test(question.answer);
			if(question.answer > 100 || question.answer < 1 || !ok){
				return 'bad';
			}
			break;
		
	}
	return 'ok';
}

function updateDisplay(){
	var allGood = true;
	var done = 0
	
	//audio hack
	if(document.getElementById("myPlayer") !== null & slideFrontier < slideMax){
		allGood = false;
	}
	
	for(var x=0; x<questions.length; x++){
		questions[x].questionDiv.className = questions[x].questionDiv.className.replace('bad','');
		questions[x].questionDiv.className = questions[x].questionDiv.className.replace('ok','');
		questions[x].questionDiv.className = questions[x].questionDiv.className.replace('empty','');
		questions[x].questionDiv.className = questions[x].questionDiv.className.replace('tried','');
		questions[x].questionDiv.className += ' ' + questions[x].status;
		if(questions[x].status == 'bad' || ((questions[x].status == 'empty' || questions[x].status == 'empty tried') && questions[x].required && questions[x].answerType != 'othertext') || ((questions[x].status == 'empty' || questions[x].status == 'empty tried') && questions[x].answerType == 'othertext' && questions[x].questionDiv.style.visibility == "visible") || (questions[x].answerType == "checkbox" && questions[x].answer.length == 0 && questions[x].required)){
			allGood = false;
		}
		if(questions[x].status == 'ok' && questions[x].required && questions[x].answerType != "other"){
			done++;
		}
	}
	if(document.getElementById('nextButton') != null){
		document.getElementById('nextButton').className = "";
		if(!allGood){
			document.getElementById('nextButton').className = "disabled";
		} //empty skin!!
	}
	var dperc = (qtotal - qleft + done)/qtotal;
	if(qtotal == 1){
		dperc = 1.01;
	} //ugly postsurvey hack
	document.getElementById('counter').style.width = (dperc*200) + "px";
	document.getElementById('counterText').innerHTML = Math.round(99.1*dperc,0) + "% done";
	
	return allGood;
}

function submitEnter(){
	emptyVar = "empty tried";
	if(updateDisplay()){
		submitForm();
	}
}

function submitForm(){
	emptyVar = "empty";
	getData();
	
	//save the hovers
	var hovervars = "";
	for(var x=0;x<hovers.length;x++){
		hovervars += "&h" + x + "=" + encodeURIComponent(hovers[x]);
	}
	postHovers(hovervars);
	
	var postvars = "";
	for(var x=0;x<questions.length;x++){
		/*if(questions[x].answer instanceof Array){
			for(var y=0;y<questions[x].answer.length;y++){
				postvars += "&" + encodeURIComponent(questions[x].id) + "-" + y + "=" + encodeURIComponent(questions[x].answer[y]);
			}
		}else{*/
			postvars += "&" + encodeURIComponent(questions[x].id) + "=" + encodeURIComponent(questions[x].answer);
			//}
	}
	postAnswers(postvars);
	
}

function postHovers(answerVars){
	ajax("./controllers/hoverController.php","session=" + this.sessionHash + answerVars,doNothing);
}

function doNothing(i){
	return;
}


function submitFormVar(clicked){
	var level = document.getElementById('level');
	buttonClicked = clicked + " " + level.className;
	if(clicked == "yes" && !updateDisplay()){
		for(var x=0;x<questions.length;x++){
			if(questions[x].status == 'empty' && (questions[x].answerType != 'othertext' || questions[x].questionDiv.style.visibility == "visible")){
				questions[x].questionDiv.firstChild.innerHTML += " (!)";
				questions[x].questionDiv.firstChild.style.color = "red";
			}else{
				questions[x].questionDiv.firstChild.innerHTML = questions[x].questionDiv.firstChild.innerHTML.replace("(!)","");
				questions[x].questionDiv.firstChild.style.color = "black";
			}
		}
	}else{
		submitForm();
	}
}

function getData(){
	for(var x=0;x<questions.length;x++){
		questions[x].answer = getAnswer(questions[x]);
		questions[x].status = getStatus(questions[x]);
	}
}

function getAnswer(question){
	switch(question.answerType){
		case 'radio':
		case 'checkbox':
			var answerArray = [];
			for(var x=0;x<question.options.length;x++){
				if(question.options[x].checked){
					answerArray.push(question.options[x].value);
				}
			}
			return answerArray;
		case 'dropdown':
			var answer = "";
			for(var x=0;x<question.options.length;x++){
				if(question.options[x].selected){
					answer = question.options[x].value;
				}
			}
			return answer;
		case 'buttonsdiv':
			return buttonClicked;
		case 'other':
			return;
		default:
			return question.answerDiv.value;
	}
}

function exception(cond){	
	ajax("./controllers/eventController.php","session=" + this.sessionHash + "&action=exception&object=" + cond, updateInterface);
}

function checksubs(q,item,checked){
	label(q,item,checked);
	for(var x=0;x<questions.length;x++){
		if(questions[x].id - q < 100 && questions[x].id - q > 0){
			for(var y=0;y<questions[x].options.length;y++){
				if(questions[x].options[y].value == item){
					questions[x].options[y].checked = checked;
					if(checked){
						questions[x].options[y].parentNode.className += " chk";
					}else{
						questions[x].options[y].parentNode.className = questions[x].options[y].parentNode.className.replace(" chk","");
					}
				}
			}
		}
	}
}

function label(q,item,checked){
	for(var x=0;x<questions.length;x++){
		if(questions[x].id == q){
			for(var y=0;y<questions[x].options.length;y++){
				if(questions[x].options[y].value == item){
					if(questions[x].options[y].checked){
						questions[x].options[y].parentNode.className += " chk";
					}
				}else{
					questions[x].options[y].parentNode.className = questions[x].options[y].parentNode.className.replace(" chk","");
				}
			}
		}
	}
}

function showsub(q){
	for(var x=0;x<questions.length;x++){
		if(questions[x].id == q){
			questions[x].questionDiv.className = questions[x].questionDiv.className.replace("active","passive");
			var link = getElementsByClassName(questions[x].questionDiv,"div","sublink")[0];
			link.style.visibility = "hidden";
			for(var y=0;y<questions[x].options.length;y++){
				//questions[x].options[y].disabled = "disabled";
				questions[x].options[y].style.display = "none";
			}
		}
		else if(questions[x].id - q < 100 && questions[x].id - q > 0){
			questions[x].questionDiv.style.display = "block";
		}
	}
}

function skip(item){
	if(window.confirm("Do you want to skip this description?\n\nYou may skip a couple of them, but if you skip more than 9 you become ineligible to finish the study!\n\nClick Cancel if you think you might know someone who fits the description after all.")){
		item.parentNode.parentNode.parentNode.className += " disabled";
		item.parentNode.parentNode.style.display = "none";
		item.parentNode.parentNode.firstChild.value = ">skipped<";
		person = item.className - 10000;
		ajax("./controllers/eventController.php","session=" + this.sessionHash + "&action=skip&object=" + person, skipCheck);
	}
}

function skipCheck(q){
	if(q > 9){
		document.getElementById("surveyContent").innerHTML = '<div id="surveyHeader"><div id="surveyTitle">Sorry, but you have ran out of skips!</div><div id="explanation">Since you skipped too many labels, you are ineligible to finish this study.</div>';
	}
}

function arr_diff(a1, a2)
{
  var a=[], diff=[];
  for(var i=0;i<a1.length;i++)
    a[a1[i]]=true;
  for(var i=0;i<a2.length;i++)
    if(a[a2[i]]) delete a[a2[i]];
    else a[a2[i]]=true;
  for(var k in a)
    diff.push(k);
  return diff;
}


function getElementsByClassName(oElm, strTagName, strClassName){
	var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	strClassName = strClassName.replace(/\-/g, "\\-");
	var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
	var oElement;
	for(var i=0; i<arrElements.length; i++){
		oElement = arrElements[i];
		if(oRegExp.test(oElement.className)){
			arrReturnElements.push(oElement);
		}
	}
	return (arrReturnElements);
}

function done(type){
	needToConfirm = false;
	window.location = "controllers/sessionController.php?session=" + sessionHash + "&from=" + type;
}

//track hovers
function hover(action,object){
	details = Date.now();
	hovers.push(new Array(action,object,details));
}

//audio stuff
function audioStart(){
	var audioPlayer = document.getElementById("myPlayer");
	document.getElementById("slide").onclick = "";
	document.getElementById("slide").src = "images/en1.png";
	audioPlayer.play();
	document.getElementById("vidrepeat").disabled = false;
	audioPlayer.addEventListener('ended',audioDone);
}


function audioPlay(){
	slideFrontier = Math.max(slidePage,slideFrontier);
	if(slideFrontier == slideMax){
		document.getElementById("nextButton").className = "";
	}
	if(slidePage > 1){
		document.getElementById("vidprev").disabled = false;
	}else{
		document.getElementById("vidprev").disabled = true;
	}
	if(slidePage < slideFrontier){
		document.getElementById("vidnext").disabled = false;
	}else{
		document.getElementById("vidnext").disabled = true;
	}
	if(slidePage == slideMax - 1){
		document.getElementById("vidnext").disabled = true;
	}
	if(slidePage == slideMax){
		slidePage--;
	}else{
		document.getElementById("myPlayer").src = "audio/en" + slidePage + ".m4a";
		document.getElementById("slide").src = "images/en" + slidePage + ".png";
		document.getElementById("myPlayer").play();
	}
}

function vidprv(){
	slidePage--;
	audioPlay();
}

function vidrep(){
	audioPlay();
}

function vidnxt(){
	slidePage++;
	audioPlay();
}

function audioDone(){
	slidePage++;
	audioPlay();
}
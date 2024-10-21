init();

function init(){
    $("submit").onclick = initgame;
    $("logout").onclick = endgame;
    $("submitInput").onclick = submitInput;
    hide("game");
}

function initgame(){
    var username = $("username").value;
    var psw = $("psw").value;
    var url="PHP/login.php";
    var param="username="+encodeURIComponent(username)+"&psw="+encodeURIComponent(psw);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.onreadystatechange = function(){
        if (xmlHttpRequest.readyState === 4 && xmlHttpRequest.status === 200){
            connection(xmlHttpRequest.responseText);
        }
    }
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
}

function connection(response){
    if(response === "success"){
        showGameScreen();
        console.log("Game started");
    }else if(response === "fail"){
        $("psw").value = "";
        console.log("Password incorrect");
    }
    else{
        $("psw").value = "";
        console.log("Game not started");
    }
}

function showGameScreen(){
    $("psw").value = "";
    $("username").value = "";
    hide("auth");
    show("game");
    $("gameInput").focus();
}

function endgame(){
    console.log("Game ended");
    $("area").value = "";
    hideGameScreen();
}

function hideGameScreen(){
    hide("game");
    show("auth");
    $("username").focus();
}

function submitInput(){
    var gameInput = $("gameInput").value;
    var url="PHP/AddCommand.php";
    var param="gameInput="+encodeURIComponent(gameInput);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.onreadystatechange = function(){
        if (xmlHttpRequest.readyState === 4 && xmlHttpRequest.status === 200){
            showCommand(xmlHttpRequest.responseText);
        }
    }
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
}

function showCommand(response){
    var area = $("area");
    var gameInput = $("gameInput");
    if (response !== "problem") {
        area.value += response + "\n";
        area.scrollTop = area.scrollHeight;
    }
    gameInput.value = "";
    gameInput.focus();
}
init();

function init(){
    var logout = $("logout");
    var submitInput = $("submitInput");
    $("submit").onclick = login;
    logout.onclick = endgame;
    submitInput.onclick = getCommand;
    submitInput.disabled = true;
    logout.disabled = true;
    hide("game");
}

function login(){
    var username = $("username").value;
    var psw = $("psw").value;
    var url="PHP/Login.php";
    var param="username="+encodeURIComponent(username)+"&psw="+encodeURIComponent(psw);
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessLogin(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function loadGame(){
    var url="PHP/Load.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            showGameScreen();
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send();
}

function getAffichage(){
    var url="PHP/GetAffichage.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            affichage(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send();
}

function affichage(str){
    var result = JSON.parse(str);
    var affichage = result["affichage"];
    var area = $("area");
    for(var i = 0; i < affichage.length; i++) {
        area.value += affichage[i] + "\n\n";
    }
    area.scrollTop = area.scrollHeight;
    stateMachine();
}

function ProcessLogin(response){
    $("psw").value = "";
    if(response !== "error"){
        $("username").value = "";
        loadGame();
    }
}

function showGameScreen(){
    hide("auth");
    show("game");
    $("gameInput").focus();
    getAffichage();
}

function hideGameScreen(){
    $("area").value = "";
    $("gameInput").value = "";
    hide("game");
    show("auth");
    $("username").focus();
}

function endgame(){
    var url="PHP/Save.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            hideGameScreen();
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send();
}

function clearArea(){
    $("area").innerHTML = "";
}

function action(str) {
    var result = JSON.parse(str);
    switch(result["action"]){
        case "CMD":
            $("submitInput").disabled = false;
            $("logout").disabled = false;
            $("gameInput").focus();
            break;
        case "CLEAR":
            clearArea();
            stateMachine();
            break;
        case "TEXT":
            var area = $("area");
            area.value += result["str"] + "\n\n";
            area.scrollTop = area.scrollHeight;
            stateMachine();
            break;
        case "RESET":
            reset();
            break;
        case "NOP":
            stateMachine();
            break;
    }
}


function reset(){
    var url="PHP/Init.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            stateMachine();
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send();
}

function getCommand(){
    $("submitInput").disabled = true;
    $("logout").disabled = true;
    var gameInput = $("gameInput");
    var command = gameInput.value;
    var url="PHP/Command.php";
    var param="command="+encodeURIComponent(command);
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            action(xhr.responseText);
            gameInput.value = "";
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);

}

function stateMachine(){
    var url="PHP/Command.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            action(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send();
}

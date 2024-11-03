init();

function init(){
    $("submit").onclick = login;
    $("logout").onclick = endgame;
    var submitInput = $("submitInput");
    submitInput.onclick = getCommand;
    submitInput.disabled = true;
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
    console.log(result);
    //TODO: afficher le tableau
    var area = $("area");
    for(var i = 0; i < result.length; i++) {
        area.value += result[i] + "\n\n";
    }
    area.scrollTop = area.scrollHeight;
    $("gameInput").focus();
    stateMachine();
}

function ProcessLogin(response){
    $("psw").value = "";
    if(response !== "error"){
        $("username").value = "";
        loadGame()
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
            hideGameScreen()
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send();
}

/**
function submitInput(){
    var gameInput = $("gameInput").value;
    var url="PHP/AddCommand.php";
    var param="gameInput="+encodeURIComponent(gameInput);
    var param="gameInput="+encodeURIComponent(gameInput);
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            showCommand(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function showCommand(str){
    var result = JSON.parse(str);
    var area = $("area");
    var gameInput = $("gameInput");
    for(var i = 0; i < result.length; i++) {
        if (result[i]==="Game Over") {
            endgame();
            return;
        }
        area.value += result[i] + " ";
    }
    area.value += "\n";
    area.scrollTop = area.scrollHeight;
    gameInput.value = "";
    gameInput.focus();
}*/

function action(str) {
    var result = JSON.parse(str);
    console.log(result["action"]);
    switch(result["action"]){
        case "CMD":
            $("submitInput").disabled = false;
            break;
        case "TEXT":
            showDesc(result["str"], result["clear"]);
            break;
        case "RESET":
            reset();
            break;
        case "NOP":
            stateMachine();
            break;
    }
}

function showDesc(str, clear){
    var area = $("area");
    if(clear){
        area.innerHTML = "";
    }
    area.innerHTML += str + "\n\n";
    area.scrollTop = area.scrollHeight;
    stateMachine();
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
    console.log("getCommand");
    var gameInput = $("gameInput");
    var command = gameInput.value;
    var url="PHP/Command.php";
    var param="command="+encodeURIComponent(command);
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            action(xhr.responseText);
            $("submitInput").disabled = true;
            gameInput.value = "";
            gameInput.focus();
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

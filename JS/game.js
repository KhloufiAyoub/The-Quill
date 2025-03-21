init();
setInterval(startSession, 180000);
var timeOutId=null;

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
        area.value += affichage[i].replace(/<br>/g, "\n") + "\n\n";
    }
    area.scrollTop = area.scrollHeight;
    stateMachine();
}

function ProcessLogin(response){
    var psw = $("psw");
    psw.value = "";
    if(response !== "error"){
        $("username").value = "";
        loadGame();
    }else{
        psw.focus();
    }
}

function showGameScreen(){
    hide("auth");
    show("game");
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

function action(str) {
    var result = JSON.parse(str);
    var area = $("area");
    timeOutId = null;
    var submitInput = $("submitInput");
    var logoutSubmit = $("logout");
    var gameInput = $("gameInput");
    switch(result["action"]){
        case "CMD":
            submitInput.disabled = false;
            logoutSubmit.disabled = false;
            gameInput.focus();
            break;
        case "CLEAR":
            area.value  = "";
            stateMachine();
            break;
        case "TEXT":
            area.value += result["str"].replace(/<br>/g, "\n") + "\n\n";
            area.scrollTop = area.scrollHeight;
            stateMachine();
            break;
        case "RESET":
            reset();
            break;
        case "NOP":
            stateMachine();
            break;
        case "PAUSE":
            if(result["time"] != null){
                timeOutId = setTimeout(stateMachine, result["time"]);
            }
            break;
        case "LOGOUT":
            logout();
            break;
        case "ANYKEY":
            area.value += result["str"].replace(/<br>/g, "\n") + "\n\n";
            document.addEventListener("keydown", function handler() {
                stateMachine();
                document.removeEventListener("keydown", handler);
            });
            document.addEventListener("touchstart", function handler() {
                stateMachine();
                document.removeEventListener("touchstart", handler);
            });
            break;
        case "YESNO":
            area.value += result["str"].replace(/<br>/g, "\n") + "\n\n";
            area.scrollTop = area.scrollHeight;
            submitInput.disabled = false;
            logoutSubmit.disabled = false;
            gameInput.focus();
            break;
        case "SAVESLOT":
            area.value += result["str"].replace(/<br>/g, "\n") + "\n\n";
            area.value += "Remaining slots : " + result["slots"] + "\n\n";
            area.scrollTop = area.scrollHeight;
            stateMachine();
            break;
    }
}

function logout(){
    var url="PHP/Logout.php";
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
    if(timeOutId != null){
        clearTimeout(timeOutId);
    }
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

function startSession() {
    var url = "PHP/SessionStart.php";
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.send();
}

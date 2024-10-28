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
    var url="PHP/Login.php";
    var param="username="+encodeURIComponent(username)+"&psw="+encodeURIComponent(psw);
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            connection(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
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
    hide("game");
    show("auth");
    $("username").focus();
}

function submitInput(){
    var gameInput = $("gameInput").value;
    var url="PHP/AddCommand.php";
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
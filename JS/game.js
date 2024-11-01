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
            login(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function login(response){
    $("psw").value = "";
    if(response === "success"){
        $("username").value = "";
        showGameScreen();
        console.log("Game started");
    }else if(response === "fail"){
        console.log("Password incorrect");
    }
    else{
        console.log("Game not started");
    }
}

function showGameScreen(){
    hide("auth");
    show("game");
    $("gameInput").focus();
}

function endgame(){
    $("area").value = "";
    $("gameInput").value = "";
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
}

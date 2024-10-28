init();

function init(){
    $("RoomButton").onclick = ShowRoom;
    $("MsgButton").onclick = ShowMessage;
    $("ObjButton").onclick = ShowObject;
    $("RoomSubmit").onclick = AddRoom;
    $("RoomCancel").onclick = function () {hide("AddRoom");};
    $("MsgSubmit").onclick = AddMessage;
    $("MsgCancel").onclick = function () {hide("AddMsg");};
    $("ObjSubmit").onclick = AddObject;
    $("ObjCancel").onclick = function () {hide("AddObj");};
    $("submitWord").onclick = EditWord;
    $("submitObj").onclick = EditObj;
    $("submitSave").onclick = EditSave;
    $("GetRoom").onclick = GetRoom;
    $("GetMessage").onclick = GetMessage;
    $("GetSmsg").onclick = GetSmsg;
    $("GetMove").onclick = GetMove;
    $("GetObject").onclick = GetObject;
    GetMaxValue();
}

function ShowRoom(){
    hide("AddMsg");
    hide("AddObj");
    show("AddRoom");
}

function ShowMessage(){
    hide("AddRoom");
    hide("AddObj");
    show("AddMsg");
}

function ShowObject(){
    hide("AddRoom");
    hide("AddMsg");
    show("AddObj");
}


function Add(url,param, element){
    element.value="";
    var xhr= new XMLHttpRequest();
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function AddRoom(){
    var roomName = $("RoomValue");
    var url="PHP/AddRoom.php";
    var param="room="+encodeURIComponent(roomName.value);
    Add(url,param,roomName);
}

function AddMessage(){
    var msgName = $("MsgValue");
    var url="PHP/AddMsg.php";
    var param="msg="+encodeURIComponent(msgName.value);
    Add(url,param,msgName);
}

function AddObject(){
    var objName = $("ObjValue");
    var url="PHP/AddObj.php";
    var param="obj="+encodeURIComponent(objName.value);
    Add(url,param,objName);
}

function Edit(url,param){
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            GetMaxValue();
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function EditWord(){
    var wordSize = $("wordSize").value;
    var url="PHP/EditWord.php";
    var param="wordSize="+encodeURIComponent(wordSize);
    Edit(url,param);
}

function EditObj(){
    var maxObj = $("maxObj").value;
    var url="PHP/EditObj.php";
    var param="maxObj="+encodeURIComponent(maxObj);
    Edit(url,param);
}

function EditSave(){
    var maxSave = $("maxSave").value;
    var url="PHP/EditSave.php";
    var param="maxSave="+encodeURIComponent(maxSave);
    Edit(url,param);
}

function GetMaxValue(){
    var url="PHP/GetMaxValue.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            SplitValue(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function SplitValue(response){
    var split = response.split(",");
    var wordSize_p = $("wordSize_p");
    var maxObj_p = $("maxObj_p");
    var maxSave_p = $("maxSave_p");
    wordSize_p.innerHTML="";
    maxObj_p.innerHTML="";
    maxSave_p.innerHTML="";
    wordSize_p.textContent+="Longueur maximum d'un mot : "+split[2];
    maxObj_p.textContent+="Nombre maximum d'objets dans l'inventaire : "+split[0];
    maxSave_p.textContent+="Nombre maximum de sauvegarde : "+split[1];
    $("wordSize").value="";
    $("maxObj").value="";
    $("maxSave").value="";
}

function Get(url, elements, isDeletable=true, isEditable=true, UseInnerHTML=false){
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequest(xhr.responseText, elements, isDeletable, isEditable, UseInnerHTML);
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function GetRoom(){
    var url="PHP/GetRoom.php";
    Get(url,["rid","roomdesc"],true,true,true);
}

function GetMessage(){
    var url="PHP/GetMessage.php";
    Get(url,["mid","message"]);
}

function GetSmsg(){
    var url="PHP/GetSmsg.php";
    Get(url,["smid","message"], false, false)
}

function GetMove(){
    var url="PHP/GetMove.php";
    Get(url,["rid","word", "newroom"], true,false)
}

function GetObject(){
    var url="PHP/GetObj.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequestObj(xhr.responseText,["objid","objdesc", "startloc"]);
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function InsertImg(tr, source){
    var img = document.createElement("IMG");
    var td = document.createElement("TD");
    img.src=source;
    td.appendChild(img);
    tr.appendChild(td);
}

function InsertColumn(tr, item, UseInnerHTML){
    var td = document.createElement("TD");
    if (typeof item === "string") {
        item = item.trunc(80, true, true);
    }
    if (UseInnerHTML){
        td.innerHTML=item;
    }else {
        var tn = document.createTextNode(item);
        td.appendChild(tn);
    }
    tr.appendChild(td);
}

function ProcessRequest(str,elements, isDeletable=true, isEditable=true, UseInnerHTML=false){
    var result = JSON.parse(str);
    var el=document.getElementById("maDiv");
    var i,j,tbl,tr;
    el.innerHTML="";
    tbl=document.createElement("TABLE");
    //Entête du tableau
    tr = document.createElement("TR");
    for (j=0;j<elements.length;j++){
        InsertColumn(tr, elements[j]);
    }
    tbl.appendChild(tr);
    //Corps du tableau
    for(i=0;i<result.length;i++) {
        tr=document.createElement("TR");
        //Insertion des colonnes
        for (j=0;j<elements.length;j++) {
            InsertColumn(tr, result[i][elements[j]],UseInnerHTML);
        }
        //Insertion des images de suppression
        if (isDeletable) {
            InsertImg(tr, "../img/icone-delete.png");
        }
        //Insertion des images d'édition
        if (isEditable) {
            InsertImg(tr, "../img/icone-edit.png");
        }
        tbl.appendChild(tr)
    }
    el.appendChild(tbl);
}

function ProcessRequestObj(str, elements){
    var result = JSON.parse(str);
    var el=document.getElementById("maDiv");
    var i, j,tbl,tr, td,select, options;
    el.innerHTML="";
    tbl=document.createElement("TABLE");
    tr = document.createElement("TR");
    for (j=0;j<elements.length;j++){
        InsertColumn(tr, elements[j]);
    }
    tbl.appendChild(tr);

    GetRoomArray(function (array) {
        for(i=0;i<result.length;i++) {
            tr=document.createElement("TR");
            for (j=0;j<elements.length;j++) {
                InsertColumn(tr, result[i][elements[j]]);
            }
            td = document.createElement("TD");
            select = document.createElement("SELECT");
            options = ["non créé", "transporté", "porté"].concat(array);
            options.forEach(optionText => {
                var option = document.createElement("OPTION");
                option.value = optionText;
                option.textContent = optionText;
                select.appendChild(option);
            });
            td.appendChild(select);
            tr.appendChild(td);
            InsertImg(tr,"../img/icone-delete.png");
            InsertImg(tr,"../img/icone-edit.png");
            tbl.appendChild(tr)
        }
    });
    el.appendChild(tbl);
}

function GetRoomArray(callback) {
    var url = "PHP/GetRoom.php";
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Fonction imbriquée pour traiter la réponse
            function ProcessRequestRoomArray(str, element) {
                var result = JSON.parse(str);
                var array = [];
                for (let i = 0; i < result.length; i++) {
                    array.push(result[i][element].trunc(20));
                }
                return array;
            }

            // Appel de la fonction imbriquée avec les données nécessaires
            var array = ProcessRequestRoomArray(xhr.responseText, "roomdesc");
            callback(array); // Exécute la fonction de rappel avec le tableau obtenu
        }
    };
    xhr.open("POST", url, true);
    xhr.send();
}




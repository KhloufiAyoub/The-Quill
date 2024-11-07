init();

function init(){
    $("RoomButton").onclick = ShowRoom;
    $("MsgButton").onclick = ShowMessage;
    $("ObjButton").onclick = ShowObject;
    $("MoveButton").onclick = ShowMove;
    $("VocabButton").onclick = ShowVocab;
    $("RoomSubmit").onclick = AddRoom;
    $("RoomCancel").onclick = function () {hide("AddRoom");};
    $("MsgSubmit").onclick = AddMessage;
    $("MsgCancel").onclick = function () {hide("AddMsg");};
    $("ObjSubmit").onclick = AddObject;
    $("ObjCancel").onclick = function () {hide("AddObj");};
    $("VocabSubmit").onclick = AddVocab;
    $("VocabCancel").onclick = function () {hide("AddVocab");};
    $("MoveSubmit").onclick = AddMove;
    $("MoveCancel").onclick = function () {hide("AddMove");};
    $("submitWord").onclick = EditWord;
    $("submitObj").onclick = EditObj;
    $("submitSave").onclick = EditSave;
    $("GetRoom").onclick = GetRoom;
    $("GetMessage").onclick = GetMessage;
    $("GetSmsg").onclick = GetSmsg;
    $("GetMove").onclick = GetMove;
    $("GetObject").onclick = GetObject;
    $("GetVocab").onclick = GetVocab;
    GetMaxValue();
}

function ShowRoom(){
    hide("AddMsg");
    hide("AddObj");
    hide("AddVocab");
    hide("AddMove");
    show("AddRoom");
}

function ShowMessage(){
    hide("AddRoom");
    hide("AddObj");
    hide("AddVocab");
    hide("AddMove");
    show("AddMsg");
}

function ShowObject(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddVocab");
    hide("AddMove");
    show("AddObj");
}

function ShowVocab(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddMove");
    show("AddVocab");
}

function ShowMove(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddVocab");
    show("AddMove");
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

function AddVocab(){
    console.log("AddVocab");
}

function AddMove(){
    console.log("AddMove");
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
    Get(url,["mid","message"],true,true,true);
}

function GetSmsg(){
    var url="PHP/GetSmsg.php";
    Get(url,["smid","message"], false, false, true)
}

function GetMove(){
    var url="PHP/GetMove.php";
    Get(url,["rid","word", "newroom"], true,false)
}

function GetVocab(){
    var url="PHP/GetVocab.php";
    Get(url,["wid","word"], true,false)
}

function GetObject(){
    var url="PHP/GetObj.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequestObj(xhr.responseText,["objid","objdesc"], ["startloc", "wid"]);
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
    var trunc_item = Truncate(item, 80);
    if (UseInnerHTML){
        td.innerHTML=trunc_item;
    }else {
        var tn = document.createTextNode(trunc_item);
        td.appendChild(tn);
    }
    tr.appendChild(td);
}

function ProcessRequest(str,elements, isDeletable, isEditable, UseInnerHTML){
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

function Truncate(str, length){
    if (typeof str === "string") {
        if (str.length > length) {
            str = str.substring(0, length) + "...";
        }
    }
    return str;
}

function InsertSelect(tr, array){
    var td = document.createElement("TD");
    var select = document.createElement("SELECT");
    var i;
    for (i = 0; i < array.length; i++) {
        var option = document.createElement("OPTION");
        option.value = array[i];
        option.textContent = array[i];
        select.appendChild(option);
    }
    td.appendChild(select);
    tr.appendChild(td);
}

function ProcessRequestObj(str, elements, elements2) {
    var j;
    var result = JSON.parse(str);
    var el = document.getElementById("maDiv");
    el.innerHTML = "";
    var tbl = document.createElement("TABLE");
    var tr = document.createElement("TR");
    for (j = 0; j < elements.length; j++) {
        InsertColumn(tr, elements[j]);
    }
    for (j = 0; j < elements2.length; j++) {
        InsertColumn(tr, elements2[j]);
    }
    tbl.appendChild(tr);

    result.forEach(function(item) {
        var tr = document.createElement("TR");
        elements.forEach(function(element) {
            InsertColumn(tr, item[element]);
        });

        /**
         * On avait fait une fonction pour récupérer les données des salles et du vocabulaire
         * mais lors de l'affichage, les select n'étaient pas tout le temps dans le bon ordre
         * On a donc décidé de regarder sur internet comment faire une promesse pour attendre que les deux tableaux soient chargés
         */
        Promise.all([
            GetValueArray("PHP/GetRoom.php", "roomdesc"),
            GetValueArray("PHP/GetVocab.php", "word")
        ]).then(function(arrays) {
            var location=["Non créé","Transporté", "Porté"].concat(arrays[0]);
            var vocab = ["-----"].concat(arrays[1]);
            InsertSelect(tr, location);
            InsertSelect(tr, vocab);
            InsertImg(tr, "../img/icone-delete.png");
            InsertImg(tr, "../img/icone-edit.png");
            tbl.appendChild(tr);
        });
    });

    el.appendChild(tbl);
}

function GetValueArray(url, element) {
    return new Promise(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    var result = JSON.parse(xhr.responseText);
                    var array = result.map(function(item) {
                        return Truncate(item[element],20);
                    });
                    resolve(array);
                } else {
                    reject("Error: " + xhr.status);
                }
            }
        };
        xhr.open("POST", url, true);
        xhr.send();
    });
}





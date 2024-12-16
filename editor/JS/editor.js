init();

const conditions = [
    { Cond: "TRUE", par1: null, par2: null },
    { Cond: "AT", par1: "room", par2: null },
    { Cond: "NOTAT", par1: "room", par2: null },
    { Cond: "ATGT", par1: "room", par2: null },
    { Cond: "ATLT", par1: "room", par2: null },
    { Cond: "PRESENT", par1: "objet", par2: null },
    { Cond: "ABSENT", par1: "objet", par2: null },
    { Cond: "WORN", par1: "objet", par2: null },
    { Cond: "NOTWORN", par1: "objet", par2: null },
    { Cond: "CARRIED", par1: "objet", par2: null },
    { Cond: "NOTCARR", par1: "objet", par2: null },
    { Cond: "CHANCE", par1: "1-100", par2: null },
    { Cond: "ZERO", par1: "flag", par2: null },
    { Cond: "NOTZERO", par1: "flag", par2: null },
    { Cond: "EQ", par1: "flag", par2: "valeur" },
    { Cond: "GT", par1: "flag", par2: "valeur" },
    { Cond: "LT", par1: "flag", par2: "valeur" }
];

const instructions = [
    { Instr: "NOP", par1: null, par2: null },
    { Instr: "INVENTORY", par1: null, par2: null },
    { Instr: "DESCRIBE", par1: null, par2: null },
    { Instr: "QUIT", par1: null, par2: null },
    { Instr: "END", par1: null, par2: null },
    { Instr: "DONE", par1: null, par2: null },
    { Instr: "OK", par1: null, par2: null },
    { Instr: "ANYKEY", par1: null, par2: null },
    { Instr: "SAVE", par1: null, par2: null },
    { Instr: "LOAD", par1: null, par2: null },
    { Instr: "TURNS", par1: null, par2: null },
    { Instr: "SCORE", par1: null, par2: null },
    { Instr: "CLS", par1: null, par2: null },
    { Instr: "DROPALL", par1: null, par2: null },
    { Instr: "AUTOGET", par1: null, par2: null },
    { Instr: "AUTODROP", par1: null, par2: null },
    { Instr: "AUTOWEAR", par1: null, par2: null },
    { Instr: "AUTOREM", par1: null, par2: null },
    { Instr: "GOTO", par1: "room", par2: null },
    { Instr: "REMOVE", par1: "objet", par2: null },
    { Instr: "GET", par1: "objet", par2: null },
    { Instr: "WEAR", par1: "objet", par2: null },
    { Instr: "DROP", par1: "objet", par2: null },
    { Instr: "CREATE", par1: "objet", par2: null },
    { Instr: "DESTROY", par1: "objet", par2: null },
    { Instr: "PLACE", par1: "objet", par2: "room" },
    { Instr: "SWAP", par1: "objet", par2: "objet" },
    { Instr: "PAUSE", par1: "duree", par2: null },
    { Instr: "MESSAGE", par1: "msg", par2: null },
    { Instr: "SET", par1: "flag", par2: null },
    { Instr: "CLEAR", par1: "flag", par2: null },
    { Instr: "PLUS", par1: "flag", par2: "valeur" },
    { Instr: "MINUS", par1: "flag", par2: "valeur" },
    { Instr: "LET", par1: "flag", par2: "valeur" }
];

function init(){
    $("RoomButton").onclick = ShowRoom;
    $("MsgButton").onclick = ShowMessage;
    $("ObjButton").onclick = ShowObject;
    $("MoveButton").onclick = ShowMove;
    $("VocabButton").onclick = ShowVocab;
    $("ActionButton").onclick = ShowAction;

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
    $("ActionSubmit").onclick = AddAction; //TODO : A faire
    $("ActionCancel").onclick = function () {hide("AddAction");};

    $("submitWord").onclick = EditWord; // reverif
    $("submitObj").onclick = EditObj;
    $("submitSave").onclick = EditSave;

    $("GetRoom").onclick = GetRoom;
    $("GetMessage").onclick = GetMessage;
    $("GetSmsg").onclick = GetSmsg;
    $("GetMove").onclick = GetMove;
    $("GetObject").onclick = GetObject;
    $("GetVocab").onclick = GetVocab;
    $("GetAction").onclick = GetAction;
    GetMaxValue();
}
//TODO : Tableau d'instructions et de conditions

function ShowRoom(){
    hide("AddMsg");
    hide("AddObj");
    hide("AddVocab");
    hide("AddMove");
    hide("AddAction");
    show("AddRoom");
}

function ShowMessage(){
    hide("AddRoom");
    hide("AddObj");
    hide("AddVocab");
    hide("AddMove");
    hide("AddAction");
    show("AddMsg");
}

function ShowObject(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddVocab");
    hide("AddMove");
    hide("AddAction");
    show("AddObj");
}

function ShowVocab(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddMove");
    hide("AddAction");
    show("AddVocab");
}

function ShowAction(str){
    var result = JSON.parse(str);
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddMove");
    hide("AddVocab");
    show("AddAction");

    var word1 = $("word1")
    word1.innerHTML = "";
    var word2 = $("word2")
    word2.innerHTML = "";

    var option = document.createElement("OPTION");
    var option2 = document.createElement("OPTION");
    option.text = "-----";
    option.value = null;
    word1.add(option);
    option2.text = "-----";
    option2.value = null;
    word2.add(option2);
    for(var i = 0; i <  result["vocab"].length; i++){
        option = document.createElement("OPTION");
        option.text = result["vocab"][i]["word"];
        option.value = result["vocab"][i]["wid"];
        word1.add(option);

        option2 = document.createElement("OPTION");
        option2.text = result["vocab"][i]["word"];
        option2.value = result["vocab"][i]["wid"];
        word2.add(option2);
    }

    var condition = $("condition")
    condition.innerHTML = "";
    option = document.createElement("OPTION");
    for(i = 0; i < conditions.length; i++){
        option = document.createElement("OPTION");
        option.text = conditions[i]["Cond"];
        option.value = conditions[i]["Cond"];
        condition.add(option);
    }
    var instruction = $("instruction")
    instruction.innerHTML = "";
    for(i = 0; i < instructions.length; i++){
        option = document.createElement("OPTION");
        option.text = instructions[i]["Instr"];
        option.value = instructions[i]["Instr"];
        instruction.add(option);
    }
    condition.onchange = ConditionHelper(result)
    instruction.onchange = InstructionHelper(result)
}

function InstructionHelper(result){
    return function(ev){
        var InstDiv = $("InstDiv");
        InstDiv.innerHTML = "";
        var target= ev.target;
        var instruction = target.value;
        var par1, par2;
        for (var i = 0; i < instructions.length; i++){
            if (instructions[i]["Instr"] === instruction){
                par1 = instructions[i]["par1"];
                par2 = instructions[i]["par2"];
            }
        }
        SwitchFunction(par1, result, InstDiv, 1);
        var br = document.createElement("BR");
        InstDiv.appendChild(br);
        SwitchFunction(par2, result, InstDiv, 2);
    }
}

function ConditionHelper(result){
    return function(ev){
        var CondDiv = $("CondDiv");
        CondDiv.innerHTML = "";
        var target= ev.target;
        var condition = target.value;
        var par1, par2;
        for (var i = 0; i < conditions.length; i++){
            if (conditions[i]["Cond"] === condition){
                par1 = conditions[i]["par1"];
                par2 = conditions[i]["par2"];
            }
        }
        SwitchFunction(par1, result,CondDiv,1);
        var br = document.createElement("BR");
        CondDiv.appendChild(br);
        SwitchFunction(par2, result, CondDiv,2);
    }
}

function SwitchFunction(par, result, div,num){
    var label = document.createElement("LABEL");
    var id = div.id === "CondDiv" ? "CondParam"+num : "InstParam"+num
    var input = document.createElement("INPUT");
    switch(par){
        case "room":
            label.textContent = "Piece : ";
            label.htmlFor = id;
            div.appendChild(label);
            InsertSelect(div, result["room"], "roomdesc", "rid", false, "", [], id);
            break;
        case "objet":
            label.textContent = "Objet : ";
            label.htmlFor = id;
            div.appendChild(label);
            InsertSelect(div, result["obj"], "objdesc", "objid", false, "", [], id);
            break;
        case "flag":
            label.textContent = "Flag : ";
            id = div.id === "CondDiv" ? "CondFlag" + num: "InstFlag"+ num;
            label.htmlFor = id;
            div.appendChild(label);
            input = document.createElement("INPUT");
            input.type = "number";
            input.id = id;
            input.min = 0;
            div.appendChild(input);
            break;
        case "1-100":
            label.textContent = "Valeur de 1 à 100: ";
            id = div.id === "CondDiv" ? "Cond1-100_" + num: "Inst1-100_" + num;
            label.htmlFor = id;
            div.appendChild(label);
            input = document.createElement("INPUT");
            input.type = "number";
            input.id = id;
            input.min = 1;
            input.max = 100;
            div.appendChild(input);
            break;
        case null:
            break;
        case "valeur":
            label.textContent = "Valeur : ";
            id = div.id === "CondDiv" ? "CondVal" + num: "InstVal" + num;
            label.htmlFor = id;
            div.appendChild(label);
            input = document.createElement("INPUT");
            input.type = "number";
            input.id = id;
            div.appendChild(input);
            break;
        case "msg":
            label.textContent = "Message : ";
            label.htmlFor = id;
            div.appendChild(label);
            InsertSelect(div, result["msg"], "message", "mid", false, "", [], id);
            break;
        case "duree" :
            label.textContent = "Durée en millisecondes : ";
            id = div.id === "CondDiv" ? "CondDuree" + num: "InstDuree" + num;
            label.htmlFor = id;
            div.appendChild(label);
            input = document.createElement("INPUT");
            input.type = "number";
            input.id = id;
            input.min = 0;
            div.appendChild(input);
            break;
    }
}

function ShowMove(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddVocab");
    hide("AddAction");
    show("AddMove");

    var maDiv = $("MoveSelect");
    maDiv.innerHTML = "";
    Promise.all([
        GetValueArray("PHP/GetRoom.php"),
        GetValueArray("PHP/GetVocab.php"),
        GetValueArray("PHP/GetRoom.php")
    ]).then(function(arrays) {
        var label = document.createElement("LABEL");
        label.textContent = "Déplacement : ";
        label.htmlFor = "rid";
        maDiv.appendChild(label);
        InsertSelect(maDiv, arrays[0], "roomdesc", "rid", false,"", [], "rid");
        maDiv.appendChild(document.createElement("BR"))
        label = document.createElement("LABEL");
        label.textContent = "Mot : ";
        label.htmlFor = "wid";
        maDiv.appendChild(label);
        InsertSelect(maDiv, arrays[1],"word", "wid",  false, "",[],"wid");
        maDiv.appendChild(document.createElement("BR"))
        label = document.createElement("LABEL");
        label.textContent = "Nouvelle pièce : ";
        label.htmlFor = "newroom";
        maDiv.appendChild(label);
        InsertSelect(maDiv, arrays[2],"roomdesc", "rid", false, "",[],"newroom");
        maDiv.appendChild(document.createElement("BR"))
    });
}

function Add(url,param, elements){
    for (var i = 0; i < elements.length; i++){
        elements[i].value = "";
    }
    var xhr= new XMLHttpRequest();
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function AddRoom(){
    var roomName = $("RoomValue");
    var url="PHP/AddRoom.php";
    var param="room="+encodeURIComponent(roomName.value);
    Add(url,param,[roomName]);
}

function AddMessage(){
    var msgName = $("MsgValue");
    var url="PHP/AddMsg.php";
    var param="msg="+encodeURIComponent(msgName.value);
    Add(url,param,[msgName]);
}

function AddObject(){
    var objName = $("ObjValue");
    var url="PHP/AddObj.php";
    var param="obj="+encodeURIComponent(objName.value);
    Add(url,param,[objName]);
}

function AddVocab(){
    var vocabNum = $("VocabNum");
    var vocabValue = $("VocabValue");
    var url="PHP/AddVocab.php";
    var param="wid="+encodeURIComponent(vocabNum.value)
    param = param + "&word=" + encodeURIComponent(vocabValue.value);
    Add(url,param,[vocabNum,vocabValue]);
}

function AddMove(){
    var rid = $("rid");
    var wid = $("wid");
    var newroom = $("newroom");
    var url="PHP/AddMove.php";
    var param="rid="+encodeURIComponent(rid.value)
    param = param + "&wid=" + encodeURIComponent(wid.value)
    param = param + "&newroom=" + encodeURIComponent(newroom.value);
    Add(url,param,[rid,wid,newroom]);
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
    wordSize_p.append(document.createTextNode("Longueur maximum d'un mot : " + split[2]));
    maxObj_p.append(document.createTextNode("Nombre maximum d'objets dans l'inventaire : " + split[0]));
    maxSave_p.append(document.createTextNode("Nombre maximum de sauvegarde : " + split[1]));
    $("wordSize").value= split[2];
    $("maxObj").value= split[0];
    $("maxSave").value=split[1];
}

function Get(url, elements,table, promptvalue= null, isDeletable=true, isEditable=true, UseInnerHTML=false){
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequest(xhr.responseText, elements,table, promptvalue, isDeletable, isEditable, UseInnerHTML);
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function GetRoom(){
    var url="PHP/GetRoom.php";
    Get(url,["rid","roomdesc"], "Piece","Nouvelle description de la pièce :" ,true,true,true);
}

function GetMessage(){
    var url="PHP/GetMessage.php";
    Get(url,["mid","message"],"Message", "Nouveau message :",true,true,true);
}

function GetSmsg(){
    var url="PHP/GetSmsg.php";
    Get(url,["smid","message"], "SMessage",null,false, false, true)
}

function GetMove(){
    var url="PHP/GetMove.php";
    Get(url,["rid","word", "newroom"],"Deplacement",null , true,false)
}

function GetVocab(){
    var url="PHP/GetVocab.php";
    Get(url,["wid","word"], "Vocab", null, true,false)
}

function GetAction(){
    var url="PHP/GetAction.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequest(xhr.responseText, ["tbl","wid1", "wid2", "pgm"],"Action", null, true, true, false, "action");
            show("ActionButton");
            $("ActionButton").onclick = function(){ShowAction(xhr.responseText)};
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}


function ProcessRequest(str,elements,table, promptvalue, isDeletable, isEditable, UseInnerHTML, table2=null){
    var result = JSON.parse(str);
    if(table2 !== null){
        result= result[table2];
    }
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
        var id = result[i][elements[0]];
        //Insertion des images de suppression
        if (isDeletable) {
            if(table === "Deplacement") {
                InsertDeleteImg(tr, id, table, result[i][elements[1]])
            }else if(table === "Action"){
                InsertDeleteImg(tr, id, table, result[i][elements[1]], result[i][elements[2]])
            }else if(table === "Vocab"){
                InsertDeleteImg(tr, id, table,result[i][elements[1]]);
            }else{
                InsertDeleteImg(tr, id, table);
            }
        }
        //Insertion des images d'édition
        if (isEditable) {
            InsertEditImg(tr, id, table, promptvalue);
        }
        tbl.appendChild(tr)
    }
    el.appendChild(tbl);
}

function GetObject(){
    var url="PHP/GetObj.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequestObj(xhr.responseText,["objid","objdesc"], ["startloc", "wid"], "Objet", "Nouveau nom de l'objet :");
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function ProcessRequestObj(str, elements, elements2, table, promptvalue) {
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

    result.forEach(function(item) {  //chaque ligne du resultat
        var tr = document.createElement("TR");
        var td = document.createElement("TD");
        for (j=0;j<elements.length;j++) {
            InsertColumn(tr, item[elements[j]]);
        }
        Promise.all([
            GetValueArray("PHP/GetRoom.php"),
            GetValueArray("PHP/GetVocab.php"),
            GetValueArray("PHP/GetObjSelected.php", item[elements[0]])
        ]).then(function(arrays) {
            var id = item[elements[0]];
            var locations = {"Non cree": -1, "Porte": -2, "Transporte": -3};
            var words = {"-----": null}
            InsertSelect(td, arrays[0], "roomdesc", "rid", true,arrays[2]["startloc"], locations, id, "ObjetSelectRoom");
            tr.appendChild(td);
            td = document.createElement("TD");
            InsertSelect(td, arrays[1], "word", "wid", true, arrays[2]["wid"],words, id, "ObjetSelectWord");
            tr.appendChild(td);
            InsertDeleteImg(tr, id, table);
            InsertEditImg(tr, id, table, promptvalue);
            tbl.appendChild(tr);
        });
    });
    el.appendChild(tbl);
} // a revoir


function GetValueArray(url, id=null){
    return new Promise(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        var param = "id=" + encodeURIComponent(id);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    var result = JSON.parse(xhr.responseText);
                    resolve(result);
                } else {
                    reject("Error: " + xhr.status);
                }
            }
        };
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(param);
    });
}


function InsertEditImg(tr,id, table, promptvalue){
    var img = document.createElement("IMG");
    var td = document.createElement("TD");
    img.src="../img/icone-edit.png";
    img.onclick = InsertEditHelper(id, table, promptvalue)
    td.appendChild(img);
    tr.appendChild(td);
}

function InsertEditHelper(id, table, promptvalue) {
    return function() {
        var newValue = prompt(promptvalue);
        if (newValue !== null) {
            var url = "PHP/Edit.php";
            var param = "id1=" + encodeURIComponent(id) + "&table=" + encodeURIComponent(table) + "&newValue=" + encodeURIComponent(newValue);
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    Refresh(xhr.response)
                }
            };
            xhr.open("POST", url, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(param);
        }
    };
}

function InsertDeleteImg(tr, id1, table, id2=null, id3=null){
    var img = document.createElement("IMG");
    var td = document.createElement("TD");
    img.src="../img/icone-delete.png";
    img.onclick =  InsertDeleteHelper(id1, id2,id3, table)
    td.appendChild(img);
    tr.appendChild(td);
}

function InsertDeleteHelper(id1, id2, id3, table){
    return function(){
        var url="PHP/Delete.php";
        var param="id1="+encodeURIComponent(id1)
        param = param + "&id2=" + encodeURIComponent(id2)
        param = param + "&id3=" + encodeURIComponent(id3)
        param = param + "&table=" + encodeURIComponent(table)
        var xhr= new XMLHttpRequest();
        xhr.onreadystatechange = function(){
            if (xhr.readyState === 4 && xhr.status === 200){
                Refresh(xhr.response)
            }
        }
        xhr.open("POST",url,true);
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        xhr.send(param);
    }
}

function Refresh (str){
    switch(str){
        case "piece":
            GetRoom();
            break;
        case "message":
            GetMessage();
            break;
        case "deplacement":
            GetMove();
            break;
        case "vocab":
            GetVocab();
            break;
        case "objet":
            GetObject();
            break;
        case "action":
            GetAction();
            break;
    }
}

//TODO : Pas de InnerHTML

function InsertColumn(tr, item, UseInnerHTML){
    var td = document.createElement("TD");
    item = String(item).trunc(80, " ", true);
    if (UseInnerHTML){
        td.innerHTML=item;
    }else {
        var tn = document.createTextNode(item);
        td.appendChild(tn);
    }
    tr.appendChild(td);
}

function InsertSelectHelper(table, id){
    return function(ev){
        var target = ev.target;
        var url = "PHP/Edit.php";
        var param = "id1=" + encodeURIComponent(id)
        param = param + "&table=" + encodeURIComponent(table)
        param = param +"&newValue=" + encodeURIComponent(target.value);
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                Refresh(xhr.response)
            }
        };
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(param);
    }
}

function InsertSelect(td, array, text, value, onChanged,selected, moreOption=[], id=null, table = null){
    var select = document.createElement("SELECT");
    if (onChanged){
        select.onchange = InsertSelectHelper(table,id);
    }else{
        select.id = id;
    }
    var i;
    var option = document.createElement("OPTION");
    for (i in moreOption){
        option = document.createElement("OPTION");
        option.text = i;
        option.value = moreOption[i];
        if (selected === null || selected === moreOption[i]) {
            option.selected = true;
        }
        select.appendChild(option);
    }
    for (i = 0; i < array.length; i++) {
        option = document.createElement("OPTION");
        option.value = array[i][value];
        option.text = String(array[i][text]).trunc(30, " ", true);
        if(selected === array[i][value]){
            option.selected = true;
        }
        select.appendChild(option);
    }
    td.appendChild(select);
}











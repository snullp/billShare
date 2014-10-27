<?php

//fetch all variables
function editaction(){
    global $db;
    global $users;
    while (1){
        $id = addslashes($_POST["id"]);
        if ($id!="new") {
            $id=intval($id);
            if ($id == 0) {
                $_SESSION["alarm"]="Id error";
                break;
            }
        }

        $date = addslashes($_POST["date"]);
        $stamp = strtotime($date);
        if (date("Y-m") != date("Y-m",$stamp) || $date != date('Y-m-d',$stamp)){
            $_SESSION["alarm"]="Date error";
            break;
        }

        if ($_POST["action"]=="remove"){
            $query="DELETE FROM purchases WHERE id=$id";

            $db->query($query);
            if ($db->affected_rows==0)
                $_SESSION["alarm"]="DB failure";
            else
                $_SESSION["notice"]="OK.";
            break;
        }

        $item = addslashes($_POST["item"]);
        if ($item=="") {
            $_SESSION["alarm"]="Item empty";
            break;
        }

        $price = floatval(addslashes($_POST["price"]));
        if ($price == 0) {
            $_SESSION["alarm"]="Price error";
            break;
        }

        $payer = intval(addslashes($_POST["payer"]));
        if (!isset($users[$payer])){
            $_SESSION["alarm"]="Payer error";
            break;
        }

        $comment = addslashes($_POST["comment"]);

        $userarray = $_POST["user"];
        $userstr = "";
        foreach($userarray as $userid)
            if (isset($users[$userid])) $userstr.=$userid.",";
        if ($userstr=="") {
            $_SESSION["alarm"]="User empty";
            break;
        }
        $userstr=trim($userstr,",");

        if ($id=="new") $query="INSERT INTO purchases (`id`,`date`,`item`,`price`,`payer`,`share`,`Comment`) VALUES(NULL, '$date', '$item', '$price', '$payer', '$userstr', '$comment')";
        else $query="UPDATE purchases SET date='$date', item='$item', price='$price', payer='$payer', share='$userstr', comment='$comment' WHERE id = $id LIMIT 1";

        $db->query($query);
        if ($db->affected_rows==0)
            $_SESSION["alarm"]="No data affected (Possible due to DB failure?).";
        else
            $_SESSION["notice"]="OK.";
        break;
    }
    session_regenerate_id();
}

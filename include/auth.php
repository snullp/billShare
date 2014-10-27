<?php

function passwordcheck($pwd){
    global $nopassword_access;
    global $pagetitle;
    if ($nopassword_access && $pwd == null) return true;
    if (isset($_SERVER['PHP_AUTH_PW'])) 
        if (md5(md5($_SERVER['PHP_AUTH_PW']).$pagetitle)==$pwd) return true;
    return false;
}

function auth(){
    global $db;
    global $uid;
    global $username;
    global $users;
    if (!isset($_SERVER['PHP_AUTH_USER'])){
        if (empty($_GET)) return false;
        $username = addslashes(trim(array_keys($_GET)[0],"/"));
    }
    else
        $username = addslashes($_SERVER['PHP_AUTH_USER']);
    if (empty($username)) return false;
    //check if user is authorized
    $query = "SELECT id,name,password FROM users";
    $result = $db->query($query);
    while ($row = $result->fetch_row()){
        $users[$row[0]]=$row[1];
        if (strcasecmp($username, $row[1])==0) {
            if (passwordcheck($row[2])){
                $uid = $row[0];
                $username = $row[1];
                $valid = true;
            }
        }
    }
    $result->close();
    if (!isset($valid)) {
        return false;
    }
    return true;
}

/*get name from id*/
function namequery($namestr){
    global $users;
    $names = explode(",",trim($namestr,", \t\n\r\0\x0B"));
    if (count($names)>1){
        $output = "";
        foreach ($names as $name){
            $output .= $users[intval($name)].", ";
        }
    }else{
        $output=$users[intval($names[0])];
    }
    return trim($output," ,");
}

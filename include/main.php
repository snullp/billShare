<?php
/*really messy here, sorry*/
$table=array();
if (isset($_POST["disp_month"]) && $stamp = strtotime($_POST["disp_month"])) $month = date("Y-m",$stamp);
else $month = date("Y-m");
$query = "SELECT * FROM purchases WHERE date BETWEEN '$month-00' AND '$month-31' ORDER BY date DESC, id DESC";
$consumption = array();
$payment = array();
$optionlist="";
$useroptionlist="";
$alloptionlist="";

foreach($users as $key=>$usern){
    $optionlist.='<option value="'.$key.'">'.$usern.'</option>';
    if ($key!=$uid)
        $useroptionlist.='<option value="'.$key.'">'.$usern.'</option>';
    else
        $useroptionlist.='<option value="'.$key.'" selected>'.$usern.'</option>';
    $alloptionlist.='<option value="'.$key.'" selected>'.$usern.'</option>';
    $consumption[$key]=0.0;
    $payment[$key]=0.0;
}

$result = $db->query($query);

while ($row = $result->fetch_array(MYSQLI_ASSOC)){
    $cgroup = explode(",",trim($row["share"],","));
    $avg = $row["price"]/count($cgroup);
    $avg = round($avg,3);
    $payment[$row["payer"]] += $avg * count($cgroup);
    foreach($cgroup as $consumer)
        $consumption[$consumer] += $avg;

    $table[] = array(
            "id" => $row["id"],
            "date" => $row["date"],
            "item" => strip_tags($row["item"]),
            "price" => $row["price"],
            "payer" => namequery($row["payer"]),
            "user" => namequery($row["share"]),
            "comment" => strip_tags($row["comment"]),
            );
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pagetitle ?></title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/custom.css" rel="stylesheet">
</head>
<body>

<div class="container">

<h3>Welcome, <?php print $username; ?><small>&nbsp;&nbsp;<?php echo $wel_msg ?></small></h3>

<?php if (isset($_SESSION["notice"])){ ?>
<div class="alert alert-success alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<?php echo $_SESSION["notice"];?>
</div>
<?php unset($_SESSION["notice"]);} ?>

<?php if (isset($_SESSION["alarm"])){ ?>
<div class="alert alert-danger alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<?php echo $_SESSION["alarm"];?>
</div>
<?php unset($_SESSION["alarm"]);} ?>

<a class="pull-right" href=""><span class="glyphicon glyphicon-refresh"></span>&nbspRefresh</a>
<ul class="nav nav-tabs">
<?php
$nowmonth = date("Y-m");
while (1) { ?>
<form id="disp_<?php echo $nowmonth ?>" action="" method="POST">
<input type="hidden" name="disp_month" value="<?php echo $nowmonth ?>">
</form>
<li<?php if ($month == $nowmonth) echo ' class="active"'; ?>><a href="#" class="monthbutton" id="disp_<?php echo $nowmonth ?>"><?php echo date("M. Y", strtotime($nowmonth)) ?></a></li>
<?php
    if ($nowmonth == $deploy_time) break;
$nowmonth = date("Y-m", strtotime("$nowmonth -1 month"));
};
?>
</ul>
<p>
<div class="pull-right">
<?php
//calculate final payment
foreach($users as $user=>$name){
    if ($payment[$user] == 0.0 && $consumption[$user] == 0.0) continue;
    if ($payment[$user]>=$consumption[$user]) $receiver[$user]=$payment[$user]-$consumption[$user];
    else $payer[$user]=$consumption[$user]-$payment[$user];
}
asort($payer);
asort($receiver);
while (!empty($payer)){
    $val = reset($payer);
    $userpay = key($payer);
    unset($payer[$userpay]);
    reset($receiver);
    $userrecv = key($receiver);
    print "<span>".namequery($userpay)." => ".namequery($userrecv)." $".round($val,2)."&nbsp;&nbsp;&nbsp;</span>\n";
    $receiver[$userrecv] -= $val;
    if ($receiver[$userrecv] <= 0.0001){
        if ($receiver[$userrecv] < -0.0001) {
            $payer[$userrecv] = -$receiver[$userrecv];
            asort($payer);
        }
        unset($receiver[$userrecv]);
    }
}
if (!empty($receiver)) print "Warning, money value mismatch.";
?>
</div>
<span>My total spend: <?php echo round($consumption[$uid],2); ?> &nbsp;&nbsp;&nbsp;</span>
</p>
<?php if ($month == date("Y-m")) { ?>
<form id="addform" action="" method="POST">
<input type="hidden" name="key" value="">
<input type="hidden" name="action" value="add">
</form>
<a class="addbutton" href="#"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;New Entry</a>
<?php } ?>
<table class="table table-striped table-hover table-condensed">
<thead>
<tr>
<td class="inputdate">Date</td>
<td class="inputitem">Item</td>
<td class="inputprice">Price</td>
<td class="inputpayer">Paid by</td>
<td class="inputuser">Used by</td>
<td class="inputcomment">Comment</td>
<?php if ($month == date("Y-m")) { ?>
<td class="inputaction">Action</td>
<?php } ?>
</tr>
</thead>
<tbody>
<?php if (isset($_SESSION["newline"])){ ?>
<tr>
<form id="#new" action="" method="POST">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="id" value="new">
<input type="hidden" name="key" value="">
<td><input class="inputdate" type="text" name="date" value="<?php echo date("Y-m-d") ?>"></td>
<td><input class="inputitem" type="text" name="item"></td>
<td><input class="inputprice" type="text" name="price"></td>
<td>
<select class="inputpayer" name="payer">
<?php echo $useroptionlist ?>
</select>
</td>
<td>
<select class="inputuser" name="user[]" multiple>
<?php echo $alloptionlist ?>
</select>
</td>
<td><input class="inputcomment" type="text" name="comment"></td>
<td>
<a class="confirmbutton" href="#new"><span class="glyphicon glyphicon-ok"></span></a>
<a href=""><span class="glyphicon glyphicon-remove"></span></a>
</td>
</form>
</tr>
<?php
unset($_SESSION["newline"]);
}
?>
<?php foreach($table as $row){ ?>
<tr>
<form id="#<?php echo $row["id"] ?>" role="form" action="" method="POST">
<input class="postaction" id="#<?php echo $row["id"] ?>" type="hidden" name="action" value="edit">
<input type="hidden" name="id" value="<?php echo $row["id"] ?>">
<input type="hidden" name="key" value="">
<td>
<input type="text" name="date" class="inputdate" id="#<?php echo $row["id"] ?>" style="display: none;">
<span class="formdate" id="#<?php echo $row["id"] ?>"><?php echo $row["date"] ?></span>
</td>
<td>
<input type="text" name="item" class="inputitem" id="#<?php echo $row["id"] ?>" style="display: none;">
<span class="formitem" id="#<?php echo $row["id"] ?>"><?php echo $row["item"] ?></span>
</td>
<td>
<input type="text" name="price" class="inputprice" id="#<?php echo $row["id"] ?>" style="display: none;">
<span class="formprice" id="#<?php echo $row["id"] ?>"><?php echo $row["price"] ?></span>
</td>
<td>
<select name="payer" class="inputpayer" id="#<?php echo $row["id"] ?>" style="display: none;">
<?php echo $optionlist ?>
</select>
<span class="formpayer" id="#<?php echo $row["id"] ?>"><?php echo $row["payer"] ?></span>
</td>
<td>
<select name="user[]" multiple class="inputuser" id="#<?php echo $row["id"] ?>" style="display: none;">
<?php echo $optionlist ?>
</select>
<span class="formuser" id="#<?php echo $row["id"] ?>"><?php echo $row["user"] ?></span>
</td>
<td>
<input type="text" name="comment" class="inputcomment" id="#<?php echo $row["id"] ?>" style="display: none;">
<span class="formcomment" id="#<?php echo $row["id"] ?>"><?php echo $row["comment"] ?></span>
</td>
<?php if ($month == date("Y-m")) { ?>
<td><a class="editbutton" id="#<?php echo $row["id"] ?>" href="#<?php echo $row["id"] ?>">
<span class="glyphicon glyphicon-edit"></span>&nbsp;&nbsp;Edit
</a>
<a class="confirmbutton" id="#<?php echo $row["id"] ?>" href="#<?php echo $row["id"] ?>" style="display: none;"><span class="glyphicon glyphicon-ok"></span></a>
<a class="removebutton" id="#<?php echo $row["id"] ?>" href="#<?php echo $row["id"] ?>" style="display: none;"><span class="glyphicon glyphicon-trash"></span></a>
<a class="cancelbutton" id="#<?php echo $row["id"] ?>" href="#<?php echo $row["id"] ?>" style="display: none;"><span class="glyphicon glyphicon-remove"></span></a>
</td>
<?php } ?>
</form>
</tr>
<?php } ?>
</tbody>
</table>
<hr>
<footer>
<p>&copy; BigSquirrel 2014</p>
</footer>
</div>
<script src="https://code.jquery.com/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
var hashkey='<?php echo session_id() ?>';
</script>
<script src="js/custom.js"></script>
</body>
</html>

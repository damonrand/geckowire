<?php
/*
 * Author: Damon Rand
 * http://www.cybermagic.co.nz
 * 
 * Feed a Xero bank balance into a range of other apps. 
 * My first PHP app, sorry for the lack of MVC!
 * 
 * parameters:
 * debug=anything   			Show the Xero objects 
 * format=1    					What are you outputing to? 1 means Geckoboard xml. Nothing else is supported so far.
 * bankindex=0..n   			Which bank account do you want to see? Defaults to 0
 * includefx=anything			Add 1 to closing balance cause there is an extra column for fx gain
 * 
 */

if (!isset($_REQUEST['key']))
{
	echo "You must specify 'key'. See api.xero.com";	
	exit();	
}

if (!isset($_REQUEST['secret']))
{
	echo "You must specify 'secret'. See api.xero.com";	
	exit();	
}


?>
<?php

include_once "xero.php";

//define your application key and secret (find these at https://api.xero.com/Application)
define('XERO_KEY',$_REQUEST['key']);
define('XERO_SECRET',$_REQUEST['secret']);

//instantiate the Xero class with your key, secret and paths to your RSA cert and key
//the last argument is optional and may be either "xml" or "json" (default)
//"xml" will give you the result as a SimpleXMLElement object, while 'json' will give you a plain array object
$xero = new Xero(XERO_KEY, XERO_SECRET, "xero.cer", "xero.pem" , 'json' );


//get the report
$result = $xero->reports_banksummary();
$myarr = json_decode(json_encode($result), true, 512);

$bankindex = 0;
if (isset($_REQUEST['bankindex']))
	$bankindex = $_REQUEST['bankindex'];

$closingindex=4;
if (isset($_REQUEST['includefx']))
	$closingindex=5;

$startbalance=0;
$closebalance=0;

if($bankindex > count($myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"])-1){
	echo "Error with bankindex";
	exit();
}else{
	$startbalance = $myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"][$bankindex]["Cells"]["Cell"][1]["Value"];
	$closebalance = $myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"][$bankindex]["Cells"]["Cell"][$closingindex]["Value"];
}

$ashtml = true;
// Format 1 means Geckoboard xml
if (isset($_REQUEST['format']) &&  $_REQUEST['format'] == '1')
	$ashtml = false;

?>
<?php
if ($ashtml)
{
?>	
<!DOCTYPE html>

<html>
<head>
<title>Hello cloudControl</title>
</head>
<body>

<h2>Display our balance in Geckoboard</h2>

<p>Start balance: <?php echo $startbalance ?></p>
<p>Close balance: <?php echo $closebalance ?></p>
<?php 
	if (isset($_REQUEST['debug'])){
		echo "<h3>Debug information</h3>";
		echo  "<hr/>";
		var_dump($_REQUEST);
		echo "<hr/>";
		var_dump($myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"][$bankindex]["Cells"]["Cell"][1]);
		var_dump($myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"][$bankindex]["Cells"]["Cell"][4]);
		echo "<hr/>";
		var_dump($myarr["Reports"]);
	}
?>
</body>
</html>
<?php
}
else
{
?>
<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" ; ?>
<root>
<item>
<value><?php echo round($startbalance) ?></value>
<text>Starting balance</text>
</item>
<item>
<value><?php echo round($closebalance) ?></value>
<text>Closing balance</text>
</item>
</root>
<?php 
}
?>

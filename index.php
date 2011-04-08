<?php
/*
 * Author: Damon Rand
 * http://www.cybermagic.co.nz
 * 
 * Feed a Xero bank balance into a range of other apps. 
 * My first PHP app, sorry for the lack of MVC!
 * 
 * parameters:
 * debug=anythign   			Show the Xero objects 
 * format=html|geckoboard    	What are you outputing to?
 * bankindex=0..n   			Which bank account do you want to see? Defaults to 0
 * 
 */

if (!isset($_REQUEST['secret']))
{
	echo "You must specify 'secret. See api.xero.com'";	
	exit();	
}

?>

<?php

include_once "xero.php";

//define your application key and secret (find these at https://api.xero.com/Application)
define('XERO_KEY','YTU2ZJG2ZGFKZMVHNDNJZGIZZJY1OG');
define('XERO_SECRET',$_REQUEST['secret']);

//instantiate the Xero class with your key, secret and paths to your RSA cert and key
//the last argument is optional and may be either "xml" or "json" (default)
//"xml" will give you the result as a SimpleXMLElement object, while 'json' will give you a plain array object
$xero = new Xero(XERO_KEY, XERO_SECRET, "xero.cer", "xero.pem" , 'json' );


//get the report
$result = $xero->reports_banksummary();
$myarr = json_decode(json_encode($result), true, 512);

$bankindex = 0;
if ($_REQUEST['bankindex'])
$bankindex = $_REQUEST['bankindex'];

$startbalance=0;
$closebalance=0;

if($bankindex > count($myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"])-1){
	echo "Error with bankindex";
	exit();
}else{
	$startbalance = $myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"][$bankindex]["Cells"]["Cell"][1]["Value"];
	$closebalance = $myarr["Reports"]["Report"]["Rows"]["Row"][1]["Rows"]["Row"][$bankindex]["Cells"]["Cell"][4]["Value"];
}

$ashtml = true;
if (isset($_REQUEST['format']) &&  $_REQUEST['format'] == 'geckoboard')
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
<root>
<item>
<value>
<?php echo $startbalance ?>
</value>
<text></text>
</item>
<item>
<value>
<?php echo $closebalance ?>
</value>
<text></text>
</item>
</root>
<?php 
}
?>

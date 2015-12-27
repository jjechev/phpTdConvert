<?php 
date_default_timezone_set('Europe/Sofia');
//if(!isset($_GET['yegler'])){die('...');}
if(isset($_GET['refresh'])) HTTP::header("Refresh: 2; ");
var_dump($_ENV);
?>

<a name="top"></a>
<form action="#bottom" method="POST" style="position:fixed;top:0px;left:0px;padding:3px 7px; background-color: white; color:000;">
	regex:<input type="text" name="regex" value="<?php echo isset($_POST['regex'])?$_POST['regex']:''; ?>" />
	lines:<input type="text" name="n" value="<?php echo isset($_POST['n'])?$_POST['n']:''; ?>" style='width:50px;'/>
	<input type="submit" value="GO" />
	<input type="button" value="clear" onclick="javascript:window.location.reload();" />
</form>
<a href="<?php echo (isset($_GET['refresh']))? '/' : '/?refresh'?>" style="position:fixed;top:0px;left:520px;padding:3px 7px; background-color: white;">Auto Refresh: <?php echo (isset($_GET['refresh']))? '0n' : 'Off'?></a>
<a href="#top" style="position:fixed;bottom:0px;right:0px;padding:3px 7px; background-color: white;">top</a>
<br />
<br />
<?php
$pathlog	= '../local/php_errors.log';
$log	= isset($_GET['log']) && file_exists($_GET['log']) ? $_GET['log'] : $pathlog;
$lines	= isset($_POST['n']) && !$_POST['n']=='' ? abs((int)$_POST['n']) : 50;
$file	= $log;
$adds	= '';
$now	= time();
$day	= date('d');

if( isset($_POST['regex']) && $_POST['regex'] ){
	$adds .= ' | grep -i \''.$_POST['regex'].'\'';
}
//echo ('/usr/bin/tail -n '.$lines.' '.$file.$adds);
exec('/usr/bin/tail -n '.$lines.' '.$file.$adds, $output);
for( $i=0, $n=count($output); $i < $n; $i++ ){
	preg_match('/(\d{2})-(\w{3})-(\d{4}) (\d{2}):(\d{2}):(\d{2})/', $output[$i], $match);
	if( $match ){
		$match = array_combine(array('str','day','month','year', 'hour','min','sec'), $match);
		#$time = mktime( $match[4], $match[5], $match[6], $match[2], $match[1], $match[3]);
		#$time = strtotime($match[0]);
		$output[$i] = str_replace( $match['str'], '<span style="color: '.($match['day']<$day?'grey':'green').'">'.$match['str'].'</span>', $output[$i] );
	}
	
	$output[$i] = str_replace( 'Notice', '<span style="color: blue">Notice</span>', $output[$i] );
	$output[$i] = str_replace( 'Warning', '<span style="color: orange">Warning</span>', $output[$i] );
	$output[$i] = str_replace( 'Fatal', '<span style="color: red">Fatal</span>', $output[$i] );
	$output[$i] = str_replace( 'error', '<span style="color: red">error</span>', $output[$i] );
	$output[$i] = str_replace( 'Stack trace:', '<span style="color: yellow">Stack trace:</span>', $output[$i] );
}

//$output = array_reverse($output);
echo '<pre>';
echo implode(PHP_EOL, $output);
echo '</pre>';
?>
<a name="bottom"></a>
<br />
<br />

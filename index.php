<?php
require_once('src/DiffGenerator.php');

$dg = new DiffGenerator(array('type' => 'block', 'size' => 1), 'wxabcdefgyz', 'wxadef12gyz');
$diffs = $dg->getDiffs();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3c.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
    <title>Diffs</title>
    <style type='text/css'>@import url("main.css");</style>
  </head>
  <body>
      <ul>
<?php
foreach($diffs as $diff) {
	if(substr($diff, 0, 1) != '+' && substr($diff, 0, 1) != '-') {
		echo '<li>' . $diff . '</li>';
	} else if(substr($diff, 0, 1) == '+') {
		echo '<li class = \'ins\'>' . substr($diff, 1, strlen($diff) - 1) . '</li>';
	} else if(substr($diff, 0, 1) == '-') {
		echo '<li class = \'del\'>' . substr($diff, 1, strlen($diff) - 1) . '</li>';
	}
	
	//print_r($diff);
}
?>
      </ul>
  </body>
</html>
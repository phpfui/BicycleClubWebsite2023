<html>
<head>
</head>
<body>
<form>
<?php
if (! empty($_REQUEST))
	{
	echo '<fieldset><legend>Posted Values</legend>';

	foreach ($_REQUEST as $key => $value)
		{
		echo "<b>{$key}</b> = {$value}<br>";
		}
	echo '</fieldset>';
	}
?>
    <fieldset>
        <legend>Calendar Testing</legend>
        Time <input type="time" name="time" placeholder="time" step=900>
				<br>
				<br>
        Date <input type="date" name="date" placeholder="date">
				<br>
				<br>
        DateTime <input type="datetime" name="datetime" placeholder="datetime">
				<br>
				<br>
        Integer <input type="integer" name="integer" placeholder="integer">
				<br>
				<br>
    </fieldset>
    <input type="submit" value="Test" name="action"/>
</form>
</body>



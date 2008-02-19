<form action="index.php" method="get">
<input type="hidden" name="_step" value="3" />
<?php

echo '<p>[do some tests as in check.php-dist here]</p>';

echo '<input type="submit" value="EXECUTE TESTS" />';

?>
</form>

<p class="warning">

After completing the installation and the final tests please <b>remove</b> the whole
installer folder from the document root of the webserver.<br />
<br />

These files may expose sensitive configuration data like server passwords and encryption keys
to the public. Make sure you cannot access this installer from your browser.

</p>

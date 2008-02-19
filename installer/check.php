<form action="index.php" method="get">
<input type="hidden" name="_step" value="2" />
<?php

echo '<p>[do some tests as in check.php-dist here]</p>';

echo '<input type="submit" value="NEXT" ' . ($RCI->failures ? 'disabled' : '') . ' />';

?>
</form>

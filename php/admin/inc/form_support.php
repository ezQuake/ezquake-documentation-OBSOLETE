<?php

    $select_builds = "<select name=\"id_build\">".$supForms->OptionsBuilds()."</select>";
?>

<p>Use <code>dump_default</code> command to create <code>cvar_defaults.cfg</code> config to be uploaded here:</p>

<form enctype="multipart/form-data" action="index.php" method="post">
<div><input type="hidden" name="action" value="varssetdefaults" />
<input type="hidden" name="MAX_FILE_SIZE" value="300000" /></div>
<label>Build: <?=$select_builds?></label><br />
<label>Config with default values: <input name="userfile" type="file" /></label>
<p><input type="submit" value="Submit" /></p>
</form>

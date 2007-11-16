<p>Use following commands: <br />
<code>log cmdlist; cmdlist; log stop</code><br />
Then come upload the list in here. It will be used to determine which cmds
are missing documentation and which are documented but do not exist.</p>

<h2 id="rename">Add phys cmds list</h2>
<form action="index.php" method="post" id="addphysvars">
<div><input type="hidden" name="action" value="addphyscmds" /></div>
<textarea cols="60" rows="10" name="physlist" id="physlist"></textarea>
<div><input type="submit" value="Submit" /></div>
</form>

<h2 id="rename">Clear phys cmds</h2>
<form action="index.php" method="post" id="clearphysvars">
<div><input type="hidden" name="action" value="clearphyscmds" /></div>
<div><input type="submit" value="Submit" /></div>
</form>

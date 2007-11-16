<p>Use following commands: <br />
<code>log cvarlist; cvarlist; log stop</code><br />
Then you need to strip out first 4 chars from the list (each var has some flags).<br />
Then come upload the list in here. It will be used to determine which cvars
are missing documentation and which are documented but do not exist.</p>

<h2 id="rename">Add phys vars list</h2>
<form action="index.php" method="post" id="addphysvars">
<div><input type="hidden" name="action" value="addphysvars" /></div>
<textarea cols="60" rows="10" name="physlist" id="physlist"></textarea>
<div><input type="submit" value="Submit" /></div>
</form>

<h2 id="rename">Clear phys vars</h2>
<form action="index.php" method="post" id="clearphysvars">
<div><input type="hidden" name="action" value="clearphysvars" /></div>
<div><input type="submit" value="Submit" /></div>
</form>

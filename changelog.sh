SRC=$1
OUT=changelog.html
FEAT_PAT="s/.\t\w*\t\([^\t]*\)\t\(.*\)/<li>\2 - feature by <span class='author'>\1<\/span><\/li>/"
FIX_PAT="s/.\t\w*\t\([^\t]*\)\t\(.*\)/<li>\2 - fixed by <span class='author'>\1<\/span><\/li>/"
CHANGE_PAT="s/.\t\w*\t\(\w*\)\t\(.*\)/<li>\2 - fixed by <span class='author'>\1<\/span><\/li>/"
HDR_PAT="s|.\t\(.*\)|<h2>\1</h2>|"
THNX_PAT="s|.\t\(\w*\)\t\(.*\)|<li><span class='author'>\1</span> \2</li>|"
if [ -f "$1" ]
then
	echo Processing changelog $SRC...
	sort $SRC | sed "s|\[\([^]]*\)\]|<a href='?\1'>\1</a>|g" > chlg_sorted
	grep ^0. chlg_sorted | sed "$HDR_PAT" > $OUT
	echo "<dl>" >> $OUT
	 
	echo "<dt>Major Features</dt><dd><ul>" >> $OUT
	grep ^1.feature chlg_sorted | sed "$FEAT_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT
	
	echo "<dt>Major Fixes</dt><dd><ul>" >> $OUT
	grep ^1.fix chlg_sorted | sed "$FIX_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT

	echo "<dt>Major Changes</dt><dd><ul>" >> $OUT
	grep ^1.change chlg_sorted | sed "$CHANGE_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT

	echo "<dt>For major contributions thanks goes to</dt><dd><ul>" >> $OUT
	grep ^a. chlg_sorted | sed "$THNX_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT
	
	echo "<dt>Credits to external contributors</dt><dd><ul>" >> $OUT
	grep ^b. chlg_sorted | sed "$THNX_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT


	echo "<dt>More Features</dt><dd><ul>" >> $OUT
	grep ^2.feature chlg_sorted | sed "$FEAT_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT

	echo "<dt>More Fixes</dt><dd><ul>" >> $OUT
	grep ^2.fix chlg_sorted | sed "$FIX_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT

	echo "<dt>More Changes</dt><dd><ul>" >> $OUT
	grep ^2.change chlg_sorted | sed "$CHANGE_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT

	echo "<dt>Minor Features</dt><dd><ul>" >> $OUT
	grep ^3.feature chlg_sorted | sed "$FEAT_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT
	
	echo "<dt>Minor Fixes</dt><dd><ul>" >> $OUT
	grep ^3.fix chlg_sorted | sed "$FIX_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT

	echo "<dt>Minor Changes</dt><dd><ul>" >> $OUT
	grep ^3.change chlg_sorted | sed "$CHANGE_PAT" >> $OUT
	echo "</ul></dd>" >> $OUT
	
	echo "</dl>" >> $OUT
	
	rm chlg_*
else
	echo Give changelog filename as the first argument
fi

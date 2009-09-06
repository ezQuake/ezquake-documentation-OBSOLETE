<?php

require_once("../inc/mysql_commands.php");  // mysql data interface classes
require_once("../inc/common.php");

function GetXMLCmd($cmd)
/**
 * Generates XML content for game in-built help system
 * $var - content to render, array
 */
{
    $xmlclose = "?".">"; // those two char confuses my PHP editor :(

    $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"{$xmlclose}\n";
    $ret .= "<?xml-stylesheet type=\"text/xsl\" href=\"../xsl/command.xsl\"{$xmlclose}\n";
    $ret .= "<command xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"../xsd/command.xsd\">\n";
	$ret .= "  <name>".Cvrt($cmd["name"])."</name>\n";
	if(strlen($cmd["description"]))
        $ret .= "  <description>".Cvrt($cmd["description"])."</description>\n";

    if(strlen($cmd["syntax"]))
        $ret .= "  <syntax>".Cvrt($cmd["syntax"])."</syntax>\n";

	if(isset($cmd["args"]))
	{
	  $ret .= "  <arguments>\n";
	  foreach ($cmd["args"] as $argname => $argdesc)
	  {
        $ret .= "    <argument>\n";
        $ret .= "      <name>".Cvrt($argname)."</name>\n";
        $ret .= "      <description>".Cvrt($argdesc)."</description>\n";
        $ret .= "    </argument>\n";
      }
      $ret .= "  </arguments>\n";
    }
	  
	if(strlen($var["remarks"]))
        $ret .= "  <remarks>".Cvrt($var["remarks"])."</remarks>\n";
        
    $ret .= "</command>\n";

    return $ret;
}

function GetXMLVar($var)
/**
 * Generates XML content for game in-built help system
 * $var - content to render, array
 */
{
    $xmlclose = "?".">"; // those two char confuses my PHP editor :(
    
    $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"{$xmlclose}\n";
    $ret .= "<?xml-stylesheet type=\"text/xsl\" href=\"../xsl/variable.xsl\"{$xmlclose}\n";
    $ret .= "<variable xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"../xsd/variable.xsd\">\n";
    $ret .= "  <name>".Cvrt($var["name"])."</name>\n";
    if(strlen($var["description"]))
        $ret .= "  <description>".Cvrt($var["description"])."</description>\n";
    
    $ret .= "  <value>\n";
    switch($var["type"]) 
    {
        case 'string': $ret .= "    <string>".Cvrt($var["valdesc"])."</string>\n"; break;
        case 'integer': $ret .= "    <integer>".Cvrt($var["valdesc"])."</integer>\n"; break;
        case 'float': $ret .= "    <float>".Cvrt($var["valdesc"])."</float>\n"; break;
        case 'boolean':
            $ret .= "    <boolean>\n";
            $ret .= "      <true>".Cvrt($var["valdesc"]["true"])."</true>\n";
            $ret .= "      <false>".Cvrt($var["valdesc"]["false"])."</false>\n";
            $ret .= "    </boolean>\n";
            break;
        case 'enum':
            $ret .= "    <enum>\n";
            foreach ($var["valdesc"] as $argname => $argdesc)
            {
                $ret .= "    <value>\n";
                $ret .= "      <name>".Cvrt($argname)."</name>\n";
                $ret .= "      <description>".Cvrt($argdesc)."</description>\n";
                $ret .= "    </value>\n";
            }
        	$ret .= "    </enum>\n";
        	break;
    }
    $ret .= "  </value>\n";
    
    if(strlen($var["remarks"]))
        $ret .= "  <remarks>".Cvrt($var["remarks"])."</remarks>\n";
    
    $ret .= "</variable>\n";
    
    return $ret;
}

function GetXML($file, $id, &$db)
{
    switch ($file) {
        case "variable": return GetXMLVar($db->GetVar($id)); break;
        case "command": return GetXMLCmd($db->GetCmd($id)); break;
    }
}

function MakeArchive($file, &$db)
{
	echo "<p>Removing old data...</p>\n";
	flush();
    passthru("rm -f ".BASEPATH."/{$file}s/*", $retval);
    echo "<p>Return code {$retval}</p>\n";
    passthru("rm -f ".BASEPATH."/{$file}s.zip", $retval);
    echo "<p>Return code {$retval}</p>\n";
	echo "<p>Done.</p>";
	flush();

	echo "<p>Retrieving list of data from the database.</p>\n";
    $entries = $db->GetList(1);
    echo "<p>Retrieved ".count($entries)." entries</p>\n";
    
    foreach ($entries as $id => $name)
    {
    	$filename = BASEPATH."/{$file}s/{$name}.xml"; 
        $fp = fopen($filename, "w");
        if (!$fp) {
			echo "Error: could not open $filename for writing\n";
			return;
		}
        fwrite($fp, GetXML($file, $id, $db));
        fclose($fp);
    }
    
    echo "<p>Files created</p>\n";
    
    echo "<p>Executing archiver...</p>\n";
    passthru("cd ".BASEPATH."/{$file}s ; find . | zip -9 -@ ../{$file}s.zip", $retval);
    echo "<p>Return value was {$retval}</p>\n";
    echp "<p>Release finished</p>";
}

function Archive($type)
{
    switch ($type) {
        case "variable": $db = new VariablesData; break;
        case "command": $db = new CommandsData; break;
        default: return; break;
    }
    
    return MakeArchive($type, $db);
}


?>

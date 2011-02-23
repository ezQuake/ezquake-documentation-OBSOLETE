<?php

/**
 * class Download
 * delivers documentation archives and XML files stored in 
 * /tmp/persistent/ezquake/describe
 */

define (BASEPATH, "/home/project-web/e/ez/ezquake/htdocs/docs/admin/release");
require_once("../inc/mysql_commands.php");

class Downloader
{
	function DownloadArchive($file)
	{
        header("Content-type: application/x-gzip");
        header('Content-Disposition: attachment; filename="ezquake_describe_'.$file.'.tar.gz"');
        readfile(BASEPATH."/{$file}.tar.gz");
        die;
    }
    
    function DownloadXML($file, $type)
    {
        header("Content-type: application/xml");
        header('Content-Disposition: attachment; filename="'.$file.'.xml"');
        readfile(BASEPATH."/{$type}s/{$file}.zip");
        die;    
    }

    function Downloader($request)
    {
        switch ($request) {
            case "commands": $this->DownloadArchive("commands"); break;
            case "variables": $this->DownloadArchive("variables"); break;
            default:
                $name = substr($request, 0, strlen($request) - 4);
                $extension = substr($request, -4);
                if ($extension == ".xml")
                {
                    $dbv = new VariablesData;
                    $dbc = new CommandsData;
                    
                    if ($dbv->GetId($name))
                        $this->DownloadXML($name, "variable");
                    elseif ($dbc->GetId($name))
                        $this->DownloadXML($name, "command");
                }
        }
    }


}

?>

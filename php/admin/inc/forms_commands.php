<?php

require_once("../inc/mysql_commands.php");
require_once("../inc/common-xmlparse.php");

/**
 * print forms and process data from forms
 */
class DocsForms
{
    var $data;
    var $editaction;
    var $whoami;

    function Rename($oldname, $newname, $userid)
    {
        if ($this->data->Rename($oldname, $newname, $userid))
            echo "{$this->whoami} has been renamed.";
        else
            echo "{$this->whoami} hasn't been renamed.";
    }
    
    function Remove($name, $physically, $userid)
    {
        if ($this->data->Remove($name, $physically, $userid))
            echo "{$this->whoami} has been removed.";
        else
            echo "{$this->whoami} has not been removed.";
    }
    
    function PrintList($detailed = 1, $orderby = 'name', $order = 'ASC')
    {
        $notdetailed = $detailed ? 0 : 1;
        $detailedtext = $detailed ? "Hide details" : "Full details";
        echo "<p><a href=\"?action={$this->editaction}s&amp;detailed={$notdetailed}\">{$detailedtext}</a></p>";
        
        if ($detailed)
        {
            $orderrev = $order == 'ASC' ? 'DESC' : 'ASC';
            echo "\n\n<table>\n<thead>\n<tr>";
            foreach ($this->data->detlistattrs as $k => $v)
                echo "\n  <td><a title=\"{$this->data->detlistdescs[$k]}\" href=\".?action={$this->editaction}s&detailed=1&amp;orderby={$k}&amp;order={$orderrev}\">{$k}</a></td>";

            echo "\n</tr>\n</thead>\n<tbody>";
            
            foreach ($this->data->GetDetailedList($orderby, $order) as $k => $v)
            {
                echo "\n<tr>";
                foreach ($this->data->detlistattrs as $attr => $_sql)
                    switch ($attr)
                    {
                        case "name":
                            echo "<td><a href=\"?action={$this->editaction}&amp;id=".$v["id"]."\">".$v["name"]."</a></td>";
                            break;
                        default:
                            echo "<td>{$v[$attr]}</td>";
                            break;
                    }
                echo "\n</tr>";
            }
            
            echo "\n</tbody>\n</table>\n";
        }
        else // not detailed
        {
            echo "<ul>\n";
            $lastletter = '';
            foreach ($this->data->GetList(0) as $k => $v)
            {   
                $letter = substr($v, 0, 1);
                if ($letter != $lastletter) 
                {
                    if ($lastletter = '')
                        echo "\n<li>\n";
                    else
                        echo "\n</li>\n<li>\n";
                }
                else
                {
                    echo ', ';
                }
                echo "<a href=\"?action={$this->editaction}&amp;id={$k}\">".$v."</a>";
                $lastletter = $letter;
            }
        }
        echo "\n</li>\n</ul>";        
    }
    
    function PrintHistory()
    {
        if (($rows = $_REQUEST["rows"]) < 1) $rows = 20;
        
        echo "<h2>".$this->whoami."s history</h2>";
        
        if (!($hist = $this->data->GetHistory($rows)))
            return False;

		echo "\n\n<table><thead><tr><td>Entry</td><td>Action</td><td>User</td><td>Time</td></tr></thead>\n<tbody>\n";

        foreach ($hist as $row)
        {
			echo "<tr><td><a href=\"/admin/index.php?action={$this->editaction}&id={$row['Id']}\">{$row['Entry']}</a></td><td>{$row['Action']}</td><td>{$row['User']}</td><td>{$row['Time']}</td></tr>";
        }
        
        echo "\n</tbody></table>\n\n";
    }
}

class CommandsForms extends DocsForms
{
    function CommandsForms()
    {
        $this->data = new CommandsData;
        $this->editaction = "editcommand";
        $this->whoami = "Command";
    }

    function FormCommand($id = 0)
    {
        $id = (int) $id;
        if ($id > 0)
        {
            $d = $this->data->GetCmd($id);
            $name = htmlspecialchars($d["name"]);
            $description = htmlspecialchars($d["description"]);
            $syntax = htmlspecialchars($d["syntax"]);
            $remarks = htmlspecialchars($d["remarks"]);
            $i = 0;
            $argnames = array(); $argdescs = array();
            if (isset($d["args"]))
                foreach ($d["args"] as $k => $v)
                {
                    $argnames[$i] = htmlspecialchars($k);
                    $argdescs[$i] = htmlspecialchars($v);
                    $i++;
                }
        }
        else
        {
            $name = $description = $syntax = $remarks = "";
            $argnames = $argdescs = array();
        }
        include("inc/form_add_command.php");
    }
    
    function ReadAddCmd($userId)
    {
        $data = array();
        $data["name"] = $_REQUEST["c_name"];
        $data["description"] = $_REQUEST["c_desc"];
        $data["syntax"] = $_REQUEST["c_syntax"];
        $data["remarks"] = $_REQUEST["c_remarks"];
        $userId = (int) $userId;
        
        $data["args"] = array();
        for ($i = 0; ($i <= MAXCMDARGS) && strlen($_REQUEST["c_arg_".$i."_name"]); $i++)
            $data["args"][$_REQUEST["c_arg_".$i."_name"]] = $_REQUEST["c_arg_".$i."_desc"];
        
        if (!$this->data->AddOrUpdate($data, $userId))
            echo "Command has not been added / updated.";
        else
            echo "Command has been added / updated.";
    }


}

class VariablesForms extends DocsForms
{
    function VariablesForms()
    {
        $this->data = new VariablesData;
        $this->editaction = "editvariable";
        $this->whoami = "Variable";
    }
    
    function FormVariable($id = 0)
    {
        $id = (int) $_REQUEST["id"];
        if ($id > 0)
        {
            $d = $this->data->GetVar($id);
            $name = htmlspecialchars($d["name"]);
            $description = htmlspecialchars($d["description"]);
            $remarks = htmlspecialchars($d["remarks"]);
            switch ($d["type"])
            {
                case "float":
                    $fltdesc = $d["valdesc"];
                    $fltchck = "checked=\"checked\"";
                    break;
                case "string":
                    $strdesc = $d["valdesc"];
                    $strchck = "checked=\"checked\"";
                    break;
                case "integer":
                    $intdesc = $d["valdesc"];
                    $intchck = "checked=\"checked\"";
                    break;
                case "boolean":
                    $true = $d["valdesc"]["true"];
                    $false = $d["valdesc"]["false"];
                    $blnchck = "checked=\"checked\"";
                    break;
                case "enum":
                    $enuchck = "checked=\"checked\"";
                    $i = 0;
                    $valnames = array(); $valdescs = array();
                    if (isset($d["valdesc"]))
                        foreach ($d["valdesc"] as $k => $v)
                        {
                            $valnames[$i] = htmlspecialchars($k);
                            $valdescs[$i] = htmlspecialchars($v);
                            $i++;
                        }
                    break;
            }
        }
        else
        {
            $name = $description = $remarks = "";
            $valname = $valdescs = array();
        }
        include("inc/form_add_variable.php");
    }

    function ReadAddVar($userId)
    {
        $data = array();
        $data["name"] = $_REQUEST["v_name"];
        $data["description"] = $_REQUEST["v_desc"];
        $data["remarks"] = $_REQUEST["v_remarks"];
        $data["type"] = $_REQUEST["v_type"];
        $userId = (int) $userId;
        
        switch ($data["type"])
        {
            case "float": $data["valdesc"] = $_REQUEST["v_float"]; break;
            case "string": $data["valdesc"] = $_REQUEST["v_string"]; break;
            case "integer": $data["valdesc"] = $_REQUEST["v_integer"]; break;
            case "boolean":
                $data["valdesc"]["true"] = $_REQUEST["v_boolean_true"];
                $data["valdesc"]["false"] = $_REQUEST["v_boolean_false"];
                break;
            case "enum":
                for ($i = 0; ($i <= MAXVARENUMS) && strlen($_REQUEST["v_val_".$i."_name"]); $i++)
                    $data["valdesc"][$_REQUEST["v_val_".$i."_name"]] = $_REQUEST["v_val_".$i."_desc"];
                break;        
        }
        
        if (!$this->data->AddOrUpdate($data, $userId))
            echo "{$this->whoami} has not been added / updated.";
        else
            echo "{$this->whoami} has been added / updated.";
    }
    
    function ReadMassAdd($userId)
    {
		$n = (int) $_POST["varscount"];
		$r = array();
		
		if (!$n) {
			echo "<p>No vars to add</p>";
			return;
		}
		
		for ($i = 0; $i < $n; $i++)
		{
			if (strlen($_POST["vardesc"][$i])) {
				$e = array();
				$e["name"] = $_POST["varname"][$i];
				$e["description"] = $_POST["vardesc"][$i];
				$e["remarks"] = $_POST["varremarks"][$i];
				$e["type"] = $_POST["vartype"][$i];
				$e["valdesc"] = "";
				$e["v_boolean_true"] = "";
				$e["v_boolean_false"] = "";
				$r[] = $e;
			}
		}
		
		$succ = $this->data->MassAdd($r, $userId);
		
		echo "<p>$succ variable(s) have been added.</p>";
	}
}

class ManualsForms extends DocsForms
{
    function ManualsForms()
    {
        $this->data = new ManualsData;
        $this->editaction = "editmanual";
        $this->whoami = "Manual page";
    }
    
    function FormManual($id = 0)
    {
        $id = (int) $id;
        if ($id > 0)
        {
            $d = $this->data->GetMan($id);
            $name = htmlspecialchars($d["name"]);
            $title = htmlspecialchars($d["title"]);
            $content = htmlspecialchars($d["content"]);
        }
        else
        {
            $name = $title = $content = "";
        }
        include("inc/form_add_manual.php");
    }
    
    function ReadAddMan($userId)
    {
        $data = array();
        $data["name"] = $_REQUEST["m_name"];
        if (!IsIdSafe($data["name"]))
        {
            echo "Name cannot be used in URL. You cannot use spaces and other special chars in 'name' field. Manual page hasn't been added / updated.";
            return False;
        }
        $data["title"] = $_REQUEST["m_title"];
        $data["content"] = $_REQUEST["m_content"];
        if (!CheckWellFormed($data["content"]))
            echo "<strong>Warning.</strong> Markup isn't well-formed.";

        $userId = (int) $userId;
        
        if (!$this->data->AddOrUpdate($data, $userId))
            echo "{$this->whoami} has not been added / updated.";
        else
            echo "{$this->whoami} has been added / updated.";
    }
}

class OptionsForms extends DocsForms
{
    function OptionsForms()
    {
        $this->data = new OptionsData;
        $this->editaction = "editoption";
        $this->whoami = "Command-line option";
    }
    
    function GetFlagsCheckboxes($flags_n)
    {
        $r = "";
        $flags_d = $this->data->GetFlagsNames($flags_n);
        foreach ($flags_d as $flagnum => $flagdata)
        {
            $checked = $flagdata["set"] ? "checked=\"checked\"" : "";
            $r .= "\n  <li><label><input type=\"checkbox\" name=\"o_flag_{$flagnum}\" {$checked} /> ".$flagdata["name"]."</label></li>";
        }
        return $r;
    }
    
    function FormOption($id = 0)
    {
        $id = (int) $id;
        if ($id > 0)
        {
            $d = $this->data->GetOption($id);
            $name = htmlspecialchars($d["name"]);
            $description = htmlspecialchars($d["description"]);
            $args = htmlspecialchars($d["args"]);
            $argsdesc = htmlspecialchars($d["argsdesc"]);
            $flags = $this->GetFlagsCheckboxes($d["flags"]);
        }
        else
        {
            $name = $title = $content = "";
            $flags = $this->GetFlagsCheckboxes(0);
        }
        include("inc/form_add_option.php");
    }
    
    function ReadAddOpt($userId)
    {
        $userId = (int) $userId;
        $data = array();
        $data["name"] = $_REQUEST["o_name"];
        $data["description"] = $_REQUEST["o_description"];
        $data["args"] = $_REQUEST["o_args"];
        $data["argsdesc"] = $_REQUEST["o_argsdesc"];
        
        $flagshi = pow(2, count($this->data->GetFlagsList()) - 1);
        $c = 0;
        for ($i = 1; $i <= $flagshi; $i *= 2)
            if ($_REQUEST["o_flag_".$i])
                $c += $i;
        
        $data["flags"] = $c;

        if (!$this->data->AddOrUpdate($data, $userId))
            echo "{$this->whoami} has not been added / updated.";
        else
            echo "{$this->whoami} has been added / updated.";
    }

}

class GroupsForms
/* for both variables_groups & variables_mgroups */
{
    var $gdata;
    var $mgdata;
    
    function GroupsForms()
    {
        $this->gdata = new GroupsData;
        $this->mgdata = new MGroupsData;
    }
    
    function OptionsListMG($mark = 0)
    {
        static $data;
        
        if (!$data) 
        {
            if (!($data = $this->mgdata->GetList()))
                return False;
        }

        $ret = "";
        foreach($data as $k)
        {   
            $selected = $k["id"] == $mark ? "selected=\"selected\"" : "";
            $ret .= "<option value=\"".$k["id"]."\" {$selected}>".$k["title"]."</option>";
        }
        
        return $ret;
    }

    function OptionsListG($mark = 0)
    {
        if (!($data = $this->gdata->GetList(1)))
            return False;

        $ret = "";
        foreach($data as $k)
            $ret .= "<option value=\"".$k["id"]."\">".$k["title"]."</option>";
        
        return $ret;
    }

    
    function PrintListG()
    {
        if (!($d = $this->gdata->GetList()))
            return False;
        
        echo "\n<table>\n<thead><tr><td>id</id><td>Group</td><td>Major Group</td></tr></thead>\n<tbody>\n";
        foreach($d as $k)
            // to-do!!
            echo "\n<tr><td>".$k["id"]."</td><td>".$k["title"]."</td><td><select name=\"group_assignment[".$k["id"]."]\">".$this->OptionsListMG($k["id_mgroup"])."</select></td></tr>";

        echo "\n</tbody>\n</table>\n";
    }

    function RenameG() 
    {
        if (!$this->gdata->Rename($_REQUEST["group_id"], $_REQUEST["g_name"]))
            echo "Variables Group hasn't been renamed.";
        else
            echo "Variables Group has been renamed.";
    }
    
    function RenameMG() 
    {
        if (!$this->mgdata->Rename($_REQUEST["mgroup_id"], $_REQUEST["mg_name"]))
            echo "Variables Major Group hasn't been renamed.";
        else
            echo "Variables Major Group has been renamed.";
    }
    
    function RemoveG() 
    {
        if (!$this->gdata->Remove($_REQUEST["group_id"]))
            echo "Variables Group hasn't been removed.";
        else
            echo "Variables Group has been removed.";
    }
    
    function RemoveMG() 
    {
        if (!$this->mgdata->Remove($_REQUEST["mgroup_id"], $_REQUEST["to_mgroup_id"]))
            echo "Variables Major Group hasn't been removed.";
        else
            echo "Variables Major Group has been removed.";
    }
    
    function AddG() 
    {
        if (!$this->gdata->Add($_REQUEST["g_name"], $_REQUEST["mgroup_id"]))
            echo "Variables Group hasn't been added.";
        else
            echo "Variables Group has been added.";
    }
    
    function AddMG() 
    {
        if(!$this->mgdata->Add($_REQUEST["mg_name"]))
            echo "Variables Major Group hasn't been added.";
        else
            echo "Variables Major Group has been added.";
    }
    
    function MultiAssign()
    {   
        $s = 0; $f = 0;
        foreach ($_POST["group_assignment"] as $group_id => $mgroup_id)
            if (!$this->gdata->Assign($group_id, $mgroup_id))
                $f++;
            else
                $s++;
        
        if ($f == 0 && $s == 0)
            echo "No variables groups reassigned.";
        
        if ($f > 0)
            echo "Reassigning groups failed. Variables reassigned: {$s}.";
            
        if ($f == 0 && $s > 0)
            echo "All {$s} variable groups have been reassigned.";
    }    
}

class SupportForms
{
    var $data;
    var $grdata;
    var $vardata;
    
    function SupportForms()
    {
        $this->data = new SupportData;
        $this->grdata = new GroupsData;
        $this->vardata = new VariablesData;
        $this->cmddata = new CommandsData;
    }
    
    function OptionsBuilds()
    {
        if (!($r = $this->data->GetBuilds()))
        {
            echo "Getting build list failed.";
            return;
        }
        
        $ret = "";
        foreach ($r as $f)
            $ret .= "<option value=\"".$f["id"]."\">".$f["title"]."</option>";
        
        return $ret;
    }
    
    function ReadVarsSetDefaults()
    /* Reads user-uploaded file with cvars default values.
       Sets proper vargroup and stores default value for given build. */
    {
        $filename = $_FILES['userfile']['tmp_name'];
        $id_build = (int) $_REQUEST["id_build"];
        if (!($defaults = ParseCvarsConfig($filename)))
        {
            echo "Config parsing failed.";
            return;
        }
        // print_r($defaults); return;
        if (!$this->data->ClearBuildSupport($id_build))
        {
            echo "Error while clearing support table.";
            return;
        }
         
        $varids = $this->vardata->GetIds();       
        $grpids = $this->grdata->GetIds();

        $d = array();
        foreach ($defaults as $var_group => $var_defaults)
        {
            $vgroup_id = (int) $grpids[IdSafe($var_group)];
            if (!vgroup_id) // new group found in config
                $vgroup_id = $this->grdata->AddG($var_group);
                
            $var_ids = array();
            foreach ($var_defaults as $var_name => $var_default)
            {
                $var_id = $varids[$var_name];
                $var_ids[] = $var_id;
                $n = array();
                $n["id_variable"] = $var_id;
                $n["id_build"] = $id_build;
                $n["default"] = $var_default;
                $d[] = $n;
            }
            $this->vardata->SetGroup($var_ids, $vgroup_id);
        }
        $this->data->VarsSetDefaults($d);
    }
    
    function PhysUploadForm()
    {
		include ("inc/form_add_phys.php");
	}
	
	function TypeMiniRadios($c)
	{
		$types = array("string", "integer", "float", "boolean", "enum");
		$i = 0;
		foreach ($types as $t) {
			// if ($i++) echo " | ";
			echo "    <label><input type='radio' name='vartype[{$c}]' value='{$t}' />".$t{0}."</label>\n";
		}
	}
	
	function ViewMissingVars()
	{
		$mv = $this->vardata->GetMissingVars();
		echo "<h3>Undocumented vars</h3>";
		if (!$mv[1]) {
			echo "<p>No vars found</p>";
		} else
		{
			echo "\n<form action='' method='post'>\n";
			echo "<input type='hidden' name='varscount' value='".count($mv[1])."' />\n";
			echo "<input type='hidden' name='action' value='massaddvar'>\n";
			echo "<table><thead><tr><th>Variable</th><th>Description</th><th>Type</th><th>Remarks</th></tr></thead>\n<tbody>\n";
			$c = 0;
			foreach($mv[1] as $v)
			{
				echo "<tr>\n";
				echo "  <td>{$v}</td>\n";
				echo "  <td><input type='hidden' name='varname[{$c}]' value='".htmlspecialchars($v)."' />"
					."<input type='text' name='vardesc[{$c}]' value='' size='20' /></td>\n";
				echo "  <td>\n";
				$this->TypeMiniRadios($c);
				echo "  </td>\n";
				echo "  <td><input type='text' name='varremarks[{$c}]' value='' size='20' /></td>\n";
				echo "</tr>";
				$c++;
			}
			echo "</tbody>\n</table>\n\n";
			echo "<input type='submit' value='Submit' />\n";
			echo "</form>";
		}

		echo "<h3>Documented vars that are missing in the client</h3>";
		if (!$mv[0]) {
		    echo "<p>No vars found</p>";
		} else
		{		
			echo "<ol>";
			foreach($mv[0] as $v)
			{
				echo "\n<li>{$v}</li>";
			}
			echo "</ol>";
		}	
	}
	
	function AddPhysVars()
	{
		$pl = $_REQUEST["physlist"];
		$vars = explode("\r\n", $pl);
		$this->vardata->AddPhysVars($vars);
		echo "<p>Physical variables added.</p>";
	}
	
	function ClearPhysVars()
	{
		$this->vardata->ClearPhysVars();
		echo "<p>Physical variables cleared. You should upload a new list now.</p>";
	}
	
    function PhysCmdUploadForm()
    {
		include ("inc/form_add_physcmd.php");
	}
	
	function ViewMissingCmds()
	{
		$mv = $this->cmddata->GetMissingCmds();
		echo "<h3>Documented cmds that are missing in the client</h3>";
		
		if (!$mv[0]) {
		    echo "<p>No commands found</p>";
		} else
		{
			echo "<ol>";
			foreach($mv[0] as $v)
			{
				echo "\n<li>{$v}</li>";
			}
			echo "</ol>";
		}		

		echo "<h3>Undocumented cmds</h3>";
		if (!$mv[1]) {
		    echo "<p>No commands found</p>";
		} else
		{
			echo "<ol>";
			foreach($mv[1] as $v)
			{
				echo "\n<li>{$v}</li>";
			}
			echo "</ol>";
		}	
	}
	
	function AddPhysCmds()
	{
		$pl = $_REQUEST["physlist"];
		$vars = explode("\r\n", $pl);
		$this->cmddata->AddPhysCmds($vars);
		echo "<p>Physical commands added.</p>";
	}
	
	function ClearPhysCmds()
	{
		$this->cmddata->ClearPhysCmds();
		echo "<p>Physical commands removed. You should upload new list now.</p>";
	}
}

class IndexForms
{   // admins can only update index
    var $data;
    function IndexForms() {
        $this->data = new IndexData;
    }
    
    function Update() {
        if ($this->data->Refresh())
            echo "<p>Index updated.</p>";
        else
            echo "<p>Update failed.</p><p><code>".mysql_error()."</code></p>";
    }
}

class SearchHitsForms
{   // admins can only view top queries
    
    var $data;
    function SearchHitsForms() {
        $this->data = new SearchHits;
    }
    
    function PrintTopQueries($count = 30)
    {
        $data = $this->data->GetTopHits($count);
        echo "\n<table>";
        echo "<caption>Top&nbsp;{$count}&nbsp;searched&nbsp;queries</caption>\n";
        echo "<thead><tr><td>query</td><td>hits</td></tr></thead>\n<tbody>";
        foreach ($data as $d)
        {  
            echo "\n  <tr><td><a href=\"../?search={$d[0]}&amp;omitstats=1\">{$d[0]}</a></td><td>{$d[1]}</td></tr>";
        }
        echo "\n</tbody>\n</table>\n";
    }
}

?>

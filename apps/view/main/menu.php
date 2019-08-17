<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/ddlevelsmenu-base.css")); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/ddlevelsmenu-topbar.css")); ?>"/>

<script type="text/javascript" src="<?php print($helper->path("public/js/ddlevelsmenu.js")); ?>"></script>
<script type="text/javascript">
	/***********************************************
	 * All Levels Navigational Menu- (c) Dynamic Drive DHTML code library (http://www.dynamicdrive.com)
	 * This notice MUST stay intact for legal use
	 * Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	 ***********************************************/
	$(document).ready(function() {
		// Change some variable
		ddlevelsmenu.enableshim = false;
		ddlevelsmenu.arrowpointers = {
			downarrow:["<?php print($helper->path("public/css/images/arrow-down.gif")); ?>", 11, 7], //[path_to_down_arrow, arrowwidth, arrowheight]
			rightarrow:["<?php print($helper->path("public/css/images/arrow-right.gif")); ?>", 12, 12], //[path_to_right_arrow, arrowwidth, arrowheight]
			showarrow:{toplevel:true, sublevel:true} //Show arrow images on top level items and sub level items, respectively?
		};
		ddlevelsmenu.hideinterval = 500;
		ddlevelsmenu.effects = {enableswipe:true, enableslide:false, enablefade:false, duration:250};

		ddlevelsmenu.init("mainMenu", "topbar");
	});
</script>

<?php
include_once(USER_CONFIG . "sitemap.php");

function GenerateMenuLink(Node $node, AppHelper $helper) {

	switch (strtolower($node->Type)) {
		case "url":
		case "link":
			if (!$node->Visible) {
				return;
			}
			printf('<li><a class="menu-link" href="%s">%s</a>', $helper->site_url($node->Url), $node->Text);
			break;
		case "submenu":
			if (!$node->Visible) {
				return;
			}
			printf('<li><a class="sub-menu">%s</a>', $node->Text);
			break;
		case "title":
			if (!$node->Visible) {
				return;
			}
			printf('<li><a><span class="menu-title bold center">%s</span></a>', $node->Text);
			//printf('<li><span class="menu-title bold center">%s</span>', $node->Text);
			break;
		case "sep":
		case "divider":
			return;
			print('<li><span class="separator">&nbsp;</span>');
			break;
	}

	// Loop sub-menu
	if (count($node->Nodes) > 0) {
		print("<ul>");
		foreach ($node->Nodes as $childNode) {
			GenerateMenuLink($childNode, $helper);
		}
		print("</ul>");
	}

	// Tutup </li> sesudah semua sub-menu beres
	print("</li>");
}

$persistence = PersistenceManager::GetInstance();

$isCorporate = $persistence->LoadState("is_corporate");
$entityId = $persistence->LoadState("entity_id");
$sbu = $persistence->LoadState("entity_cd");
$prn = $persistence->LoadState("project_name");

$realName = AclManager::GetInstance()->GetCurrentUser()->RealName;

// Periode jika ada
if ($persistence->LoadState("force_periode")) {
	$_monthNames = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
	$_month = $persistence->LoadState("acc_month");
	$_year = $persistence->LoadState("acc_year");

	if ($_month == null || $_year == null) {
		$periode = sprintf('Periode Akuntansi: <a href="%s" class="bold" style="color: blue;">[SETTING DISINI]</a>&nbsp;&nbsp;&nbsp;', $helper->site_url("main/set_periode"));
	} else {
		$periode = sprintf('Periode Akuntansi: <span class="bold">%s %s</span>&nbsp;&nbsp;&nbsp;', $_monthNames[$_month - 1], $_year);
	}
} else {
	$periode = null;
}

$_rhs = $periode . "Login As: " . $realName . "@" . $sbu . ' - ' .$prn;
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
	<tr align="center" class="subTitle">
		<td align="left">REKASYS - PT. MANADO KARYA ANUGRAH</td>
		<td align="right">
			<?php print($_rhs);?>
		</td>
	</tr>
</table>

<div id="mainMenu" class="mattblackmenu" style="margin: 5px auto;">
	<ul>
		<?php
		foreach ($root->Nodes as $idx => $menu) {
			if ($menu->Visible) {
				if ($menu->Type == "menu") {
					printf('<li><a rel="sub-menu-%d">%s</a></li>', $idx, $menu->Text);
				} else {
					printf('<li><a href="%s" class="menu-link">%s</a></li>', $helper->site_url($menu->Url), $menu->Text);
				}
			}
		}
		?>
	</ul>
</div>

<!-- Sub-Menus -->
<?php
foreach ($root->Nodes as $idx => $menu) {
	if (!$menu->Visible) {
		continue;
	}
	printf('<div id="sub-menu-%d" class="ddsubmenustyle">', $idx);
	foreach ($menu->Nodes as $node) {
		GenerateMenuLink($node, $helper);
	}
	print("</div>\n");
}
?>

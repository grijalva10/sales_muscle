<?php
$install = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'install.sql'));
$installPla = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'install-pla.sql'));
$uninstall = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'uninstall.sql'));
$uninstallPla = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'uninstall-pla.sql'));
$formLayouts = array("INSERT INTO `x2_form_layouts` VALUES (1,'Contacts','Form','Default','{\"version\":\"5.2\",\"sections\":[{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_firstName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_lastName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"},{\"items\":[{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"}]}],\"collapsible\":false,\"title\":\"Contact Info\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"},{\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"Sales &amp; Marketing\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_address2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"},{\"items\":[{\"name\":\"formItem_state\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"Address\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"99.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"99.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"\"}]}',0,1,1439008529,1439008529),(2,'Contacts','View','Default','{\"version\":\"5.2\",\"sections\":[{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":0},{\"name\":\"formItem_title\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"},{\"items\":[{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":0},{\"name\":\"formItem_company\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"}]}],\"collapsible\":false,\"title\":\"Contact Info\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_leadtype\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadscore\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"},{\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_closedate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_rating\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_dealstatus\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"Sales &amp; Marketing\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_address2\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_city\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"},{\"items\":[{\"name\":\"formItem_state\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_zipcode\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_country\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"Address\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"99.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_priority\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"99.83%\"}]}],\"collapsible\":true,\"collapsedByDefault\":true,\"title\":\"\"}]}',1,0,1439008529,1439008529),(20,'Contacts','Inline View','Inline','{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"Contact Info\",\"rows\":[{\"cols\":[{\"width\":279,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"189\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_priority\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"},{\"name\":\"formItem_address\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"0\"}]},{\"width\":309,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":589,\"items\":[{\"name\":\"formItem_backgroundInfo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"71\",\"width\":\"493\",\"tabindex\":\"0\"}]}]}]}]}',1,0,1439008529,1439008529);");

return array(
	'name' => "Contacts",
	'install' => file_exists($installPla)? array($install, $installPla, $formLayouts) : array($install, $formLayouts),
	'uninstall' => file_exists($uninstallPla)? array($uninstall, $uninstallPla) : array($uninstall),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0',
);
?>
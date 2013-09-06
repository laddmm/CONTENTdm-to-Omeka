<?php

/* IMPORT CONTENTdm VIEWER AND METADATA RECORDS INTO AN OMEKA PAGE

Metadata import by: Marcus Ladd (laddmm@miamioh.edu), Special Collections Librarian, Miami University.
Viewer import by: Elias Tzoc (tzoce@miamioh.edu), Digital Initiatives Librarian at Miami University.
Mobile device detector by: user 'iamandrus' at stackoverflow.com.

This code replaces the standard Omeka display with CONTENTdm's viewer, as well as importing selected metadata records from CONTENTdm. Since the CONTENTdm viewer is not navigable by a touchscreen, mobile device detection has been made available. If activated and a mobile display is detected, a thumbnail will be displayed with a link to the full-size image (if available) in Omeka instead of the CONTENTdm viewer.

CONFIGURATION
1. Change [yourcollection] in the line below to your collection's handle in CONTENTdm (remove the square brackets) */
$collection = "[yourcollection]";
/* 2. Change [yourserver] in the line below to your CONTENTdm server address (remove the square brackets) */
$server = "[yourserver]";
/* 3. Change [yourport] in the line below to your CONTENTdm server port (remove the square brackets) */
$port = "[yourport]";

/*
INSTRUCTIONS
1.  Create a record for the item in Omeka with a Title. If you wish to have the mobile detection enabled, include an image file in the Omeka record.
2. Add the URL to the fullscreen view of the object to the Source field (e.g. http://contentdm.lib.muohio.edu/cdm/fullbrowser/collection/myaamia/id/143/rv/compoundobject/cpd/155).
3. Insert this code into the file /[yourtheme]/items/show.php, replacing the echo all_element_texts command and the section: <!-- The following returns all of the files associated with an item. --> (e.g. in the theme 'seasons' these are lines 5-16)
4. By default, the mobile detection is enabled. If you wish this removed, delete lines 27-62 and 77.
5. Metadata is configured beginning at line 88.

USER-AGENTS by user 'iamandrus' at stackoverflow.com. Source: http://stackoverflow.com/questions/6524301/detect-mobile-browser
*/
function check_user_agent ( $type = NULL ) {
        $user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
        if ( $type == 'bot' ) {
                // matches popular bots
                if ( preg_match ( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent ) ) {
                        return true;
                        // watchmouse|pingdom\.com are "uptime services"
                }
        } else if ( $type == 'browser' ) {
                // matches core browser types
                if ( preg_match ( "/mozilla\/|opera\//", $user_agent ) ) {
                        return true;
                }
        } else if ( $type == 'mobile' ) {
                // matches popular mobile devices that have small screens and/or touch inputs
                // mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
                // detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here
                if ( preg_match ( "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent ) ) {
                        // these are the most common
                        return true;
                } else if ( preg_match ( "/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent ) ) {
                        // these are less common, and might not be worth checking
                        return true;
                }
        }
        return false;
}

$ismobile = check_user_agent('mobile');
if($ismobile) {
	echo '<div id="itemfiles" class="element">' . files_for_item(array('imageSize' => 'thumbnail', 'linkToFile' => 'fullsize')) . '</div>';
	}

else {
	$cdm_url = metadata('item', array('Dublin Core', 'Source'));
	
	if (stripos($cdm_url,'/cpd') !==false) {
		$pos = stripos($cdm_url, 'cpd');
		$cpd_id = substr($cdm_url, $pos+4, 5);
		}

	else {
		$pos = stripos ($cdm_url,'/id/');
		$cpd_id = strstr ($pos,'/rv/',true);
		}

	echo '<iframe src="' . $cdm_url . '" style="width: 95%;" height="600" scrolling="no" ></iframe>';	

}

echo "<br />";

/*The line below calls up the CONTENTdm API to get the item info. It will look something like "http://contentserver.lib.muohio.edu:81/dmwebservices/index.php?q=dmGetItemInfo/myaamia/161/json". */
$metadata_url = "http://" . $server . ":" . $port . "/dmwebservices/index.php?q=dmGetItemInfo/" . $collection . "/" . $cpd_id . "/json";

$raw_data = file_get_contents($metadata_url);

$data = json_decode($raw_data);

/* METADATA

By default, this mod is configured to display the Simple Dublin Core fields. Only fields with entries will be displayed. Qualified Dublin Core fields can be added by modifying the $fields array. The keys are visible in a var_dump() or print_r () of $data. When adding new fields, be sure to check the keys of the current fields - the nicknames of some subfields in CONTENTdm are determined by what other fields are presented.

*/

$fields = array(
	'title' => "Title",
	'subjec' => "Subject",
	'descri' => "Description",
	'creato' => "Creator",
	'publis' => "Publisher",
	'contri' => "Contributors",
	'date' => "Date",
	'type' => "Type",
	'format' => "Format",
	'identi' => "Identifier",
	'source' => "Source",
	'langua' => "Language",
	'relati' => "Relation",
	'covera' => "Coverage",
	'rights' => "Rights",
	);

foreach ($data as $key => $value) {
echo "<strong>" . $fields[$key] . ":</strong> " . $value . "<br />";
}

?>

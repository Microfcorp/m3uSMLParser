<?php
$mods_serv = [];

define("DefaultGroupe", "1000");
$ModuleName = "m3uToXml";
$ModuleVersion = "1.1";
$ModuleAuthor = "MicrofDev";
$ModuleDescreption = "Парсер m3u в xml";
$ModuleAction = function($parametr){
	$file_urls = $parametr[0];
	$outs_url = $parametr[1];
	$sv = "";
	foreach($file_urls as $tmp){
		$sv = $sv . loadfile($tmp);
	}
	file_put_contents($outs_url, $sv);
};

$ChanenelGroups = [];
$Groups = [];

function loadfile($url){
	$m3ufile = file_get_contents($url);	
	$m3u = parsem3u($m3ufile, $url);
	return GenerationXMLPL($m3u);
}

function parsem3u($m3ufile, $playlist){
	$re = '/#EXTINF:(.+?)[,]\s?(.+?)[\r\n]+?((?:https?|rtmp):\/\/(?:\S*?\.\S*?)(?:[\s)\[\]{};"\'<]|\.\s|$))/';
	$attributes = '/([a-zA-Z0-9-]+?)=[\'"]([^\'"]*)[\'"]/';

	$m3ufile = str_replace("tvg-id", "id", $m3ufile);
	$m3ufile = str_replace("tvg-logo", "logo", $m3ufile);
	$m3ufile = str_replace("group_id", "group-id", $m3ufile);
	//$m3ufile = str_replace("tvg-name", "tvtitle", $m3ufile);
	//$m3ufile = str_replace(' ', '_', $m3ufile); // FOR GROUP

	$i = 0;
	preg_match_all($re, $m3ufile, $matches);
	$items = array();

	 foreach($matches[0] as $list) {
		$i = $i + 1;
	 
	    preg_match($re, $list, $matchList);
	    $mediaURL = preg_replace("/[\n\r]/","",$matchList[3]);
	    $mediaURL = preg_replace('/\s+/', '', $mediaURL);

	    $newdata =  array (
		 'id' => $i,
		 'num' => $i,
		 'tvtitle' => $matchList[2],
		 'descreption' => "ТВ - канал",
		 'tvmedia' => $mediaURL,
		 );

		 preg_match_all($attributes, $list, $matches, PREG_SET_ORDER);

		 foreach ($matches as $match) {
		    $newdata[$match[1]] = $match[2];
		 }
		 $items[] = $newdata;	 
	 }
	GenerationGroups($items);
	return $items;	 
}

function GenerationXMLPL($m3u){
	global $Groups;
	global $ChanenelGroups;
	
	$sets = LoadSettings("set.bin");
	$picfolder = $sets['picfolder'];
	$posterfolder = $sets['posterfolder'];
	$str = "<?xml version='1.0' encoding='utf-8'?><channels_list code=\"0\">\r\n";
	$str .= "<channels channelPackageId=\"".$sets['channelPackageId']."\" locationId=\"".$sets['locationId']."\" subLocationId=\"".$sets['locationId']."\" version=\"".$sets['version']."\">\r\n";
	foreach($m3u as $ch){
		$str .= "<channel>\r\n";
		$str .= "<bcid>".$ch['id']."</bcid>\r\n";
		$str .= "<bc_r_id>".$ch['id']."</bc_r_id>\r\n";
		$str .= "<bcname>".$ch['tvtitle']."</bcname>\r\n";
		$str .= "<bcdesc>".AGK($ch, 'tvg-desc', AGK($ch, 'tvg-name', $ch['descreption']))."</bcdesc>\r\n";
		$str .= "<url>".AGK($ch, 'url', 'igmp://225.77.225.1:5000')."</url>\r\n";
		$str .= "<hqUrl>".AGK($ch, 'hqUrl')."</hqUrl>\r\n";
		$str .= "<pipUrl>".AGK($ch, 'pipUrl')."</pipUrl>\r\n";
		$str .= "<plcUrl>".AGK($ch, 'plcUrl')."</plcUrl>\r\n";
		$str .= "<backupUrl1>".AGK($ch, 'backupUrl1')."</backupUrl1>\r\n";
		$str .= "<backupUrl2>".AGK($ch, 'backupUrl2')."</backupUrl2>\r\n";
		$str .= "<logo>".ParseIMG(AGK($ch, 'logo', $sets['defaultlogo']), $picfolder)."</logo>\r\n"; //ПАРСЕР ЛОГО
		$str .= "<logo2>".ParseIMG(AGK($ch, 'logo2', $sets['defaultlogo']), $picfolder)."</logo2>\r\n";
		$str .= "<bcal>".$ChanenelGroups[$ch['id']]."</bcal>\r\n"; //ПАРСЕР ГРУППЫ
		$str .= "<num>".AGK($ch, 'num')."</num>\r\n";
		$str .= "<bceid>".AGK($ch, 'num')."</bceid>\r\n";
		$str .= "<is_crypted>".AGK($ch, 'is_crypted', '1')."</is_crypted>\r\n";
		$str .= "<isDvrCrypted>".AGK($ch, 'isDvrCrypted', '1')."</isDvrCrypted>\r\n";
		$str .= "<soundVolume>".AGK($ch, 'soundVolume', '100')."</soundVolume>\r\n";
		$str .= "<raptorPort/>\r\n";
		$str .= "<isErotic>".AGK($ch, 'censored', '0')."</isErotic>\r\n";
		$str .= "<streamAspectRatio>".AGK($ch, 'aspect-ratio', '1')."</streamAspectRatio>\r\n";
		$str .= "<startTimeRestrictUTCsec/>\r\n";
		$str .= "<endTimeRestrictUTCsec/>\r\n";
		$str .= "<version>".$sets['version']."</version>\r\n";
		$str .= "<zoomRatio>".AGK($ch, 'zoomRatio', '.5')."</zoomRatio>\r\n";
		$str .= "<ottURL>".AGK($ch, 'tvmedia')."</ottURL>\r\n";
		$str .= "<smlOttURL>".AGK($ch, 'tvmedia')."</smlOttURL>\r\n";
		$str .= "<ottDvr>".AGK($ch, 'ottDvr', '1')."</ottDvr>\r\n";
		$str .= "<tstvOttURL>".AGK($ch, 'tstvOttURL')."</tstvOttURL>\r\n";
		$str .= "<plOttURL>".AGK($ch, 'plOttURL')."</plOttURL>\r\n";
		$str .= "<httpUrl>".AGK($ch, 'httpUrl')."</httpUrl>\r\n";
		$str .= "<epgOffset>".AGK($ch, 'tvg-shift')."</epgOffset>\r\n";
		$str .= "<dvbtChannelName>".AGK($ch, 'dvbtChannelName')."</dvbtChannelName>\r\n";
		$str .= "<poster>".ParseIMG(AGK($ch, 'poster'), $posterfolder)."</poster>\r\n";
		$str .= "<isOttEncrypted>".AGK($ch, 'isOttEncrypted', '1')."</isOttEncrypted>\r\n";
		$str .= "<nPVRChannelID>".AGK($ch, 'nPVRChannelID', 'npvr')."</nPVRChannelID>\r\n";
		$str .= "<isQualityMonitoring>".AGK($ch, 'isQualityMonitoring', '0')."</isQualityMonitoring>\r\n";
		$str .= "<isTestStreamQuality>".AGK($ch, 'isTestStreamQuality', '0')."</isTestStreamQuality>\r\n";
		$str .= "<isBarker>".AGK($ch, 'isBarker', '0')."</isBarker>\r\n";
		$str .= "<promo_url>".AGK($ch, 'promo_url')."</promo_url>\r\n";
		$str .= "<videoServerProtocol>".AGK($ch, 'videoServerProtocol')."</videoServerProtocol>\r\n";
		$str .= "<subjects/>\r\n";
		$str .= "<packages>\r\n";
		$str .= "<id>".$sets['PacId']."</id>\r\n";
		$str .= "</packages>\r\n";
		$str .= "<loc>\r\n";
		$str .= "<id>".$sets['locationId']."</id>\r\n";
		$str .= "</loc>\r\n";
		$str .= "<excl/>\r\n";
		$str .= "<accs/>\r\n";
		$str .= "<stbFunctions>".AGK($ch, 'stbFunctions')."</stbFunctions>\r\n";
		$str .= "<networkTypes>".AGK($ch, 'networkTypes')."</networkTypes>\r\n";
		$str .= "<audioPIDs>".AGK($ch, 'audio-track-num')."</audioPIDs>\r\n";
		$str .= "<subtitlePIDs>".AGK($ch, 'subtitlePIDs')."</subtitlePIDs>\r\n";
		$str .= "<urls>".AGK($ch, 'urls')."</urls>\r\n";
		$str .= "<ott_urls>".AGK($ch, 'ott_urls')."</ott_urls>\r\n";
		$str .= "<dvbUrls>".AGK($ch, 'dvbUrls')."</dvbUrls>\r\n";
		$str .= "</channel>\r\n";
	}
	return $str;
}

function GenerationGroups($m3u){ //1000 - без группы
	//1
	//Основная
	//2
	//дополнительная
	global $Groups;
	global $ChanenelGroups;
	$sets = LoadSettings("set.bin");
	
	$savegrtmp = "";
	foreach($m3u as $tmp){
		if(isset($tmp['group-title']) && !in_array($tmp['group-title'], $Groups)){
			$Groups[] = $tmp['group-title'];
			$savegrtmp .= $tmp['group-title'] . "\r\n" . (count($Groups) - 1) . "\r\n";
		}
		$ChanenelGroups[$tmp['id']] = isset($tmp['group-title']) ? array_search($tmp['group-title'], $Groups) : (isset($tmp['group-id']) ? $tmp['group-id'] : DefaultGroupe);
	}
	file_put_contents($sets['GrID'], $savegrtmp);
	GenerationCHGroup($sets);
}

function GenerationCHGroup($sets){
	global $Groups;
	global $ChanenelGroups;
	
	$savetmp = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$savetmp .= "<rows code=\"0\">\n";
	$savetmp .= "<parametrs>\n";
	foreach($ChanenelGroups as $key => $tmp){
		$savetmp .= "<chid".$key.">".$tmp."</chid".$key.">\n";
	}	
	$savetmp .= "</parametrs>\n";
	file_put_contents($sets['ChGr'], $savetmp);
}

function ParseIMG($img, $picfolder){
	//global $picfolder;
	if(IsUrl($img)){
		return $img;
	}
	elseif($img != "")
		return $picfolder . $img;
	return "";
}

function AGK($arr, $key, $def = ""){
	return (isset($arr[$key]) ? $arr[$key] : $def);
}

function LoadSettings($file){
	$s = file_get_contents($file);
	$q = [];
	$strs = explode(PHP_EOL, $s);
	foreach($strs as $tmp){
		$a = explode('=', $tmp);
		$q[$a[0]] = $a[1];
	}
	return $q;
}

function IsUrl($url){
    return strpos($url, "http") === true || strpos($url, "https") === true;
}

$ModuleAction([["PL.m3u"], "test", "set.bin"]);

$mods_serv[] = [$ModuleName, $ModuleVersion, $ModuleAuthor, $ModuleDescreption, $ModuleAction];
echo "ok\n";
?>
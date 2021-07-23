<?
  
  
//   // Имя файла для кеша
//   define (CFILENAME, '/var/www/vm.ua/exec/price_loy_tmp.html');
//   
//   // Время кеширования (1 час)
//   define (CFILETIME, 3600);
//   
//   if (!(file_exists(CFILENAME) && (time()-filemtime(CFILENAME)<CFILETIME))) { 

  
	set_time_limit(0);
    $_SERVER['DOCUMENT_ROOT'] = str_replace('/exec/cron','',dirname(realpath(__FILE__)));
    include($_SERVER['DOCUMENT_ROOT'].'/sunsite.config');
	require_once($_SERVER['DOCUMENT_ROOT'].'/modules/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/lib/globals.php');
	include($_SERVER['DOCUMENT_ROOT'].'/lib/classes/rozetmap.class.php');
  $map = new Rozetmap();
  $catl = array();
  
	
	$map->baseurl = 'https://'.SUNSITE_DOMAIN;
	$map->langid = 1;
	$map->gzip = false;
  header("Content-Type: text/xml");
  
  $out=$map->getheader();
  // echo $map->getsecmap();
  $offers=$map->getoffermap();
  $out .= $map->getsecmap().$offers;
  echo $out;
  echo '</shop>'."\n".
       '</yml_catalog>'; 
  die();    
  
?>
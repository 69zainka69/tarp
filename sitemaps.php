<?
  
	set_time_limit(0);
    $_SERVER['DOCUMENT_ROOT'] = str_replace('/exec/cron','',dirname(realpath(__FILE__)));
    include($_SERVER['DOCUMENT_ROOT'].'/sunsite.config');
	require_once($_SERVER['DOCUMENT_ROOT'].'/modules/config.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/lib/globals.php');
	include($_SERVER['DOCUMENT_ROOT'].'/lib/classes/sitemaps.class.php');
    $map = new Sitemaps();
	$map->excludePages(array('hidden'), array());
	$map->baseurl = 'https://'.SUNSITE_DOMAIN;
	$map->langid = 1;
	$map->gzip = false;

	if (!file_exists('/var/www/vm.ua/cache/sitemaps')) {
	    mkdir('/var/www/vm.ua/cache/sitemaps', 0777, true);
	}

  $map->gensecSitemap($allpages->childs());  
  $map->saveSitemap();    
  $map->ind++;  
  $map->genAllArtsSitemap();
  $map->saveSitemap();
  $map->ind++;
  $map->genAllFiltersSitemap();
  $map->saveSitemap();
  $map->ind++;
  $map->genPrinterSitemap();
  $map->saveSitemap();
  $map->ind++;
  $map->genBrandSitemap();   
  
	/*
	������ ���������� ������ ������
	$db->query("SELECT absnum FROM news WHERE 1");
	while($db->next_record()){
		$map->add(array(
			'loc'		=> '/news/'.$db->f('absnum').'.html',
			'changefreq'=> 'daily',
			'priority'	=> $map->setPriority(1, FALSE, TRUE)
		));
	}
	*/

	$map->buildSitemapsIndex();
  	echo "<br />OK-".$map->totalcnt;
?>
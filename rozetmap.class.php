<?php


class Rozetmap {

    // Глобальные переменные и константы
    var $sitemaps_path = '/cache/sitemaps/';
    var $sitemaps_limit = 50000;
    var $gzip = true;
    var $gzip_compression = 5;
  	var $exclude_absnum = array();
  	var $exclude_type = array();
  	var $level = 1;
  	var $totalcnt = 0;
  	var $ind = 0;
  	var $totall = 0;
  	var $langid = 1;
  	var $data = array();
  	var $baseurl = '';
  	var $nametype = 'main';
  	var $upPriorityPageType = array();
    var $artParents = array();
    
    
    var $rashod = array(
        3280=>1,
        3281=>1,
        3282=>1,
        3283=>1,
        3284=>1,
        3286=>1,
        3287=>1,
        3291=>1,
        3292=>1,
        3293=>1,
        3294=>1,
        3295=>1,
        3296=>1,
        3298=>1,
        3708=>1,        
    );
    
    // 
    function Rozetmap(){

    }
       
//     SELECT * 
//     FROM  `articles` a, articles_roz b
//     WHERE a.axapta_code = b.axapta_code
//     AND a.langid =1 
//     ORDER BY a.category
//     LIMIT 0 , 30


//         SELECT  distinctrow c.absnum, c.numsup, c.name 
//         FROM `articles` a, articles_roz b, pages c 
//         WHERE a.axapta_code=b.axapta_code AND c.langid=1 AND c.absnum=a.category 
//         ORDER BY a.category
//         
    

  	function add($val = array()){
  		$this->totall++;
      	$this->data[$val['loc']] = $val;
          if($this->totall % $this->sitemaps_limit == 0){
          	$this->saveSitemap();
  			$this->ind++;
  		}
  	}
    
    function getheader () {
        $out='<?xml version="1.0" encoding="utf-8"?'.'>'."\n".
             '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">'."\n".
             '<yml_catalog date="'.date('Y-m-d H:i').'">'."\n".
             '<shop>'."\n".
             '<name>BePrint</name>'."\n".
             '<company>ТОВ "В.М."</company>'."\n".
             '<url>'.$this->baseurl.'</url>'."\n".
             '<currencies>'."\n".
             '<currency id="UAH" rate="1"/>'."\n".
             '</currencies>'."\n";
        return $out;     
    }
    
    function getsecmap(){
    GLOBAL $db;
    GLOBAL $catl;
    
        $db->query("SELECT  distinctrow c.absnum, c.numsup, c.name 
             FROM `articles` a, articles_roz b, pages c 
             WHERE a.axapta_code=b.axapta_code AND c.langid=1 AND c.absnum=a.category AND a.approved=1 AND a.axapta_code<>'' AND b.avail=1 
             ORDER BY a.category");
        $out='';
        $pcat=array();
        while($db->next_record()){
           if (isset($catl[$db->f('absnum').'-1'])) {
              $out.='<category id="'.$db->f('absnum').'-1" parentId="'.$db->f('numsup').'">'.$db->f('name').' оригинальные</category>'."\n";
              unset($catl[$db->f('absnum').'-1']);   
           }
           if (isset($catl[$db->f('absnum').'-2'])) {
              $out.='<category id="'.$db->f('absnum').'-2" parentId="'.$db->f('numsup').'">'.$db->f('name').' совместимые</category>'."\n";
              unset($catl[$db->f('absnum').'-2']);  
           }
           if (isset($catl[$db->f('absnum')])) {              
              $out.='<category id="'.$db->f('absnum').'" parentId="'.$db->f('numsup').'">'.$db->f('name').'</category>'."\n";
              unset($catl[$db->f('absnum')]);              
           }
           /*if($db->f('absnum')==3316){ // разибавем "клавиатуры и наборы" на две категории
             $out.='<category id="3316-N" parentId="3316">Наборы</category>'."\n";  
             $out.='<category id="3316-K" parentId="3316">Клавиатуры</category>'."\n";  
            }*/

           $pcat[$db->f('numsup')]= $db->f('numsup');
        }
        foreach ($catl as $k=>$v) {
           $out.='<category id="'.$k.'" parentId="3314">'.$v.'</category>'."\n"; 


        }

        
        $db->query("SELECT  absnum, name 
             FROM `pages` 
             WHERE absnum in (".implode($pcat, ',').") AND langid=1");
        
        while($db->next_record()){
           if (isset($catl[$db->f('absnum').'-1'])) {
              $out = '<category id="'.$db->f('absnum').'-1">'.$db->f('name').' оригинальные</category>'."\n" . $out;   
           }
           if (isset($catl[$db->f('absnum').'-2'])) {
              $out = '<category id="'.$db->f('absnum').'-2">'.$db->f('name').' совместимые</category>'."\n" . $out;   
           }
           $out = '<category id="'.$db->f('absnum').'">'.$db->f('name').'</category>'."\n" . $out;           
           

        }     
        
        $out = "<categories>\n" . $out . "</categories>\n";  
		    return $out; 		
	  }
    
    function getoffermap(){
    GLOBAL $db;
    GLOBAL $catl;
    
//         $arts = $db->query("(SELECT *, b.avail as availroz 
//            FROM  `articles` a, articles_roz b, cont_2130 c
//            WHERE a.axapta_code = b.axapta_code
//            AND a.langid =1
//            AND a.avail = 1  
//            AND a.category not in ('3694')         
//            AND c.absnum=a.absnum
//            AND c.model=1
//            AND c.langid=1 
//            AND c.price_group=1
//            AND c.currency=1 
//            AND c.amount>0)
//            ORDER BY a.category
//            ");
        
         $arts = $db->query("(SELECT * FROM 
            (SELECT distinct ar.axapta_code, ar.roz_title, ar.avail as availroz, a.absnum, a.category, a.alias, a.avail, ar.src, a.body
            FROM articles_roz ar
            LEFT JOIN articles a using(axapta_code)
            WHERE ar.src=1 AND a.avail=1 AND a.langid=1 AND a.category not in ('3694')) at 
            LEFT JOIN cont_2130 c using (absnum) 
            WHERE c.model=1 AND c.langid=1 AND c.price_group=1 AND c.currency=1)
            UNION
            (SELECT * FROM 
            (SELECT distinct ar.axapta_code, ar.roz_title, ar.avail as availroz, a.absnum, a.category, a.alias, a.avail, ar.src, a.body
            FROM articles_roz ar
            LEFT JOIN articles a using(axapta_code)
            WHERE ar.src=2 AND a.avail=1 AND a.langid=1 AND a.category not in ('3694')) at 
            LEFT JOIN cont_2130 c using (absnum) 
            WHERE c.model=15 AND c.langid=1 AND c.price_group=15 AND c.currency=1)
            ORDER BY category");
        
        
        $out='';
        
        // Очистка логов
        try {
//           unlink('/var/www/vm.ua/exec/no-vendor.txt');
//           unlink('/var/www/vm.ua/exec/no-params.txt');
//             unlink('/var/www/vm.ua/exec/no-images.txt');
//           unlink('/var/www/vm.ua/exec/no-comp.txt');
        } catch (Exception $e) {
        }
         
        
        while($a=mysql_fetch_assoc($arts)) {

           // $a=$arts->Record;           
           $a = sunsite('make_article',$a,ART_GALLERY);

           // print_r($a); die();
           // file_put_contents('/var/www/vm.ua/rrrooz.txt', $a['axapta_code'].'=>'.$a['roz_title']."\n", FILE_APPEND);
           // Получение списка параметров
           
           $parms = mysql_query("SELECT ap.*,apn.name as prop_name,avn.name value_name 
               FROM axapta_props ap 
               LEFT JOIN axapta_props_names apn ON ap.prop_id=apn.prop_id and apn.langid='1' 
               LEFT JOIN axapta_values_names avn ON ap.value_id=avn.value_id and avn.langid='1' 
               WHERE ap.attribute='1' and ap.absnum='".$a['absnum']."' and apn.name is not NULL and avn.name is not NULL");              
           $par='';
           $vendor='';
           $original='';
           $tovtype='';    
           $tov_type=''; 
           while ($b=mysql_fetch_assoc($parms)) {

               $par.='<param name="'.$b['prop_name'].'">'.$b['value_name'].'</param>'."\n";
               if ($b['prop_id']=='АТ000001') $vendor=$b['value_name'];
               // Оригинальный/совместимый 
               if ($b['prop_id']=='АТ000013') $original=($b['value_name']=='Оригинальный' ? '-1' : '-2'); 
               // Тип товара 
               if ($b['prop_id']=='АТ001098') $tovtype=$b['value_name']; 
               
               if($a['category']==3316){
                  //if ($b['prop_id']=='АТ001098') $tov_type=($b['value_name']=='Комплект' ? '-K' : '-N'); 
                  if ($b['prop_id']=='АТ001098') $original=($b['value_name']=='Комплект' ? '1' : '2'); 
               }
           }  
           // если цена меньше 100грн
           // или Вебкамеры Logitech
           if((int)$a['amount']<=100 || $vendor=='Logitech' && $a['category']==6323){
            $par.='<param name="Доставка/Оплата">100% предоплата</param>'."\n";
           }
           //<vendor>Logitech</vendor>
           
           // Перенос в другую категорию кабелей, патчкордов и т.д
           if ($a['category']==6642) {
               $original='-'.(int)substr(md5($tovtype), 0, 3);
           }
           
           if ($vendor=='') { 
              //file_put_contents('/var/www/vm.ua/exec/no-vendor.txt',$this->baseurl.'/'.$a['alias'].'.html'."\n", FILE_APPEND);             
           }
           
           // Проверка совместимости
           if ($original=='' && $this->rashod[$a['category']]) {           
              //file_put_contents('/var/www/vm.ua/exec/no-comp.txt',$this->baseurl.'/'.$a['alias'].'.html'."\n", FILE_APPEND);             
           } 
           
           // Совместимость
           $comp = mysql_query ("SELECT DISTINCT c.connection_type, a.*
                FROM axapta_compability c 
                LEFT JOIN articles a ON a.absnum = c.child_absnum and a.langid =1 
               WHERE 
                  c.direct=1 and
                  c.connection_type='/P' and                   
                  c.absnum='".$a['absnum']."' and
                  !(a.category=3286 && a.axapta_code like 'I-PN-%')");
                  
           $com=array();       
           while ($b=mysql_fetch_assoc($comp)) {
               $com[] = $b['axapta_alias'];
           }
            // echo count($com);
           if (count($com)>0) { 
               $par .= '<param name="Подходит к">'.implode($com, ', ').'</param>'."\n";
           }
                  
             
           $descr = preg_replace('/(\\{title\\})/iu', $a['roz_title'], $a['body']);
           
           
           // Удаление ссылок           
           $count = null;
           $descr = preg_replace('/<a href=\"(http:|https:).*\"\>/i', '', $descr, -1, $count);
           $count = null;
           if ($a['availroz']==1) $catl[$a['category'].$original]=$tovtype;
           // $descr = preg_replace('/(<h(1|2|3|4)(\/*)>|<span.*>)/isU', $descr, -1, $count);
           
           // $descr = preg_replace('/(\\{.+?\\})/iu', ' ', $descr);          
           // print_r($a); 
          /* if($a['category']==3316){
           echo $a['category'].$original.'<br>';
           }*/
           //$a['amount'] = round((int)$a['amount'] - (int)$a['amount']*0.1);
           $out.=
           '<offer id="'.$a['absnum'].'" available="'.($a['availroz']==1?'true':'false').'">'."\n".
           '<currencyId>UAH</currencyId>'."\n".
           '<price>'.$a['amount'].'</price>'."\n";

           $pr = $db->qrows("SELECT * FROM `cont_2130` WHERE absnum = '" . $a['absnum'] . "' AND price_group = 14 AND currency = 1 AND langid = 1");
           if(isset($pr[0]['amount'])){
            $out.= '<price_promo>'.$pr[0]['amount'].'</price_promo>'."\n";
           }

           $out.='<categoryId>'.$a['category'].$original.$tov_type.'</categoryId>'."\n".
           '<url>'.$this->baseurl.'/'.$a['alias'].'.html</url>'."\n".
           '<name>'.$a['roz_title'].'</name>'. "\n".           
           '<stock_quantity>'.($a['availroz']==1?(int)rand(5, 15):0).'</stock_quantity>'."\n";
           
           if ($descr) {
           $out .= '<description><![CDATA['.$descr.']]></description>'."\n";
           }
           if ($vendor) {
           $out .= '<vendor>'.$vendor.'</vendor>'."\n";
           }
           if ($par=='') { 
            file_put_contents('/var/www/vm.ua/exec/no-params.txt',$this->baseurl.'/'.$a['alias'].'.html'."\n", FILE_APPEND);             
           }
            
           $out .= $par;
           $pic = '';
           // Изображение по умобчанию
           $picma = '/img/article/'.substr($a['absnum'],0,3).'/'.substr($a['absnum'],-2).'_main.jpg';
           if (file_exists('/var/www/vm.ua'.$picma)) {
              $o=getimagesize('/var/www/vm.ua'.$picma);
              if ($o[0]>=400 && $o[1]>=400) $pic='<picture>'.$this->baseurl.$picma.'</picture>'."\n";
           }
           
           // Отдельный случай для изображений товаров для ПРОТЕ 
           if ($a['src']==2) {
                $gallery=array();
              	$q = $db->exquery("SELECT * FROM articles_gallery WHERE parent = '".$a['absnum']."' AND dest & 2 = 2 ORDER BY position");
            		while($res = $q->nextAssoc()):
            			
            			$gallery[$res['absnum']] = sunsite('get_gal_file', 'gallery', $res['parent'], $res['absnum']);
            			foreach($gallery[$res['absnum']] as $key=> $osrc)
            				if($osrc != '' && !$gallery[$res['absnum']]['prw_src']) {
            					$gallery[$res['absnum']]['size'][$key] = getimagesize($_SERVER['DOCUMENT_ROOT'].$osrc);
            				}
            			$gallery[$res['absnum']]['descr'] = $db->qsingle("SELECT gallery_descr FROM articles_gallery_descr WHERE langid='".$langid."' AND absnum = '".$res['absnum']."'");
            		endwhile;
            		$a['gallery'] = $gallery;
           }
           //             
                     
           if ($a['gallery']) {     
             foreach ($a['gallery'] as $key=>$val) {
                 $pic.='<picture>'.$this->baseurl.$val['main'].'</picture>'."\n";           
             } 
           }
            
           if ($pic=='') { 
              file_put_contents('/var/www/vm.ua/exec/no-images.txt',$this->baseurl.'/'.$a['alias'].'.html'."\n", FILE_APPEND);
           }
           
           $out .= $pic;
           
           $out.='</offer>'."\n";           
        } 
        $out = "<offers>\n" . $out . "</offers>\n";
        return $out;
     }   
  
    function genartsSitemap($category = 0, $url = '', $level){  
      $level=3;
    	GLOBAL $db;
      GLOBAL $bad_links;
      GLOBAL $surl_categories;
        $db->query("SELECT a.alias, a.absnum, a.redirect, a.category FROM articles a
        JOIN cont_2130 b on a.absnum = b.absnum AND b.price_group=1 AND b.currency=1 AND b.langid=a.langid AND b.amount>0   
        WHERE a.approved = 1 AND a.category = '".intval($category)."' AND a.langid = '".$this->langid."'");
		while($db->next_record()){
			if(trim($db->f('redirect')) && substr($db->f('redirect'), 0, 1) != '/') continue;
     
      if ($db->f('redirect'))
          $href = $db->f('redirect');
      elseif (in_array($category, $surl_categories))  // Номера разделов для товаров которых включены "короткие" УРЛ              
          $href = '/' . ($langid==2 ? 'ukr/' : '') . $db->f('alias') . '.html';          
      else     
			    $href = $url . ($db->f('alias') ? $db->f('alias') : $db->f('absnum')) . '.html';

      // Нет ли в бедлинксе      
      $r=$db->exquery("SELECT * from `badlinks` WHERE `link`='//". $_SERVER['SERVER_NAME'] . $href . "'");      
      if (!$r->nextAssoc()) {
          echo 'https://patronservice.ua'.$href, "\n";
          continue;
      }
      
      
//       if (!in_array('http://patronservice.ua'.$href, $bad_links)===FALSE) {
//          echo 'http://patronservice.ua'.$href, "\n";
//          continue;
//       }

      // Для статей-товаров - один приоритет, для просто статей - другой       /va
      $noprodart=(strpos($href, '/vmshop')===false);
      $this->add(array(
				'loc'		=> $href,
				'changefreq'=> 'monthly',  // изменил daily на monthly    13.04.2016 14:44:38
				'priority'	=> $this->setPriority($level, FALSE, $noprodart) //TRUE)
			));
      
      // украинская версия страницы
//       $uhref=str_replace('patronservice.ua/', 'patronservice.ua/ukr/', $href);
//       $this->add(array(
// 				'loc'		=> $uhref,
// 				'changefreq'=> 'monthly',  // изменил daily на monthly    13.04.2016 14:44:38
// 				'priority'	=> $this->setPriority($level, FALSE, $noprodart) //TRUE)
// 			));
//       
      // Укр версия
      $this->add(array(
				'loc'		=> '/ukr'.$href,
				'changefreq'=> 'monthly',  // изменил daily на monthly    13.04.2016 14:44:38
				'priority'	=> $this->setPriority($level, FALSE, $noprodart) //TRUE)
			));
		}
	}
  
    function genAllArtsSitemap()
    { // BEGIN function genAllArtsSitemap
       array_walk($this->artParents, function ($val, $key) {
          $this->genartsSitemap($val['absnum'], $val['url'], $val['level']); 
       }); 
    	
    } // END function genAllArtsSitemap
    
    
    
    // Сохранение сайтмапа в файл
    function saveSitemap() {

        $xml = '';
		reset($this->data);
		while(list($k, $v) = each($this->data)){
			if(substr($v['loc'], 0, 1) == '/') $v['loc'] = $this->baseurl.$v['loc'];
            $xml .= '
					<url>
                        <loc>'.$v['loc'].'</loc>
                        <changefreq>'.$v['changefreq'].'</changefreq>
                        <priority>'.$v['priority'].'</priority>
                     </url>';
			$this->totalcnt++;
        }
        if($xml != '') $this->saveSitemapFile($this->ind, $xml);
		unset($this->data); $this->data = array();
    }
    
    function saveSitemapFile($num, $xml){
        $pre_xml = '<?xml version="1.0" encoding="UTF-8"?>
                        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $post_xml = '</urlset>';
        $this->saveFile($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path.str_replace(".","",$this->nametype).'.'.intval($num/10).'.'.intval($num%10).'.xml', $pre_xml.$xml.$post_xml);
    }
    function buildSitemapsIndex() {
		$this->saveSitemap();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $files = array();
        if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path)){
            while(false !== ($entry = readdir($handle))){
                if ($entry != "." && $entry != ".." && $entry != 'index.xml.gz' && $entry != 'index.xml') {
                    $files[$entry] = filemtime($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path.$entry)>0?filemtime($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path.$entry):filectime($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path.$entry);
                }
            }
            closedir($handle);
        }
        ksort($files);
        foreach($files as $fname => $ftime) {
            $xml .= '<sitemap>';
            $xml .= '<loc>'.$this->baseurl.$this->sitemaps_path.$fname.'</loc>';
            $xml .= '<lastmod>'.date("c", $ftime).'</lastmod>';
            $xml .= '</sitemap>';
        }        
        
        $xml .= '</sitemapindex>';
        $this->saveFile($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path.'index.xml', $xml);
        // Сохраняем сразу в сайтмап!!! 
        $this->saveFile($_SERVER['DOCUMENT_ROOT'].'/sitemap.xml', $xml);
    }
    
    function clearSitemaps() {
         if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    @unlink($_SERVER['DOCUMENT_ROOT'].$this->sitemaps_path.$entry);
                }
            }
         }
    }
    
    function saveFile($filename, $data) {
        if($this->gzip) $filename = $filename.'.gz';
        @unlink($filename);
        if($this->gzip) {
            $gz = gzopen($filename,'w'.$this->gzip_compression);
            gzwrite($gz, $data);
            gzclose($gz);
        } else {
            file_put_contents($filename, $data);
        }
        chmod($filename, 0777);
    }
}
?>
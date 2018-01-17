<?php 

// get site domain name
function getDomainName()
{
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
	{
	    $protocol  = "https://";
	}
	else
	{
	    $protocol  = "http://";
	}
	
	return $protocol . $_SERVER['HTTP_HOST'];	
}

// folder list
$commonDir = __DIR__.'/common/';
$categoryDir = __DIR__.'/category/';
$pageDir = __DIR__.'/page/';
$postDir = __DIR__.'/post/';
$product_categoryDir = __DIR__.'/product_category/';
$productDir = __DIR__.'/product/';
$tagDir = __DIR__.'/tag/';

$dirArr = [$commonDir, $categoryDir, $pageDir, $postDir, $product_categoryDir, $productDir, $tagDir];

// get all files
foreach ($dirArr as $key => $value)
{
	$fld = explode('/', $value);
	array_pop($fld);
	$fld = array_pop($fld);
	$files[$fld] = array_diff(scandir($value), array('.', '..'));
}

$xmlRows = '';
foreach ($files as $key => $file)
{
	foreach ($file as $keyf => $value) {
		$xmlRows .='
			<sitemap>
			<loc>'.getDomainName().'/sitemap/'.$key.'/'.$value.'</loc>
			<lastmod>'.date("Y-m-d\TH:i:s+00:00").'</lastmod>
			</sitemap>'."\r\n";
	}
}

$xmlRowsStart = '<?xml version="1.0" encoding="UTF-8"?>';
$xmlRowsStart .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\r\n";

$xmlRowsEnd = '</sitemapindex>';

$xmlData = $xmlRowsStart . $xmlRows . $xmlRowsEnd;

$file_name = 'sitemap.xml';
$one_file = fopen($file_name,"w");
fwrite($one_file,$xmlData);
fclose($one_file);

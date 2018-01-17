<?php 
require( dirname( __FILE__ ) . '/../wp-blog-header.php' );

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

// clear folder from old files
function delete_files($target)
{
    if(is_dir($target))
    {
        $files = glob( $target . '*', GLOB_MARK );
        
        foreach( $files as $file )
        {
            delete_files( $file );      
        }
      
        rmdir( $target );
    }
    elseif(is_file($target))
    {
        unlink( $target );  
    }
}
delete_files(__DIR__.'/category/');
mkdir(__DIR__.'/category/', 0777, true);

// save data to mxl file
function saveXmlSitemap($xmlRows, $j)
{
	$_fnc = ($j > 1) ? '-'.$j : '';
	$file_name = 'category/categories'.$_fnc.'.xml';
	$one_file = fopen($file_name,"w");
	fwrite($one_file,$xmlRows);
	fclose($one_file);
}

$s_map_start = '<?xml version="1.0" encoding="UTF-8"?>
<urlset
xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\r\n";

$s_map_end = '</urlset>';

// ***********************************************************************

// get categories: http://enhelbeauty.loc/categories.xml
// http://enhelbeauty.loc/category/press
$sql = "SELECT {$wpdb->prefix}terms.slug, {$wpdb->prefix}term_taxonomy.parent FROM {$wpdb->prefix}term_taxonomy LEFT JOIN {$wpdb->prefix}terms ON {$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id WHERE {$wpdb->prefix}term_taxonomy.taxonomy = 'category' ORDER BY {$wpdb->prefix}terms.slug";

$result = $wpdb->get_results($sql);

$resultCount = count($result);

for($i=0, $j=0, $s_map=''; $i<$resultCount; $i++)
{ 
	// devide links by 500 rows per XML file
	if( ($i>0) && ($i%500) == 0 )
	{
		++$j;
		saveXmlSitemap($s_map_start . $s_map . $s_map_end, $j);
		$s_map = '';
	}

	$s_map .= '
	<url>
	<loc>'.getDomainName().'/category/'.$result[$i]->slug.'</loc>
	<lastmod>'.date("Y-m-d\TH:i:s+00:00").'</lastmod>
	<changefreq>hourly</changefreq>
	<priority>0.8</priority>
	</url>'."\r\n";
}

// save last XML rows
saveXmlSitemap($s_map_start . $s_map . $s_map_end, ++$j);

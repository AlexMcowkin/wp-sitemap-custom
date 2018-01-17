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
delete_files(__DIR__.'/tag/');
mkdir(__DIR__.'/tag/', 0777, true);

// save data to mxl file
function saveXmlSitemap($xmlRows, $j)
{
	$_fnc = ($j > 1) ? '-'.$j : '';
	$file_name = 'tag/tags'.$_fnc.'.xml';
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

// get tags: http://enhelbeauty.loc/tags.xml
// http://enhelbeauty.loc/tag/4789
$sql = "SELECT {$wpdb->prefix}terms.name FROM {$wpdb->prefix}term_taxonomy LEFT JOIN {$wpdb->prefix}terms ON {$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id WHERE {$wpdb->prefix}term_taxonomy.taxonomy = 'post_tag' ORDER BY {$wpdb->prefix}terms.name";

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
	<loc>'.getDomainName().'/tag/'.$result[$i]->name.'</loc>
	<lastmod>'.date("Y-m-d\TH:i:s+00:00").'</lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5</priority>
	</url>'."\r\n";
}

// save last XML rows
saveXmlSitemap($s_map_start . $s_map . $s_map_end, ++$j);

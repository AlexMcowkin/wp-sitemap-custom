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
delete_files(__DIR__.'/post/');
mkdir(__DIR__.'/post/', 0777, true);

// save data to mxl file
function saveXmlSitemap($xmlRows, $j)
{
	$_fnc = ($j > 1) ? '-'.$j : '';
	$file_name = 'post/posts'.$_fnc.'.xml';
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

// get post: http://enhelbeauty.loc/pages.xml
// http://enhelbeauty.loc/o-nas
$sql = "SELECT id, post_name, post_modified, post_parent FROM {$wpdb->prefix}posts WHERE post_type = 'post' AND post_status='publish' ORDER BY id";

$result = $wpdb->get_results($sql);

// make new array
foreach ($result as $key => $value)
{
	$arr[$value->id] = ['pid'=>$value->post_parent, 'name'=>$value->post_name];
}

// create links
foreach ($arr as $key => $value)
{
	$links[] = recursGetParentUrl($value["pid"], $arr) . $value["name"];
}

function recursGetParentUrl($id, $arr)
{
	if(!$arr[$id]) {return '';}
	return $arr[$id]['name'] .'/'. recursGetParentUrl($arr[$id]['pid'], $arr);
}

$resultCount = count($links);

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
	<loc>'.getDomainName().'/'.$links[$i].'</loc>
	<lastmod>'.date("Y-m-d\TH:i:s+00:00").'</lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.5</priority>
	</url>'."\r\n";
}

// save last XML rows
saveXmlSitemap($s_map_start . $s_map . $s_map_end, ++$j);

<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2003 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id$

if (!defined('AT_INCLUDE_PATH')) { exit; }

function print_organizations($parent_id,
							 &$_menu, 
							 $depth, 
							 $path='',
							 $children,
							 &$string) {
	
	global $html_template, $zipfile, $resources, $ims_template_xml, $parser, $my_files;
	global $used_glossary_terms, $course_id, $course_language_charset, $course_language_code;
	static $paths, $zipped_files;

	$space  = '    ';
	$prefix = '                    ';

	if ($depth == 0) {
		$string .= '<ul>';
	}
	$top_level = $_menu[$parent_id];
	if (!is_array($paths)) {
		$paths = array();
	}
	if (!is_array($zipped_files)) {
		$zipped_files = array();
	}
	if ( is_array($top_level) ) {
		$counter = 1;
		$num_items = count($top_level);
		foreach ($top_level as $garbage => $content) {

			$link = '';
				
			if (is_array($temp_path)) {
				$this = current($temp_path);
			}
			if ($content['content_path']) {
				$content['content_path'] .= '/';
			}

			$link = $prevfix.'<item identifier="MANIFEST01_ITEM'.$content['content_id'].'" identifierref="MANIFEST01_RESOURCE'.$content['content_id'].'" parameters="">'."\n";
			$html_link = '<a href="resources/'.$content['content_path'].$content['content_id'].'.html" target="body">'.$content['title'].'</a>';
			
			/* save the content as HTML files */
			/* @See: include/lib/format_content.inc.php */
			$content['text'] = str_replace('CONTENT_DIR/', '', $content['text']);

			/* get all the glossary terms used */
			$terms = find_terms($content['text']);
			if (is_array($terms)) {
				foreach ($terms[2] as $term) {
					$used_glossary_terms[] = $term;
				}
			}
			global $glossary;
			/* calculate how deep this page is: */
			$path = '../';
			if ($content['content_path']) {
				$depth = substr_count($content['content_path'], '/');

				$path .= str_repeat('../', $depth);
			}
			$content['text'] = format_content($content['text'], $content['formatting'], $glossary, $path);

			/* add HTML header and footers to the files */

			$content['text'] = str_replace(	array('{TITLE}',	'{CONTENT}', '{KEYWORDS}', '{COURSE_PRIMARY_LANGUAGE_CHARSET}', '{COURSE_PRIMARY_LANGUAGE_CODE}'),
									array($content['title'],	$content['text'], $content['keywords'], $course_language_charset, $course_language_code),
									$html_template);

			/* duplicate the paths in the content_path field in the zip file */
			if ($content['content_path']) {
				if (!in_array($content['content_path'], $paths)) {
					$zipfile->create_dir('resources/'.$content['content_path'], time());
					$paths[] = $content['content_path'];
				}
			}

			$zipfile->add_file($content['text'], 'resources/'.$content['content_path'].$content['content_id'].'.html', $content['u_ts']);
			$content['title'] = htmlspecialchars($content['title']);

			/* add the resource dependancies */
			$my_files = array();
			$content_files = "\n";
			$parser->parse($content['text']);

			foreach ($my_files as $file) {
				/* filter out full urls */
				$url_parts = @parse_url($file);
				if (isset($url_parts['scheme'])) {
					continue;
				}

				/* file should be relative to content. let's double check */
				if ((substr($file, 0, 1) == '/') || ( strpos($file, '..') !== false) ) {
					continue;
				}

				$file_path = realpath('../../content/' . $course_id . '/' . $content['content_path'] . $file);

				/* check if this file exists in the content dir, if not don't include it */
				if (file_exists($file_path) && 	is_file($file_path) && !in_array($file_path, $zipped_files)) {
					$zipped_files[] = $file_path;

					$dir = dirname($content['content_path'] . $file).'/';

					if (!in_array($dir, $paths)) {
						$zipfile->create_dir('resources/'.$dir, time());
						$paths[] = $dir;
					}

					$file_info = stat( $file_path );
					$zipfile->add_file(@file_get_contents($file_path), 'resources/' . $content['content_path'] . $file, $file_info['mtime']);

					$content_files .= str_replace('{FILE}', $content['content_path'] . $file, $ims_template_xml['file']);
				}
			}

			/******************************/
			$resources .= str_replace(	array('{CONTENT_ID}', '{PATH}', '{FILES}'),
										array($content['content_id'], $content['content_path'], $content_files),
										$ims_template_xml['resource']);


			for ($i=0; $i<$depth; $i++) {
				$link .= $space;
			}
			
			$title = $prefix.$space.'<title>'.$content['title'].'</title>';

			if ( is_array($_menu[$content['content_id']]) ) {
				/* has children */

				$html_link = '<li>'.$html_link.'<ul>';
				for ($i=0; $i<$depth; $i++) {
					if ($children[$i] == 1) {
						echo $space;
						//$html_link = $space.$html_link;
					} else {
						echo $space;
						//$html_link = $space.$html_link;
					}
				}

			} else {
				/* doesn't have children */

				$html_link = '<li>'.$html_link.'</li>';
				if ($counter == $num_items) {
					for ($i=0; $i<$depth; $i++) {
						if ($children[$i] == 1) {
							echo $space;
							//$html_link = $space.$html_link;
						} else {
							echo $space;
							//$html_link = $space.$html_link;
						}
					}
				} else {
					for ($i=0; $i<$depth; $i++) {
						echo $space;
						//$html_link = $space.$html_link;
					}
				}
				$title = $space.$title;
			}

			echo $prefix.$link;
			echo $title;
			echo "\n";

			$string .= $html_link."\n";

			$depth ++;
			print_organizations($content['content_id'],
								$_menu, 
								$depth, 
								$path.$counter.'.', 
								$children,
								$string);
			$depth--;

			$counter++;
			for ($i=0; $i<$depth; $i++) {
				echo $space;
			}
			echo $prefix.'</item>';
			echo "\n";
		}
		$string .= '</ul>';
		if ($depth > 0) {
			$string .= '</li>';
		}

	}
}



$ims_template_xml['header'] = '<?xml version="1.0" encoding="{COURSE_PRIMARY_LANGUAGE_CHARSET}"?>
<!--This is an ATutor SCORM 1.2 Content Package document-->
<!--Created from the ATutor Content Package Generator - http://www.atutor.ca-->
<manifest xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2" xmlns:imsmd="http://www.imsglobal.org/xsd/imsmd_rootv1p2p1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2" identifier="MANIFEST-'.md5(time()).'" xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">
	<metadata>
		<schema>ADL SCORM</schema> 
  	    <schemaversion>1.2</schemaversion>
		<imsmd:lom>
		  <imsmd:general>
			<imsmd:title>
			  <imsmd:langstring xml:lang="{COURSE_PRIMARY_LANGUAGE_CODE}">{COURSE_TITLE}</imsmd:langstring>
			</imsmd:title>
			<imsmd:description>
			  <imsmd:langstring xml:lang="{COURSE_PRIMARY_LANGUAGE_CODE}">{COURSE_DESCRIPTION}</imsmd:langstring>
			</imsmd:description>
		  </imsmd:general>
		  <imsmd:lifecycle>
			<imsmd:contribute>
			  <imsmd:role>
			    <imsmd:source>
			      <imsmd:langstring xml:lang="x-none">LOMv1.0</imsmd:langstring> 
			    </imsmd:source>
			    <imsmd:value>
			      <imsmd:langstring xml:lang="x-none">Author</imsmd:langstring> 
			    </imsmd:value>
			  </imsmd:role>
			  <imsmd:centity>
			    <imsmd:vcard>{VCARD}</imsmd:vcard> 
			  </imsmd:centity>
			</imsmd:contribute>
		  </imsmd:lifecycle>
		  <imsmd:educational>
			<imsmd:learningresourcetype>
			  <imsmd:source>
				<imsmd:langstring xml:lang="x-none">ATutor</imsmd:langstring>
			  </imsmd:source>
			  <imsmd:value>
				<imsmd:langstring xml:lang="x-none">Content Module</imsmd:langstring>
			  </imsmd:value>
			</imsmd:learningresourcetype>
		  </imsmd:educational>
		  <imsmd:rights>
		  </imsmd:rights>
		</imsmd:lom>
	</metadata>
';

$ims_template_xml['resource'] = '		<resource identifier="MANIFEST01_RESOURCE{CONTENT_ID}" type="webcontent" href="resources/{PATH}{CONTENT_ID}.html"  adlcp:scormtype="asset">
			<metadata/>
			<file href="resources/{PATH}{CONTENT_ID}.html"/>{FILES}
		</resource>
'."\n";

$ims_template_xml['file'] = '			<file href="resources/{FILE}"/>'."\n";

$ims_template_xml['final'] = '
	<organizations default="MANIFEST01_ORG1">
		<organization identifier="MANIFEST01_ORG1" structure="hierarchical">
			<title>{COURSE_TITLE}</title>
{ORGANIZATIONS}
		</organization>
	</organizations>
	<resources>
{RESOURCES}
	</resources>
</manifest>';

$html_template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{COURSE_PRIMARY_LANGUAGE_CODE}" lang="{COURSE_PRIMARY_LANGUAGE_CODE}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={COURSE_PRIMARY_LANGUAGE_CHARSET}" />
	<style type="text/css">
	body { font-family: Verdana, Arial, Helvetica, sans-serif;}
	a.at-term {	font-style: italic; }
	</style>
	<title>{TITLE}</title>
	<meta name="Generator" content="ATutor">
	<meta name="Keywords" content="{KEYWORDS}">
</head>
<body>{CONTENT}</body>
</html>';



//output this as header.html
$html_mainheader = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{COURSE_PRIMARY_LANGUAGE_CODE}" lang="{COURSE_PRIMARY_LANGUAGE_CODE}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={COURSE_PRIMARY_LANGUAGE_CHARSET}" />
	<link rel="stylesheet" type="text/css" href="ims.css"/>
	<title>{COURSE_TITLE}</title>
</head>
<body class="headerbody"><h3>{COURSE_TITLE}</h3></body></html>';


$html_toc = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{COURSE_PRIMARY_LANGUAGE_CODE}" lang="{COURSE_PRIMARY_LANGUAGE_CODE}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={COURSE_PRIMARY_LANGUAGE_CHARSET}" />
	<link rel="stylesheet" type="text/css" href="ims.css" />
	<title></title>
</head>
<body>{TOC}</body></html>';

// index.html
$html_frame = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{COURSE_PRIMARY_LANGUAGE_CODE}" lang="{COURSE_PRIMARY_LANGUAGE_CODE}">
	<meta http-equiv="Content-Type" content="text/html; charset={COURSE_PRIMARY_LANGUAGE_CHARSET}" />
	<title>{COURSE_TITLE}</title>
</head>
<frameset rows="50,*,50">
<frame src="header.html" name="header" title="header" scrolling="no">
	<frameset cols="25%, *" frameborder="1" framespacing="3">
		<frame frameborder="2" marginwidth="0" marginheight="0" src="toc.html" name="frame" title="TOC">
		<frame frameborder="2" src="resources/{PATH}{FIRST_ID}.html" name="body" title="{COURSE_TITLE}">
	</frameset>
<frame src="footer.html" name="footer" title="footer" scrolling="no">
	<noframes>
		<h1>{COURSE_TITLE}</h1>
      <p><a href="toc.html">Table of Contents</a> | <a href="footer.html">About</a><br />
	  </p>
  </noframes>
</frameset>
</html>';



$glossary_xml = '<?xml version="1.0" encoding="{COURSE_PRIMARY_LANGUAGE_CHARSET}"?>
<!--This is an ATutor Glossary terms document-->
<!--Created from the ATutor Content Package Generator - http://www.atutor.ca-->

<!DOCTYPE glossary [
   <!ELEMENT item (term, definition)>
   <!ELEMENT term (#PCDATA)>
   <!ELEMENT definition (#PCDATA)>
]>

<glossary>
      {GLOSSARY_TERMS}
</glossary>
';

$glossary_term_xml = '	<item>
		<term>{TERM}</term>
		<definition>{DEFINITION}</definition>
	</item>';

$glossary_body_html = '<h2>Glossary</h2>
	<ul>
{BODY}
</ul>';

$glossary_term_html = '	<li><a name="{ENCODED_TERM}"></a><strong>{TERM}</strong><br />
		{DEFINITION}<br /><br /></li>';

?>
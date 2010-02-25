<?php
/*
Plugin Name: NextGEN Resize.
Plugin URI: http://designerfoo.com/nextgen-resize-wordpress-plugin-to-resize-nextgen-gallery-images#menunav
Description: A plugin mod to the ever popular plugin Nextgen gallery. This plugin resizes the images on the fly, as you upload them. This comes in handy if you or clients are uploading images that are oversize and hiRes.
Version: 1.3b
Author: Manoj Sachwani
Author URI: http://designerfoo.com
*/

/*  Copyright 2009-2010  Manoj Sachwani  (email : i@designerfoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
 //error_reporting(E_ALL); /*Notice marte salla*/
require_once( WP_PLUGIN_DIR . '/nextgen-resize/simpleimage.php');


if ( class_exists('nggLoader') ){
		
		register_activation_hook( __FILE__, 'resizeinstalls' );
		add_action('ngg_added_new_image','resizeuploadsngg',1);
		add_action('admin_menu', 'adminmenu');
		 if ( function_exists('register_uninstall_hook') ){
    register_uninstall_hook(__FILE__, 'resizeuninstalls');}

		//echo "Nextgen Found";
     }  
     else
     { echo "<div id=\"message\" class=\"updated fade\"><p><a href=\"http://wordpress.org/extend/plugins/nextgen-gallery/\" target=\"_blank\">NextGEN Gallery Plugin</a> Not found</p></div>";}
     
     
     /*function that will add a next-gen resize page under the gallery tab*/
    function adminmenu() {
		$file = __FILE__;
		
		
		
		$subpage = add_submenu_page('nextgen-gallery', "NextGen Resize Options", "NextGen Resize Options", 10, $file, 'options_panel');
		
	} //end of admin_menu()
	
	
	
	
   /*function that runs when the plugin is activated*/
   function resizeinstalls()
   {
   	 $nggresize_options['resizeby']="width";
   	 $nggresize_options['px']="550";
   	 $nggresize_options['on']="yes";
   	 $nggresize_options['pxh']="0";
	 add_option("nggresizeoptions", $nggresize_options, 'Options to use in the pane.', 'yes');
   }
   
   /*function that runs when the plugin is deactivated and deleted */
   function resizeuninstalls()
   {
   		if(get_option("nggresizeoptions"))
			{
				delete_option("nggresizeoptions");
				
			}
   }
   
   /*function that hooks on to ngg's ngg_added_new_image hook, gets the $image array and uses it to resize the image dynamically*/
   function resizeuploadsngg($image)
     {
     	global $wpdb;
     	$resize_nggallery						= $wpdb->prefix . 'ngg_gallery';
     	$query = "select * from $resize_nggallery where gid='".$image['galleryID']."'" ;
     	//print_r($image);
     	//echo ' line:'. __LINE__ .' file:'. __FILE__ .' directory:'. __DIR__ .' function:'. __FUNCTION__ .' class:'. __CLASS__ .' method:'. __METHOD__ .' namespace:'. __NAMESPACE__;
     	$results = $wpdb->get_results($query);
     	//$p = getcwd();
		//echo $p;

		$nggarray_options = get_option("nggresizeoptions");
		
     	//print_r($nggarray_options);
     	 
		
			if($nggarray_options['on']=="yes")
			{
				$imageres = new SimpleImage();				
				 $imageres->load(ABSPATH.$results[0]->path."/".$image['filename']);
				// echo ABSPATH.$results[0]->path."/".$image['filename'];
				if($nggarray_options['resizeby']=="width")
				{
					//echo "in_width";
	   				$imageres->resizeToWidth($nggarray_options['px']);
	   			}
   				elseif($nggarray_options['resizeby']=="wandh")
   				{
   					$imageres->resize($nggarray_options['px'],$nggarray_options['pxh']);
   				}
				elseif($nggarray_options['resizeby']=="height")
				{
						$imageres->resizeToHeight($nggarray_options['px']);
				}
   			
  				$resulted =	$imageres->save(ABSPATH.$results[0]->path."/".$image['filename']);
  			}
		
     			

     }
     
     /*function that will display the options page*/
	function options_panel() 
	{ 
		$message="";
		
		//print_r($nggarray_options);
		if(isset($_POST['rezsub']) && $_POST['rezsub'] == "Save Options")
		{
			$nonce = $_POST['nonce-nextgenresize'];
			if (!wp_verify_nonce($nonce, 'nextgenresize-nonce')) die ( 'Security Check - If you receive this in error, log out and back in to WordPress');
			$nggresize_options['resizeby']=$_POST['rezby'];
   	 		$nggresize_options['px']=$_POST['rezpix'];
   	 		$nggresize_options['pxh']=$_POST['rezpixh'];
   			$nggresize_options['on']=$_POST['rezon'];
   			
   			
			update_option("nggresizeoptions", $nggresize_options);
			$message = "NextGEN Resize options updated.";
			//print_r($nggresize_options);
		}
		
		//resize all images in the folder selected.
		if(isset($_POST['resizefoldersure']) && $_POST['resizefoldersure']=="1" && $_POST['folderresize']!="00")
		{
			$nonce = $_POST['nonce-nextgenresize'];
			if (!wp_verify_nonce($nonce, 'nextgenresize-nonce')) die ( 'Security Check - If you receive this in error, log out and back in to WordPress');
			$folderresize = $_POST['folderresize'];
			$folderresize = str_ireplace("wp-content/","",$folderresize);
			//echo $folderresize;
			$folderresize = WP_CONTENT_DIR."/".$folderresize;
			$nggarray_options = get_option("nggresizeoptions");
			//echo $folderresize;
			$handlerresize = opendir($folderresize);
			while($file = readdir($handlerresize))
			{
				
				if($file != "." && $file!=".." )
				{
					$extfile = substr($file,-3);
					//echo $folderresize."/".$file;
					if(is_file($folderresize."/".$file)==TRUE)
					{
						
						if($nggarray_options['on']=="yes" && ($extfile == "jpg" || $extfile == "gif" || "$extfile" == "png" || $extfile=="bmp"))
						{
							$imageres = new SimpleImage();				
							 @$imageres->load($folderresize."/".$file);
							
							//echo $nggarray_options['resizeby'];
							echo $nggarray_options['px'];
							if($nggarray_options['resizeby']=="width")
							{
								//echo "in_width";
				   				$imageres->resizeToWidth($nggarray_options['px']);
				   			}
			   				elseif($nggarray_options['resizeby']=="wandh")
			   				{
								//echo "in_width";
			   					$imageres->resize($nggarray_options['px'],$nggarray_options['pxh']);
			   				}
							elseif($nggarray_options['resizeby']=="height")
							{
									$imageres->resizeToHeight($nggarray_options['px']);
							}

			  				$resulted =	@$imageres->save($folderresize."/".$file);
			  			}
					}
					
				}
			}
			closedir($handlerresize);
			$message="Gallery pictures resized!";
			
		}
	//update_option("em_timezone",$_POST['em_timezone']); <?php if(preg_match('/none/',get_option("em_timezone"))=='1'){
	$nggarray_options = get_option("nggresizeoptions");
	?>
	<div class="wrap">
		<h2>Nextgen Resize - Control Panel</h2>
		<h4>A plugin to auto reduce the size and dimensions of the every image uploaded.</h4>
		<h4><a href="http://feeds.feedburner.com/Designerfoo" target="_blank">Subscribe to the RSS feed</a> or <a href="http://feedburner.google.com/fb/a/mailverify?uri=Designerfoo&loc=en_US" target="_blank">subscribe via Email</a>, to know what other updates/plugins/themes I am releasing</h4>
		<div style="position:relative;width:75%;float:left;clear:both;"><script type="text/javascript" src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US"></script><script type="text/javascript">FB.init("c1a9395ecd2e5d09a614d234a3cad356");</script><fb:fan profile_id="304857902799" stream="0" connections="0" logobar="0" width="300"></fb:fan><div style="font-size:8px; padding-left:10px"><a href="http://www.facebook.com/pages/NextGEN-Resize/304857902799">NextGEN Resize</a> on Facebook</div></div><br/><Br/><br/><br/>
		<?php if ($message) : ?>
			<div id="message" class="updated fade" style="clear:both;"><p><?php echo $message; ?></p></div>
		<?php endif; ?>
		<form name="rezitboy" method="post" action=""/>
		<div id="inputcontrols" style="clear:both;">
			
			<label for="resizeby_op">Resize Images?</label>&nbsp;
			<select name="rezon" id="rezon"><option value="yes" <?php if(preg_match('/yes/',$nggarray_options['on'])=='1') { ?> selected <?php } ?> >Yes</option><option value="no" <?php if(preg_match('/no/',$nggarray_options['on'])=='1') { ?> selected <?php } ?>>No</option></select><br/><br/>
			
			<label for="resizeby_op">Resize by</label>&nbsp;
			<select name="rezby" id="rezby" ><option value="width" <?php if(preg_match('/width/',$nggarray_options['resizeby'])=='1') { ?> selected <?php } ?> >Width</option><option value="height" <?php if(preg_match('/height/',$nggarray_options['resizeby'])=='1') { ?> selected <?php } ?>>Height</option><option value="wandh" <?php if(preg_match('/wandh/',$nggarray_options['resizeby'])=='1') { ?> selected <?php } ?>>Width &amp; Height</option></select><br/><br/>
			
			<label for="" id="widthpx"> Pixels</label>&nbsp;
			<input type="text" name="rezpix" id="rezpix" value="<?php echo $nggarray_options['px']; ?>"/>[Becomes the width value when the option = Height &amp; Width.]<br/><br/>
				<label for="">Height Pixels </label>&nbsp;
				<input type="text" name="rezpixh" id="rezpixh" value="<?php echo $nggarray_options['pxh']; ?>"/>[Only works if the Height &amp; Width option is selected above]<br/><br/>
			<input type="submit" name="rezsub" id="rezsub" value="Save Options"/>
			
		</div>
		<input type="hidden" name="nonce-nextgenresize" value="<?php echo wp_create_nonce('nextgenresize-nonce'); ?>" />
		</form><br/><br/>
		<form action="" method="post">
			<select name="folderresize" id="folderresize">
				<option value="00">Select One</option>
				<?php
				global $wpdb;
				$table_name = $wpdb->prefix."ngg_gallery";
				$sql = "select title, path from ".$table_name;
				$results_list = $wpdb->get_results($sql);
				foreach($results_list as $row)
				{
					?>
					<option value="<?php echo $row->path; ?>"><?php echo $row->title; ?></option>
					<?php
				}
				
				?>
			</select>&nbsp;&nbsp;
			<input type="submit" name="submit" value="Resize all images in this gallery.">
			<input type="hidden" name="resizefoldersure" id="resizefoldersure" value="1"/>
			<input type="hidden" name="nonce-nextgenresize" value="<?php echo wp_create_nonce('nextgenresize-nonce'); ?>" />
		</form>

	<?php }
?>

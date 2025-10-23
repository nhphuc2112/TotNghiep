<?php
/* 
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2024 ThemePunch
*/

if(!defined('ABSPATH')) exit();

class SrBeforeAfterFront extends RevSliderFunctions {

	private $slug;
	private $script_enqueued = false;

	public function __construct($slug) {
		global $SR_GLOBALS;
		$this->slug = $slug;
		if($this->get_val($SR_GLOBALS, 'front_version') === 7){
			add_filter('revslider_add_slider_base', array($this, 'enqueue_header_scripts'), 10, 1);
			add_filter('sr_get_full_slider_JSON', array($this, 'add_modal_scripts'), 10, 2);
			add_filter('revslider_export_html_file_inclusion', array($this, 'add_addon_files'), 10, 2);
		}
		if($this->get_val($_GET, 'page') === 'revslider'){
			add_action('admin_footer', array($this, 'add_header_scripts_return'));
		}
	}

	public function add_modal_scripts($obj, $slider){
		if(!$this->is_in_use($slider)) return $obj;
		$list = $this->get_script_list();
		if(empty($list)) return $obj;
		foreach($list ?? [] as $handle => $script){
			$obj['addOns'][$handle] = $script;
		}

		return $obj;
	}

	public function add_header_scripts($script){
		echo $this->add_header_scripts_return(false);
	}

	public function add_header_scripts_return($tags = ''){
		if($tags !== false){
			if($this->script_enqueued) return;
			$this->script_enqueued = true;
		}

		$list = $this->get_script_list();
		if(empty($list)) return '';

		$tab	= ($tags !== false) ? '' : '	';
		$nl		= (count($list) > 1 || $tags === false) ? "\n" : '';
		$html	= '';
		$html	.= ($tags !== false) ? "<script>".$nl : '';
		foreach($list ?? [] as $handle => $script){
			$html .= $tab.'SR7.E.resources.'.$handle.' = "'. $script .'";'.$nl;
		}
		$html	.= ($tags !== false) ? "</script>" . "\n" : '';

		if($tags === false) return $html;

		echo $html;
	}

	public function get_script_list(){
		$min = file_exists(RS_BEFOREAFTER_PLUGIN_PATH . 'public/js/' . $this->slug . '.js') ? '' : '.min';
		return array('beforeafter' => RS_BEFOREAFTER_PLUGIN_URL.'public/js/' . $this->slug . $min . '.js');
	}

	public function enqueue_header_scripts($slider){
		if($this->script_enqueued) return $slider;
		if(empty($slider)) return $slider;

		if($this->is_in_use($slider)) $this->enqueue_scripts();

		return $slider;
	}

	public function is_in_use($slider){
		if(empty($slider)) return false;
		
		// check if we are an v7 slider
		if($this->get_val($slider, array('settings', 'migrated'), false) !== false && $this->get_val($slider, array('settings', 'addOns', $this->slug, 'u')) === true) return true;
		if($this->get_val($slider, array('params', 'migrated'), false) !== false && $this->get_val($slider, array('params', 'addOns', $this->slug, 'u')) === true) return true;
		
		// check if we are v6
		if($this->get_val($slider, array('slider_params', 'addOns', 'revslider-'.$this->slug.'-addon', 'enable'), false) === true) return true;
		if($this->get_val($slider, array('params', 'addOns', 'revslider-'.$this->slug.'-addon', 'enable'), false) === true) return true;

		// check v7 if false is set
		if($this->get_val($slider, array('settings', 'migrated'), false) !== false && $this->get_val($slider, array('settings', 'addOns', $this->slug, 'u')) === false) return false;
		if($this->get_val($slider, array('params', 'migrated'), false) !== false && $this->get_val($slider, array('params', 'addOns', $this->slug, 'u')) === false) return false;

		// check v6 if false is set
		if($this->get_val($slider, array('slider_params', 'addOns', 'revslider-'.$this->slug.'-addon', 'enable'), 'unset') === false) return false;
		if($this->get_val($slider, array('params', 'addOns', 'revslider-'.$this->slug.'-addon', 'enable'), 'unset') === false) return false;

		//check if we are v6, and maybe some deeper element needs the addon		
		$json = json_encode($slider, true);
		$return = (strpos($json, 'revslider-'.$this->slug.'-addon') !== false) ? true : false;
		unset($json);

		return $return;
	}

	public function enqueue_scripts(){
		add_action('revslider_pre_add_js', array($this, 'add_header_scripts'));
		$this->script_enqueued = true;
		$min = file_exists(RS_BEFOREAFTER_PLUGIN_PATH . 'public/js/' . $this->slug . '.js') ? '' : '.min';
		wp_enqueue_script('revslider-'.$this->slug.'-addon', RS_BEFOREAFTER_PLUGIN_URL . "public/js/" . $this->slug . $min . ".js", '', RS_REVISION, array('strategy' => 'async'));

		global $SR_GLOBALS;
		if(!isset($SR_GLOBALS['fonts'])) $SR_GLOBALS['fonts'] = array();
		if(!isset($SR_GLOBALS['fonts']['queue'])) $SR_GLOBALS['fonts']['queue'] = array();
		$SR_GLOBALS['fonts']['queue']['FontAwesome'] = true;

	}

	public function add_addon_files($html, $export){
		$output = $export->slider_output;
		$addOn = $this->is_in_use($output->slider);
		if(empty($addOn)) return $html;

		$export_path_js = 'revslider-'.$this->slug.'-addon/public/js/';

		$list = $this->get_script_list();
		if(empty($list)) return '';
		
		foreach($list ?? [] as $handle => $script){
			$script_path = str_replace(RS_BEFOREAFTER_PLUGIN_URL, RS_BEFOREAFTER_PLUGIN_PATH, $script);
			if(!$export->usepcl){
				$export->zip->addFile($script_path,  $export_path_js . $handle . '.js');
			}else{
				$export->pclzip->add($script_path, PCLZIP_OPT_REMOVE_PATH, RS_BEFOREAFTER_PLUGIN_PATH.'public/js/', PCLZIP_OPT_ADD_PATH, $export_path_js);
			}
			$html = str_replace(array($script, str_replace('/', '\/', $script)), array($export_path_js . $handle . '.js', str_replace('/', '\/', $export_path_js . $handle . '.js')), $html);
		}
		
		return $html;
	}
	
	
}

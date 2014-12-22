<?php
	ini_set('error_reporting', E_ALL);
    ini_set("display_errors", 1); 
	/* 
	every file gets this line. nothing goes above it.
	*/
	require_once $_SERVER['DOCUMENT_ROOT'] . '/app/index.php';
	/* 
	all page specific stuff goes below here
	*/

	require_once $_SERVER['DOCUMENT_ROOT'].'/tests/apc_test/classes/apc.caching.php';
	$oCache = new CacheAPC();

class ProfileBase{
	
	function cache_it($myobject, $myname){
		global $oCache;
		if ($oCache->bEnabled) { // if APC enabled
				print "\n setting OBJECT ".$this->unique_identifier."_".$myname ."\n";
			 $oCache->setData($this->unique_identifier."_".$myname, $myobject);
		}
	}

	function get_cached($myname){
		global $oCache;
		if ($oCache->bEnabled) { // if APC enabled

			// check and see if the item is already in memory
			if($myobject = $oCache->getData($this->unique_identifier."_".$myname)){
				// if so , use it
				print "\n USING OBJECT ".$this->unique_identifier."_".$myname ."\n";
				return $myobject;

			} 
			// else!

		} else {
		    echo 'Seems APC not installed, please install it to perform tests';
		    exit;
		}
	}

	function set_prefix($pref){
		$this->unique_identifier = $pref;
	}
}

class TitleProfile extends ProfileBase{ 
	 
	/* 
		PAGE SPECIFIC STUFF 
	*/
	// store all the items to be passed into the template here;	
	function init(){  
		global $get, $server_vars, $session_vars, $props_arrays, $profile_id, $templateMgr;
	
		
		$options_array = array();
		$page = "profile";
		$options_array['page'] = $page;
		$profile_type = "title";

		$this->set_prefix($page."_".$profile_type."_".$profile_id);

		//used in JS & down below to determine what Mobile Needs
		$device_is_mobile = (Router::device_is_mobile())? 1 : 0;

		$options_array = array(
				'get'=>$get,			
				'server' => $server_vars,
				'session'=> $session_vars,  	
				'device_is_mobile' => $device_is_mobile
				);	

		if(!Router::device_is_mobile()){ 
			$options_array['props_arrays'] = $props_arrays;
		}
		
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/get_overview_'.$profile_type.'.php';

		//get unique number to bust the facebook cache for proposal sharing
		$rev = time();
		
		//get the ID of this profile
		if (isset($_GET['id'])) {
			$profile_id = clean_input_string($_GET['id']);
		}
		
		//get anchor value to jump directly to a proposal
		if (isset($_GET['anchor'])) {
			$anchor = clean_input_string($_GET['anchor']);
		}else{
			$anchor = "";
		}
		$options_array['anchor'] = $anchor;
		
		if (isset($_GET['full_summary'])) {
			$full_summary = clean_input_string($_GET['full_summary']);
		}else{
			$full_summary = "";
		}
		$options_array['full_summary'] = $full_summary; 
		
		if($profile_info = $this->get_cached('profile_info')){

		}else{ 
			$profile_info = getProfileInfo($profile_id); 
			/** CACHE **/
			$this->cache_it($profile_info, 'profile_info' );
		}

		$options_array['profile_info'] = $profile_info;
		
		$options_array['page_title'] = $profile_info['name'];
		
		if($options_array['profile_details'] = $this->get_cached('profile_details')){

		}else{ 
			$options_array['profile_details'] = getTitleDetails($profile_id); 
			/** CACHE **/
			$this->cache_it($options_array['profile_details'], 'profile_details' );
		} 

		if($options_array['story_status'] = $this->get_cached('story_status')){

		}else{ 
			$options_array['story_status'] = getStoryStatus(); 
			/** CACHE **/
			$this->cache_it($options_array['story_status'], 'story_status' );
		}
		// ??
		$num_proposals = 0;

		if($overview = $this->get_cached('overview')){

		}else{ 
			$overview = getOverview(@$profile_id, @$_SESSION['id'], @$profile_info['user_id'], @$_SESSION['is_admin'], @$_SESSION['pic']);		 
			/** CACHE **/
			$this->cache_it($overview, 'overview' );
		}

		$options_array['overview'] = $overview;
		
		$options_array['crew_names'] = $overview->crew_names;

		/* Mobile Doesnt Need This */
		if(!Router::device_is_mobile()){  
			/** VIDEOS **/	
			if($options_array['num_videos'] = $this->get_cached('num_videos')){

			}else{ 
				$options_array['num_videos'] = getVideoCount($profile_id); 
				/** CACHE **/
				$this->cache_it($options_array['num_videos'], 'num_videos' );
			}


			if ($options_array['num_videos'] > 0) { 
				if($options_array['videos'] = $this->get_cached('videos')){

				}else{ 
					// only get videos if there is more than 0
					$options_array['videos'] = getVideos($profile_id);
					/** CACHE **/
					$this->cache_it($options_array['videos'], 'videos' );
				}

			}
			else {
				$options_array['videos'] = array();
			}

			if($options_array['comments_count'] = $this->get_cached('comments_count')){

			}else{ 
				$options_array['comments_count'] = getCommentCount($profile_id);
				/** CACHE **/
				$this->cache_it($options_array['comments_count'], 'comments_count' );
			}


			if ($options_array['comments_count'] > 0) {
				if($options_array['comments_items'] = $this->get_cached('comments_items')){

				}else{ 
					$options_array['comments_items'] = getProfileCommentItems($profile_id, "title");
					/** CACHE **/
					$this->cache_it($options_array['comments_count'], 'comments_items' );
				}

			}
			else {
				$options_array['comments_items'] = array();
			}
		}

		//
		$role_names = $overview->role_names;
		$role_ids = $overview->role_ids;
		$role_pics = $overview->role_pics;

		/* 
		append to the header_vars_array, for use in JS 
		*/
		$header_vars_array['role_names'] = $role_names;
		$header_vars_array['role_ids'] = $role_ids;
		$header_vars_array['role_pics'] = $role_pics;

		$options_array['header_vars'] = $header_vars_array;

		//
		$like_proposal_id = "";
		$like_item_type = "";
		$dislike_proposal_id = "";
		$dislike_item_type = "";
		$favorite = "";
		$fav_profile_id = "";
		$fav_profile_type = "";
		$if_factor = ""; 

		/*
		Mobile Doesnt Need This: Fans
		*/
		if(!Router::device_is_mobile()){  
			if($fans_results = $this->get_cached('fans_results')){

			}else{ 
				$fans_results = getFans("title", $profile_id, @$_SESSION['friends_ids']);
				/** CACHE **/
				$this->cache_it($fans_results, 'fans_results' );
			}

			$options_array['fans'] = $fans_results['fans_array'];
			$options_array['fans_num_remaining'] = $fans_results['num_remaining'];
		}
		
		/*
		Mobile Doesnt Need This: Related Stories
		*/
		if(!Router::device_is_mobile()){  
			if($options_array['related_items'] = $this->get_cached('related_items')){

			}else{ 
				$options_array['related_items'] = get_related_for_title($profile_id, $profile_info['source_format_id'], $profile_info['genre_id'], $profile_info['creator']);				
				/** CACHE **/
				$this->cache_it($options_array['related_items'], 'related_items' );
			}
 
		}

		$options_array['profile_type'] = $profile_type; 
		$options_array['rev'] = $rev; 
		$options_array['profile_id'] = $profile_id; 
		$options_array['profile_type'] = $profile_type; 

		$templateMgr->render('/title/title_base.html', 	$options_array );
	}

}	

$t = new TitleProfile();
$t->init();

if(DEBUGGING) print $dbHandler->report_all();



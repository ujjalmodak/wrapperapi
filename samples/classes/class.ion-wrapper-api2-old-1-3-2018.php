<?php
namespace Wrapperapi;
require('classes/interface.ion-wrapper-api.php');
class Ion_wrapper_api implements Common_wrapper{

	var $baseurl;
	var $title;
	var $content;
	var $status;
	var $date;
	var $categories ;
	var $tags;
	var $image_url;
	var $alt_text;
	var $caption;
	var $description;
	var $authorname;
	var $emailid;	
	var $post_field 	= array();
	var $cat_id_arr 	= array();
	var $tag_id_arr		= array();
	var $author_id;
	var $featured_img_id;

	var $username;
	var $password;

	var $ret 			= '';

	public function __Construct($post_arr = NULL){

		$this->baseurl 		= rtrim($post_arr['baseurl'], '/\\');
		$this->title 		= trim($post_arr['title']);
		$this->content 		= ucwords((trim($post_arr['description'])));
		$this->categories 	= trim($post_arr['category']); // Comma seperated string
		$this->tags 		= trim($post_arr['tags']); // Comma seperated string
		$this->image_url 	= trim($post_arr['img_url']);
		$this->alt_text		= trim($post_arr['img_alt']);
		$this->caption 		= trim($post_arr['img_caption']);
		$this->description 	= trim($post_arr['img_description']);
		$this->authorname	= $post_arr['authorname'];
		$this->emailid		= $post_arr['emailid'];
		$this->status 		= $post_arr['status'];
		$this->date 		= $post_arr['customdatetime'];
		$this->username 	= $post_arr['username'];
		$this->password 	= $post_arr['userpassword'];

		
		



		if(!empty($this->categories)){			
			$this->__getCategoriesId($this->categories);			
		}

		if(!empty($this->tags)){			
			$this->__getTagsId($this->tags);			
		}

		if(!empty($this->authorname)){			
			$this->__getAuthorId($this->authorname);			
		}

		if(!empty($this->image_url)){			
			$this->__uploadMedia();			
		}

		$this->post_field 	= array(	'title' 			=> $this->title,
										'content' 			=> $this->content,
										'categories'		=> $this->cat_id_arr,
										'tags'				=> $this->tag_id_arr,
										'author'			=> $this->author_id,
										'featured_media'	=> $this->featured_img_id,
										'status'			=> $this->status,
										'date'				=> date('Y-m-d h:i:s', strtotime($this->date)),
										'date_gmt'			=> date('Y-m-d h:i:s', strtotime($this->date))
							  );
		



		//print_r(json_encode($this->post_field));		
		//print_r($this->post_field);
		//exit;
	}

	public function get_file_name($path){
		$url 				= $path;
		$break 				= explode('/', $url);
		$file 				= $break[count($break) - 1];		
		$random_unique_no 	= md5(time());
		$fa 				= explode('.',$file);
		$extension 			= end($fa);
		$name_only 			= current(explode('.',$file));
		
		switch ($extension) {
		    case "jpg":
		    case "jpeg":
		        $file_name = $name_only . '-' . $random_unique_no . '.jpg' ;
		        break;
		    default:
       		$file_name = $file;
		}
		return $file_name; 
	}

	/*
		# Description: Upload featured media and update alt, caption and description
	*/
	private function __uploadMedia(){
		// upload featured image
		$attachment_id = '';

		$arr = array('title' 		=> ucwords(strtolower($this->alt_text)), 
					'alt_text' 		=> ucwords(strtolower($this->alt_text)), 
					'caption' 		=> ucwords(strtolower($this->caption)), 
					'description' 	=> ucfirst(strtolower($this->description)));

		if($this->image_url!=''){
			$method		= strtoupper('post');
			$file_name 	= $this->get_file_name($this->image_url);
			$file 		= file_get_contents(trim($this->image_url));
			$url 		= $this->baseurl . '/wp-json/wp/v2/media/';
			$ch 		= curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $file );		
			//curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($arr) );		
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt( $ch, CURLOPT_HTTPHEADER, [
				'Content-Disposition: form-data; filename="'.$file_name.'"',
				'Authorization: Basic ' . base64_encode( $this->username . ':' . $this->password ),
				"cache-control: no-cache",    		
			] );
			$result = curl_exec( $ch );
			curl_close( $ch );
			$output_attachment = json_decode( $result );			
			$attachment_id = $output_attachment->id;

			if($attachment_id!=''){
				$curl_url = $url . $attachment_id;
				$method = strtoupper('put');
				$json = $this->__send($curl_url, $method, $arr, $this->username, $this->password);
			}			
		}		
		return $this->featured_img_id = (int)$attachment_id;
	}
	/*
		# Description: Generates url friendly slug text
		# @ param: string
		# Output: lowercase string		
	*/
	public function slug_text($str = NULL){
		return wordwrap(strtolower(trim($str)), 1, '-', 0);
	}
	/*
		# Description: Generates random strong password
		# @ param: string
		# Output: hash string		
	*/
	public function random_password( $length = 8 ) {
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
	    $password = substr( str_shuffle( $chars ), 0, $length );
	    return md5($password);
	}
	/*
		# Description: Retrieve and create category based on WP Rest API
		# @ param: string(Optional)
		# Output: Array of integer		
	*/

	private function __getCategoriesId($catstr = NULL){
		$url 		= $this->baseurl . '/wp-json/wp/v2/categories/';
		$method 	= strtoupper('get');
		$cat_arr 	= explode(',',$this->categories);
		$count_cat 	= sizeof($cat_arr);
			
			if($count_cat > 0){
				foreach($cat_arr as $cval){
					$curl_url = $url . '?slug=' . trim($this->slug_text($cval));					
					$json = $this->__send($curl_url, $method, '', $this->username, $this->password);
					$arrobj= json_decode($json);
					if (json_last_error() === 0) {
						if(!empty($arrobj)){					   
					   	$single_cat_id = $arrobj[0]->id; }
					   	else{

					   		$new_cat_arr = array('description'=>$cval, 'name'=>ucwords(strtolower($cval)), 'slug'=>trim($this->slug_text($cval)));

					   		$json = $this->__send($url, 'POST', $new_cat_arr, $this->username, $this->password);
					   		$arrobj= json_decode($json);
					   		//print_r($arrobj); exit;
					   		$single_cat_id = $arrobj->id;
					   	}
					}
					if($single_cat_id != 0){
						array_push($this->cat_id_arr, $single_cat_id);
					}
					$single_cat_id 	= '';
					$curl_url 		= '';
					$json 			= '';
				}
			}			
			return $this->cat_id_arr;
	}

	private function __getTagsId($catstr = NULL){
		$url = $this->baseurl . '/wp-json/wp/v2/tags/';
		$method = strtoupper('get');
		$tag_arr = explode(',',$this->tags);
			$count_tag = sizeof($tag_arr);
			
			if($count_tag > 0){
				foreach($tag_arr as $cval){
					$curl_url = $url . '?slug=' . trim($this->slug_text($cval));					
					$json = $this->__send($curl_url, $method, '', $this->username, $this->password);
					$arrobj= json_decode($json);
					if (json_last_error() === 0) {
						if(!empty($arrobj)){					   
					   	$single_tag_id = $arrobj[0]->id; }
					   	else{

					   		$new_tag_arr = array('description'=>$cval, 'name'=>ucwords(strtolower($cval)), 'slug'=>trim($this->slug_text($cval)));

					   		$json = $this->__send($url, 'POST', $new_tag_arr, $this->username, $this->password);
					   		$arrobj= json_decode($json);
					   		//print_r($arrobj); exit;
					   		$single_tag_id = $arrobj->id;
					   	}
					}
					if($single_tag_id != 0){
						array_push($this->tag_id_arr, $single_tag_id);
					}
					$single_tag_id 	= '';
					$curl_url 		= '';
					$json 			= '';
				}
			}			
			return $this->tag_id_arr;
	}

	private function __getAuthorId($authorname = NULL){
		$url 			= $this->baseurl . '/wp-json/wp/v2/users/?per_page=100';
		$method 		= strtoupper('get');
		$author_arr 	= explode(',',$this->authorname);
		$count_aut 		= sizeof($author_arr);
		$single_auth_id	= '';
		$single_auth 	= trim($author_arr[0]);
		//$curl_url 	= $url . '?slug=' . trim($this->slug_text($single_auth));
		$curl_url 		= $url;					
		$json 			= $this->__send($curl_url, $method, '', $this->username, $this->password);
		$arrobj			= json_decode($json);
		$user_f_l_name 	= array();
		
		// Returns the index of the array if found
		$ret 			= $this->recursive_array_search( $this->emailid, $arrobj );

		echo '<pre>';
		echo $ret;
		print_r($arrobj[$ret]->name);
		exit;

		if( !empty($arrobj) && ($ret != '') ){

			if( strtolower(trim($arrobj[$ret]->name)) === strtolower($single_auth) ){}else{

				$user_f_l_name 	= explode(' ',$single_auth);					
		   		$new_author_arr = array(
		   			'description'	=> ucwords(strtolower($single_auth)), 
		   			'username'		=> $this->clean($single_auth), 
		   			'slug'			=> trim($this->slug_text($single_auth)),
		   			'password'		=> $this->random_password(),
					'roles'			=> 'editor',
					'name'			=> ucwords($single_auth),
					'nickname'		=> ucwords($single_auth),
					'first_name'	=> ucwords(current($user_f_l_name)),
					'last_name'		=> ucwords(end($user_f_l_name)),
					'email'			=> trim($this->emailid)
		   		);
		   		$json = $this->__send($url, 'PUT', $new_author_arr, $this->username, $this->password);
		   		$arrobj= json_decode($json);
		   		//print_r($arrobj); exit;
		   		$single_auth_id = $arrobj->id;

			}



			foreach($arrobj as $valobj){

				if( ($this->emailid === $valobj->user_email) && ($ret === 'found') ){
					//echo $valobj->user_email;
					//echo '<br>';
					//echo $valobj->id;
					return $this->author_id = (int)$valobj->id ;
				}				
			}
		}else{
			if (json_last_error() === 0) {

				if( !empty($arrobj) && $this->emailid!='' ){
					$user_f_l_name = explode(' ',$single_auth);					
			   		$new_author_arr = array(
			   			'description'	=> ucwords(strtolower($single_auth)), 
			   			'username'		=> $this->clean($single_auth), 
			   			'slug'			=> trim($this->slug_text($single_auth)),
			   			'password'		=> $this->random_password(),
						'roles'			=> 'editor',
						'name'			=> ucwords($single_auth),
						'nickname'		=> ucwords($single_auth),
						'first_name'	=> ucwords(current($user_f_l_name)),
						'last_name'		=> ucwords(end($user_f_l_name)),
						'email'			=> trim($this->emailid)
			   		);
			   		$json = $this->__send($url, 'POST', $new_author_arr, $this->username, $this->password);
			   		$arrobj= json_decode($json);
			   		//print_r($arrobj); exit;
			   		$single_auth_id = $arrobj->id;
			   	}			   	
			}
		}



		return $this->author_id = (int)$single_auth_id ;			
	}

	public function __createPost(){
		$method = strtoupper('POST');
		$url = $this->baseurl . '/wp-json/wp/v2/posts/';
		$res = $this->__send($url, $method, $this->post_field, $this->username, $this->password );

		echo '<div class="alert alert-success"><pre>';
		print_r(json_decode($res));	
		echo '</pre></div>';

		return $res;
	}

	public function __send($url='', $method='GET', $post_fields='', $username='', $password=''){

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_POSTFIELDS => json_encode($post_fields),
		CURLOPT_HTTPHEADER => array(		  
		  "Authorization: Basic ". base64_encode( $username . ':' . $password ),
		  "Cache-Control: no-cache",
		  "Content-Type: application/json"          
		),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
        	return "Error #: " . $err;
      	} else {	        
	        return $response;	        
      	}

	} // end send

	/*------------------ Newly added functions on 27-2-2018 ------------------*/

	private function clean($string) {
		$string = str_replace(' ', '', $string); 
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
		return strtolower(preg_replace('/-+/', '-', $string)); 
	}

	private function recursive_array_search($needle,$haystack) {
		//echo '<pre>';		
		foreach($haystack as $key=>$value) {
			//print_r($value);
			if( is_object($value) ){
			//echo $key;
			//echo $value->user_email;
			//echo $needle;
			//exit;	
				if($value->user_email === $needle ){
					//return 'found';
					return (int)$key;
				}				
			}			
		}
		return '';
    }

} // end class
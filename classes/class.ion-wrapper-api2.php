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
		$this->content 		= trim($post_arr['description']);
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
		$this->post_field 	= array('title' 			=> $this->title,
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
		$str = str_replace('&', ' ', $str);
		$str = preg_replace('/[^A-Za-z0-9\-]/', ' ', $str);
		$str = preg_replace('/-+/', '-', $str);
		$str = strtolower(preg_replace('/\s+/', '-',$str));

		return $str;
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
		
		$url 			= $this->baseurl . '/wp-json/wp/v2/categories/';
		$method 		= strtoupper('get');
		$cat_arr 		= explode(',',$this->categories);
		$count_cat 		= sizeof($cat_arr);
		$single_cat_id 	= 0;


		if($count_cat > 0){
			foreach($cat_arr as $cval){
				$curl_url 	= $url . '?slug=' . trim($this->slug_text($cval));
				$json 		= $this->__send($curl_url, $method, '', $this->username, $this->password);
				$arrobj 	= json_decode($json);			

				if (json_last_error() === 0) {
					if(!empty($arrobj)){   
						$single_cat_id = $arrobj[0]->id; }
					else{
						$new_cat_arr = array('description'=>$cval, 'name'=>ucwords(strtolower($cval)), 'slug'=>trim($this->slug_text($cval)));
						$json = $this->__send($url, 'POST', $new_cat_arr, $this->username, $this->password);
						$arrobj= json_decode($json);
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
		$single_tag_id = 0;
		$url = $this->baseurl . '/wp-json/wp/v2/tags/';
		$method = strtoupper('get');
		$tag_arr = explode(',',$this->tags);
		$count_tag = sizeof($tag_arr);
		$single_tag_id = 0;
		
		if($count_tag > 0){
			foreach($tag_arr as $cval){
				$curl_url 	= $url . '?slug=' . trim($this->slug_text($cval));
				$json 		= $this->__send($curl_url, $method, '', $this->username, $this->password);
				$json 		= trim($json);
				$arrobj 	= json_decode($json);


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
		$update_url 	= '';
		$method 		= strtoupper('get');
		$author_arr 	= explode(',',$this->authorname);
		$count_aut 		= sizeof($author_arr);
		$single_auth_id = '';
		$single_auth 	= trim($author_arr[0]);
		$curl_url 		= $url . '?slug=' . trim($this->slug_text($single_auth));
		$curl_url 		= $url;
		$json 			= "";
		$arrobj 		= "";

		$json = $this->__send($curl_url, $method, '', $this->username, $this->password);

		$arrobj 		= json_decode($json);
		$user_f_l_name 	= array();
		
		// Returns the index of the array if found
		$ret = $this->recursive_array_search( $this->emailid, $arrobj );
		//print_r($ret);
		//exit;
		if( !empty($arrobj) && ($ret >= 0) ){
			if( strtolower(trim($arrobj[$ret]->name)) === strtolower($single_auth) ){
				$single_auth_id = (int)$arrobj[$ret]->id ; 
			}else{
				$update_url = $this->baseurl . '/wp-json/wp/v2/users/'.$arrobj[$ret]->id;
				$user_f_l_name = explode(' ',$single_auth);
				$new_author_arr = array(
				'description'	=> ucwords(strtolower($single_auth)),   
				'slug'			=> trim($this->slug_text($single_auth)),
				'password'		=> $this->random_password(),
				'roles'			=> 'editor',
				'name'			=> ucwords($single_auth),
				'nickname'		=> ucwords($single_auth),
				'first_name'	=> ucwords(current($user_f_l_name)),
				'last_name'		=> ucwords(end($user_f_l_name))
				);				

				$update_json = $this->__send($update_url, 'PUT', $new_author_arr, $this->username, $this->password);
				$update_arrobj= json_decode($update_json);
				
				$single_auth_id = (int)$arrobj[$ret]->id ;
			}
		}else{
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
					'email'			=> strtolower(trim($this->emailid))
				);
				$create_json 	= $this->__send($url, 'POST', $new_author_arr, $this->username, $this->password);
				$create_arrobj 	= json_decode($create_json);

				$single_auth_id = $create_arrobj->id;
			}
		}
		return $this->author_id = (int)$single_auth_id ;
	}




	public function __createPost(){
		$output = '';
		$method = strtoupper('POST');
		$url = $this->baseurl . '/wp-json/wp/v2/posts/';

		$res = $this->__send($url, $method, $this->post_field, $this->username, $this->password );	
		//print_r($res);
		//exit;
		echo '<div class="alert alert-success"><pre>';
		$output = (json_decode($res));
		echo $output->id;
		print_r($output);
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
		"Content-Type: application/json",
		"Charset: UTF-8"
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
		foreach($haystack as $key=>$value) {		
			if( is_object($value) ){				
				if(strtolower($value->user_email) === strtolower($needle) ){				
					return (int)$key;
				}
			}
		}
		return '';
	}

} // end class
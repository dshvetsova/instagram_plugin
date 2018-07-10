<?php
/**
 * @package Instagram_greenmars
 */
/*
Plugin Name: Instagram Plugin by Greenmars
Plugin URI: https://greenmars.ru
Description: Get data from user's Instagram via Graph Api Facebook.
Version: 1.0
Author: Greenmars
Author URI: https://greenmars.ru
License: GPLv2 or later
Text Domain: greenmars
*/


class InstagramData {

	public static function getPosts() {
		
		$token = (defined('INSTAGRAM_TOKEN')) ? INSTAGRAM_TOKEN : getenv('INSTAGRAM_TOKEN');
		$app_id = (defined('INSTAGRAM_APP_ID')) ? INSTAGRAM_APP_ID : getenv('INSTAGRAM_APP_ID');

		if ( !$app_id || !$token )
			return false;
		$posts = get_transient( 'instagram_posts' );
		
		if(!$posts) {
			$posts = array();
		    $count = 0;

		    $ch = curl_init();

		    $url = 'https://graph.facebook.com/v3.0/'.$app_id.'?fields=media&access_token='.$token;

		    $curl_base_options = [
	            CURLOPT_URL => $url,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_FOLLOWLOCATION => true,
	            CURLOPT_MAXREDIRS => 10,
	            CURLOPT_HEADER => true,
	            CURLOPT_ENCODING => ''
	        ];

	        curl_setopt_array($ch, $curl_base_options);
	        $response   = curl_exec($ch);
	        $error      = curl_error($ch);
	        $info       = curl_getinfo($ch);

	        $header_size = $info['header_size'];
	        $header      = substr($response, 0, $header_size);
	        $body        = substr($response, $header_size);
	        $httpCode    = $info['http_code'];

		    $curl_base_options = [
	            CURLOPT_URL => $url,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_FOLLOWLOCATION => true,
	            CURLOPT_MAXREDIRS => 10,
	            CURLOPT_HEADER => true,
	            CURLOPT_ENCODING => ''
	        ];

	        curl_setopt_array($ch, $curl_base_options);
	        $response   = curl_exec($ch);
	        $error      = curl_error($ch);
	        $info       = curl_getinfo($ch);

	        $header_size = $info['header_size'];
	        $header      = substr($response, 0, $header_size);
	        $body        = substr($response, $header_size);
	        $httpCode    = $info['http_code'];

	        if($httpCode != '200') {

	        	if(is_admin())
	        		echo 'HTTP код: '.$httpCode;
	        	return;
	        }

	        $media = json_decode($body, true);
	        $media = $media['media']['data'];
	        $item = array();
	        foreach($media as $key => $image) {
	        	if($key == 9)
	        		break;
	        	$url = 'https://graph.facebook.com/v3.0/'.$image['id'].'?fields=id,media_type,media_url,owner,timestamp,permalink,thumbnail_url,caption,like_count&access_token='.$token;

	        	$curl_base_options = [
		            CURLOPT_URL => $url,
		            CURLOPT_RETURNTRANSFER => true,
		            CURLOPT_FOLLOWLOCATION => true,
		            CURLOPT_MAXREDIRS => 10,
		            CURLOPT_HEADER => true,
		            CURLOPT_ENCODING => ''
		        ];

		        curl_setopt_array($ch, $curl_base_options);
		        $response   = curl_exec($ch);
		        $error      = curl_error($ch);
		        $info       = curl_getinfo($ch);

		        $header_size = $info['header_size'];
		        $header      = substr($response, 0, $header_size);
		        $body        = substr($response, $header_size);
		        $httpCode    = $info['http_code'];
		        if($httpCode == '200') {
		        	$answer = json_decode($body, true);
		        	if($answer['media_type'] == 'VIDEO') {
		        		$item['src'] = $answer['thumbnail_url'];
		        	} else
		        		$item['src'] = $answer['media_url'];

		        	$item['link'] = $answer['permalink'];
		        	$item['alt'] = $answer['caption'];
		        	$item['likes'] = $answer['like_count'];
		        	$item['timestamp'] = $answer['timestamp'];
		        	$posts['ITEMS'][] = $item;
		        }
	        }

	        if(!empty($posts['ITEMS'])) {

				$url = 'https://graph.facebook.com/v3.0/'.$app_id.'?fields=id,username,followers_count,follows_count,media_count,name,profile_picture_url&access_token='.$token;
				$curl_base_options = [
		            CURLOPT_URL => $url,
		            CURLOPT_RETURNTRANSFER => true,
		            CURLOPT_FOLLOWLOCATION => true,
		            CURLOPT_MAXREDIRS => 10,
		            CURLOPT_HEADER => true,
		            CURLOPT_ENCODING => ''
		        ];
				curl_setopt_array($ch, $curl_base_options);
		        $response   = curl_exec($ch);
		        $error      = curl_error($ch);
		        $info       = curl_getinfo($ch);

		        $header_size = $info['header_size'];
		        $header      = substr($response, 0, $header_size);
		        $body        = substr($response, $header_size);
		        $httpCode    = $info['http_code'];
		        if($httpCode == '200') {
					$user = json_decode($body, true);
					$posts['COUNTS']['MEDIA'] = $user['media_count'];
			  		$posts['COUNTS']['FOLLOWS'] = $user['follows_count'];
			  		$posts['COUNTS']['FOLLOWED_BY'] = $user['followers_count'];
			  		$posts['NAME'] = $user['name'];
			  		$posts['USERNAME'] = $user['username'];
			  		$posts['LINK'] = 'https://www.instagram.com/'.$user['username'].'/';
			  		$posts['PROFILE_PICTURE'] = $user['profile_picture_url'];
			  	}

			}

			curl_close($ch);

			set_transient( 'instagram_posts', $posts, DAY_IN_SECONDS / 2);
		}
		return $posts;
	}
}

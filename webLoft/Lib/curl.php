<?php
/**
 * 封装Curl
 *
 * @param unknown_type $url
 * @param unknown_type $data
 * @return unknown
 */
function _file_get_contents($url,$return_header=1,$post_data='',$save_cookie=0,$is_ajax=0,$referer=""){
	
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0");
    curl_setopt( $ch,CURLOPT_CONNECTTIMEOUT, 30 );

	if($return_header==1){
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
	}
    if(strpos($url,"https")!==false){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在'
    }

	if($save_cookie==1){
		curl_setopt( $ch, CURLOPT_COOKIEFILE, COOKIEJAR );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, COOKIEJAR );
	}
	if($referer){
        curl_setopt ($ch,CURLOPT_REFERER,$referer);
    }

	curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); 

	if(is_array($post_data)==true){
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data);	
	}
    if($is_ajax){
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest', 'X-MicrosoftAjax: Delta=true'));
    }
    
	$str=curl_exec( $ch );
	curl_close( $ch );
	return $str;
}

function _file_get_contents2($connomains,$timeout=30){
    $mh = curl_multi_init();

    foreach ($connomains as $i => $url) {
         $conn[$i]=curl_init($url);
         curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,1);
         curl_setopt($conn[$i], CURLOPT_TIMEOUT, $timeout);
         curl_multi_add_handle ($mh,$conn[$i]);
    }

    do{
        $mrc = curl_multi_exec($mh,$active);
    }while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active and $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }
    $return=array();
    foreach ($connomains as $i => $url) {
        $return[]=curl_multi_getcontent($conn[$i]);
        curl_close($conn[$i]);
    }
    curl_multi_close ( $mh );  
    return $return;
}
<?php
  require 'simple_html_dom.php';

  $username = "";
  $password = "";
  $loginurl = "https://www.seriousgmod.com/index.php?login/login";
  $hackingthread = "https://www.seriousgmod.com/threads/hacking-evidence-collection.50912/";
  $postinfo = "login=".$username."&register=0&password=".$password."&cookie_check=1&_xfToken=&redirect=https://www.seriousgmod.com/";
 
  $curl_handle = curl_init ($url);
  curl_setopt($ch, CURLOPT_COOKIESESSION, true);
  curl_setopt ($curl_handle, CURLOPT_COOKIEJAR, 'temp/cookie.txt');
  curl_setopt ($curl_handle, CURLOPT_COOKIEFILE, 'temp/cookie.txt');
  
  curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $post_array = array('login' => $username, 'password' => $password, 'cookie_check' => 1, 'redirect' => 'https://www.seriousgmod.com/', 'register' => 0, 'remember' => 1);
  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($post_array));
  $output = curl_exec ($curl_handle);
  
  //Now logged in
  
  $curl_handle = curl_init ('https://www.seriousgmod.com/threads/hacking-evidence-collection.50912/');
  
  curl_setopt($ch, CURLOPT_COOKIESESSION, true);
  curl_setopt ($curl_handle, CURLOPT_COOKIEJAR, 'temp/cookie.txt');
  curl_setopt ($curl_handle, CURLOPT_COOKIEFILE, 'temp/cookie.txt');
  curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, true);
  $output = curl_exec ($curl_handle);
  echo $output;

?>
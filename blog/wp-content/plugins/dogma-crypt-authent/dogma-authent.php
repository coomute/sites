<?php
/*
Plugin Name: Dogma users authentication
Description: Authenticate users form dogmazic using crypt() and blowfish
Version: 1.0.1
Author: Tumulte
*/

// plugin must run before any other authentication plugins -
add_filter('authenticate', 'dogmaAuth', 1, 3);


// function provides user authentication against crypt() password

function dogmaAuth( $user, $username, $typed_password ) {

  if ( is_a($user, 'WP_User') ) {
    return $user; 
  }

   // check existence of required parameters
  if ( empty($username) || empty($typed_password) )
    return $user;
	
	// retrieve user data
	$userdata = get_user_by('login', $username);
  if ( !$userdata )
    return $user;

   // authenticate against stored dogma password
   dogmaAuthVerification( $typed_password, $userdata->user_pass, $userdata->salt );
   
   return $user;
}


// Check Blowfish crypt and crypt with autogenerated hash.
function dogmaAuthVerification( $typed_password, $stored_hash, $salt) {

	$hashed_typed_pass = crypt($typed_password,$salt);
	
	 if($stored_hash == $hashed_typed_pass XOR $hashed_typed_pass == crypt($typed_password, $stored_hash)){
      return true;
   }
   else {
      return false;
   }
}



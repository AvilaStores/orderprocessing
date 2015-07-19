<?php

// Use to learn how to put and fetch from the AppEngine datastore

//require_once 'DatastoreService.php';
//require_once 'OAuth1TokenModel.php';
//
//$google_api_config = [
//    'application-id' => 'magento-orders',
//    'service-account-name' => '855043483396-d6a2uqac94d0dgmgbmoak6ocvtc0v444@developer.gserviceaccount.com',
//    'private-key' => file_get_contents('credentials/magento-orders-bcb6b4e5eec6.p12'),
//    'dataset-id' => 'magento-orders'
//];
//
//DatastoreService::setInstance(new DatastoreService($google_api_config));
//
//$request_token = 'this is the request token';
//$request_secret = 'this is the request secret';
//$access_token = 'this is the access token';
//$access_secret = 'this is the access secret';
//
//$token_model = new OAuth1TokenModel($request_token, $request_secret, $access_token, $access_secret);
//
//// save the instance to the datastore
//$token_model->put();
//
//// now, try fetching the saved model from the datastore
//
//$kname = sha1($request_token);
//// fetch the token with that key, as part of the transaction
//$token_model_fetched = OAuth1TokenModel::fetch_by_name($kname)[0];
//
//echo "fetched token model with request token: " . $token_model_fetched->getRequestToken();

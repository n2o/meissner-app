<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $helpers = array('Html', 'Form', 'Session', 'Event', 'User');

	var $_jsVars = array(); # array to store system wide JavaScript variables

	var $isMobile = false;  # preparing for mobile view 

	# Specify here were to go to after login / logout
	public $components = array(
		'Session',
		'Auth' => array(
			'loginRedirect' => array('controller' => 'events', 'action' => 'index'),
			'logoutRedirect' => array('controller' => 'users', 'action' => 'login'),
			#'authorize' => array('Controller')
		)
	);

	# Store $value into the JS array
	private function setJsVar($name, $value) {
		$this->_jsVars[$name] = $value;
	}

	# Create new public / private key pair if not available on server
	private function createKeyValuePair() {
		if (!file_exists("private.key") || !file_exists("public.key")) {
			$privateKey = openssl_pkey_new(array(
				'digest_alg' => 'sha512',
			    'private_key_bits' => 1024,
			    'private_key_type' => OPENSSL_KEYTYPE_RSA,
			));
			openssl_pkey_export_to_file($privateKey, 'private.key');
			$a_key = openssl_pkey_get_details($privateKey);
			file_put_contents('public.key', $a_key['key']);
			openssl_free_key($privateKey);
		}
	}

	# Check the role of the clients
	public function isAuthorized($user) {
		# Admin can access every action
		if (isset($user['role']) && $user['role'] === 'admin')
			return true;

		# Default deny
		return false;
	}

	# Specifies what happens before the page is loaded
	public function beforeFilter() {
		parent::beforeFilter();

		# Check if key value pair is accessible on webserver
		$this->createKeyValuePair();

		# Make current username accessible for JavaScript
		$username = $this->Session->read('Auth.User.username');
		$this->setJsVar('username', $username);
		$this->setJsVar('hostname', $_SERVER['HTTP_HOST']);
		$this->setJsVar('port', 9999); // do not forget to set this in OtherComponent.php

		# Guest can login and logout
		$this->Auth->allow('login', 'logout');

		$id = $this->Session->read('Auth.User.id');
		$subscriptions = array();

		# Prepare Publish/Subscribe for WebSocket server
		if (isset($id)) {
			# Set subscriptions
			$this->loadModel('User');
			$query = $this->User->query('SELECT id FROM events WHERE user_id = '.$id);
			$i = 0;
			foreach ($query as $key => $value)
				$subscriptions[$i++] = array('event' => $value['events']['id']);

			$this->setJsVar('subscriptions', $subscriptions);

		# Create signature for syn message for websocket server
			$fileContents = file_get_contents('private.key');
			$privateKey = openssl_pkey_get_private($fileContents);

			$signature = "";
			if (!openssl_sign($username, $signature, $privateKey))
    			die('Failed to encrypt data');
    		
    		# Remove $privateKey from RAM
    		openssl_free_key($privateKey);

    		$signature = base64_encode($signature);

			$synMessage = '{"name":"'.$username.'","type":"syn","sig":"'.$signature.'"}';
			$this->setJsVar('synMessage', $synMessage);
		}

		# if device is mobile, change layout to mobile
		if ($this->request->isMobile()) {
			$this->layout = 'mobile';
			# and if a mobile view file has been created for the action, serve it instead of the default view file
			$mobileViewFile = strtolower($this->params['controller']) . '/mobile/' . $this->params['action'] . '.ctp';
			if (file_exists($mobileViewFile)) {
				$mobileView = strtolower($this->params['controller']) . '/mobile/';
				$this->viewPath = $mobileView;
			}
		}
		$this->set('is_mobile', $this->isMobile);
	}

	# Specifies what happens before the page is shown
	public function beforeRender() {
		parent::beforeRender();

		# Make JS variables accessible
		$this->set('jsVars', $this->_jsVars);

		# Prepare mobile view for all controllers
		if ($this->request->isMobile()||isset($this->request->query['mobile'])) {
			$this->viewClass = 'Theme';
			$this->theme = 'Mobile';    # Switch current theme to Mobile
			$this->layout = 'mobile';
		}
	}

	public function afterFilter() {
		# if in mobile mode, check for a valid view and use it
		if (isset($this->is_mobile) && $this->is_mobile) {
			$view_file = file_exists( 'Views' . $this->name . DS . 'mobile/' . $this->action . '.ctp' );
			$layout_file = file_exists( 'Layouts' . 'mobile/' . $this->layout . '.ctp' );
			if($view_file || $layout_file)
				$this->render($this->action, ($layout_file?'mobile/':'').$this->layout, ($view_file?'mobile/':'').$this->action);
		}
	}
}
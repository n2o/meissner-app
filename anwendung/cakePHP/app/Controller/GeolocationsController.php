<?php
class GeolocationsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session', 'Event', 'User');
	public $components = array('Session');

	# Show all the events 
	public function index() {
		$this->set('username', $this->Session->read('Auth.User.username'));
	}
}
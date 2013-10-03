<?php
namespace Oxygen\WeezeventBundle\API;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * API to access Weezevent data
 * 
 * @author lolozere
 *
 */
class WeezeventAPI {
	
	/**
	 * @var Session
	 */
	protected $session;
	protected $apiKey;
	/**
	 * True if connected to Weezevent API
	 * 
	 * @var bool
	 */
	protected $isConnected = false;
	
	protected $defaultsCredentials = array('username' => null, 'password' => null);
	
	public function __construct($session, $apiKey, $userName = null, $password = null) {
		$this->session = $session;
		$this->apiKey = $apiKey;
		$this->defaultsCredentials = array('username' => $userName, 'password' => $password);
		if (!is_null($this->session->get('oxygen_weezevent/token', null))) {
			$this->isConnected = true;
		}
	}
	/**
	 * 
	 * @param string $path
	 * @param array $params
	 * @return array
	 */
	protected function getResponse($path, $params) {
		$options = array(
				CURLOPT_RETURNTRANSFER => true, // return web page
				CURLOPT_HEADER => false, // don't return headers
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_ENCODING => "", // handle compressed
				CURLOPT_USERAGENT => "test", // who am i
				CURLOPT_AUTOREFERER => true, // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
				CURLOPT_TIMEOUT => 120, // timeout on response
				CURLOPT_MAXREDIRS => 10 // stop after 10 redirects
			);
		$requestValues = array();
		foreach($params as $key => $value) {
			$requestValues[] = $key.'='.rawurlencode($value);
		}
		$ch = curl_init ( "https://api.weezevent.com".$path.'?'.join('&', $requestValues) );
		curl_setopt_array ( $ch, $options );
		// Get
		$content = curl_exec ( $ch );
		$err = curl_errno( $ch );
		$errmsg = curl_error ( $ch );
		curl_close ( $ch );
		return json_decode($content, true);
	}
	
	protected function throwExceptionIfNotConnected() {
		if (!$this->isConnected) {
			if (empty($this->defaultsCredentials['username']) && empty($this->defaultsCredentials['password'])) {
				throw new NotConnectedException();
			} else {
				$this->connect($this->defaultsCredentials['username'], $this->defaultsCredentials['password']);
			}
		}
	}
	/**
	 * @return string
	 * @throws NotConnectedException
	 */
	public function getToken() {
		$this->throwExceptionIfNotConnected();
		return $this->session->get('oxygen_weezevent/token');
	}
	
	/**
	 * Connect to Weezevent and get access token
	 * 
	 * @return 
	 */
	public function connect($userName, $password) {
		if (!$this->isConnected) {
			$response = $this->getResponse('/auth/access_token/', array('api_key' => $this->apiKey, 'username' => $userName, 'password' => $password));
			$this->session->set('oxygen_weezevent/token', $response['accessToken']);
			$this->isConnected = true;
		}
	}
	
	public function getEvents() {
		$this->throwExceptionIfNotConnected();
		$r = $this->getResponse('/events', array('access_token' => $this->getToken(), 'api_key' => $this->apiKey));
		return $r['events'];
	}
	/**
	 * Return tickets of an event
	 * 
	 * Ticket informations : id, name, price, participants, quota, event{id, name}
	 * 
	 * @param integer $eventId
	 * @return array
	 */
	public function getTickets($eventId) {
		$this->throwExceptionIfNotConnected();
		$r = $this->getResponse('/tickets', array('access_token' => $this->getToken(), 'api_key' => $this->apiKey, 'id_event' => $eventId));
		$tickets = array();
		foreach($r['events'] as $event) {
			foreach($event['tickets'] as $ticket) {
				$ticket['event'] = array('id' => $event['id'], 'name' => $event['name']);
				$tickets = array_merge($tickets, array($ticket));
			}
		}
		return $tickets;
	}
	/**
	 * Return all tickets
	 *
	 * Ticket informations : id, name, price, participants, quota, event{id, name}
	 *
	 * @param integer $eventId
	 * @return array
	 */
	public function getAllTickets() {
		$events = $this->getEvents();
		$tickets = array();
		foreach($events as $event) {
			$tickets = array_merge($tickets, $this->getTickets($event['id']));
		}
		return $tickets;
	}
	
	public function isIdWeezTicketValid($ticketWeezId, $ticketId) {
		$this->throwExceptionIfNotConnected();
		$r = $this->getResponse('/participants', array('access_token' => $this->getToken(), 'api_key' => $this->apiKey, 'id_ticket[]' => $ticketId));
		$tickets = array();
		foreach($r['participants'] as $participant) {
			if ($participant['id_weez_ticket'] == $ticketWeezId) {
				return true;
			}
		}
		return false;
	}
	
	public function getParticipants($ticketId) {
		$this->throwExceptionIfNotConnected();
		$r = $this->getResponse('/participants', array('access_token' => $this->getToken(), 'api_key' => $this->apiKey, 'id_ticket[]' => $ticketId));
		if (!empty($r['participants'])) {
			return $r['participants'];
		}
		return null;
	}
	
	/**
	 * Return email of a participant from ticket id
	 * 
	 * @param string $ticketWeezId
	 * @param string $ticketId
	 */
	public function getEmail($ticketWeezId, $ticketId) {
		$this->throwExceptionIfNotConnected();
		$r = $this->getResponse('/participants', array('access_token' => $this->getToken(), 'api_key' => $this->apiKey, 'id_ticket[]' => $ticketId));
		$tickets = array();
		if (!empty($r['participants'])) {
			foreach($r['participants'] as $participant) {
				if ($participant['id_weez_ticket'] == $ticketWeezId) {
					return (!empty($participant['owner']['email']))?$participant['owner']['email']:null;
				}
			}
		}
		return null;
	}
}
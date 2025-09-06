<?php

class easy_unsubscribe extends rcube_plugin
{	
	private $message_headers_done = false;
	private $unsubscribe_img;

	function init()
	{
		$rcmail = rcmail::get_instance();
		$layout = $rcmail->config->get('layout');

		$this->add_hook('message_headers_output', array($this, 'message_headers'));
		$this->add_hook('storage_init', array($this, 'storage_init'));
		
		$this->include_stylesheet('easy_unsubscribe.css');
	}
	
	public function storage_init($p)
	{
		$p['fetch_headers'] = trim($p['fetch_headers'] . ' ' . strtoupper('List-Unsubscribe'));
		return $p;
	}
	
	public function message_headers($p)
	{		
		if($this->message_headers_done===false)
		{
			$this->message_headers_done = true;

			$ListUnsubscribe = $p['headers']->others['list-unsubscribe'];
			
			preg_match('%\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))%i', $ListUnsubscribe, $UnsubURL);
			preg_match('/mailto:(.*?)>/', $ListUnsubscribe, $UnsubEmail);

			if(!empty($UnsubURL[0]))
				$this->unsubscribe_img = '<a class="easy_unsubscribe_link tooltip-right" data-tooltip="Unsubscribe via URL" href="'.$UnsubEmail[0].'" target="_blank" onclick="return confirm(\'Are you sure you want to unsubscribe?\');"><img src="plugins/easy_unsubscribe/icon.png" alt="Unsubscribe" /></a>';
			
			if(!empty($UnsubEmail[1]))
				$this->unsubscribe_img .= '<a class="easy_unsubscribe_link tooltip-right" data-tooltip="Unsubscribe via Email" href="'.$UnsubEmail[1].'" target="_blank" onclick="return confirm(\'Are you sure you want to unsubscribe?\');"><img src="plugins/easy_unsubscribe/icon.png" alt="Unsubscribe" /></a>';

		}

		if(isset($p['output']['subject']))
		{
			$p['output']['subject']['value'] = $p['output']['subject']['value'] . $this->unsubscribe_img;
			$p['output']['subject']['html'] = 1;
		}

		return $p;
	}
}

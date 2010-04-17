<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Feed extends Controller {

	protected $feeds = array(
		'forums' => array(
			'http://'
		),
	);

	public function action_load($name, $type = NULL)
	{
		$feed = Sprig::factory('feed', array('name' => $name))
			->load();

		if ( ! $feed->loaded())
		{
			throw new Kohana_Exception('Requested feed not found: :name', array(':name' => $name));
		}

		$this->request->response = View::factory('template/feed')
			->bind('links', $links)
			->bind('feed', $feed);

		if (Request::$is_ajax)
		{
			// Actually load the list of feed items and return it

			// Set the caching key name
			$cache = "feed:{$feed->id}";

			// Load the feed items from cache
			$links = Kohana::cache($cache.'x', NULL, $feed->lifetime);

			if ( ! $links)
			{
				// Parse the feed with the given limit
				$items = Feed::parse($feed->url, $feed->limit);

				$links = array();
				foreach ($items as $item)
				{
					// Choose the item link
					$link = isset($item['id']) ? $item['id'] : $item['link'];

					// Add the link to the list
					$links[(string) $link] = (string) $item['title'];
				}

				// Cache the parsed feed
				Kohana::cache($cache, $links, $feed->lifetime);
			}
		}
	}

} // End Feed
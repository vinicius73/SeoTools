<?php
namespace Vinicius73\SEO;

use App;
use Input;
use Vinicius73\SEO\Contracts\OpenGraphAware;

class OpenGraph implements OpenGraphAware
{
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	protected static $defaults = array(
		'title'       => 'seotools',
		'description' => 'seotools',
		'url'         => null,
		'type'        => false,
		'image'       => array(),
		'site_name'   => false
	);


	public function __construct($defaults = array())
	{
		if (!empty($defaults)):
			self::$defaults = array_merge(self::$defaults, $defaults);
		endif;

		$this->data = self::$defaults;
	}

	/**
	 * @param $data
	 */
	public function setup(array $data)
	{
		$this->data = array_merge(self::$defaults, $data);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOpenGraphData()
	{
		$data = $this->data;
		switch ($data['title']):
			case 'seotools':
				$data['title'] = App::make('vinicius73.seotools.generators.meta')->getTitleSession();
				break;
			case null:
				unset($data['title']);
				break;
		endswitch;

		switch ($data['description']):
			case 'seotools':
				$data['description'] = App::make('vinicius73.seotools.generators.meta')->getDescription();
				break;
			case null:
				unset($data['description']);
				break;
		endswitch;

		if (empty($data['url'])):
			$data['url'] = Input::fullUrl();
		endif;

		if (!$data['type']):
			unset($data['type']);
		endif;

		if (!$data['site_name']):
			unset($data['site_name']);
		endif;

		return $data;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function __set($key, $value)
	{
		if (method_exists($this, 'set' . camel_case($key))):
			$this->{'set' . camel_case($value)}($value);
		else:
			$this->data[$key] = $value;
		endif;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function __call($key, $value)
	{
		if ($key == 'image'):
			$this->addImage($value);
		elseif (in_array($key, self::$defaults)):
			$this->data[$key] = $value;
		endif;

		return $this;
	}

	public function addImage($image)
	{
		$this->data['image'][] = $image;
		return $this;
	}

	/**
	 * @return string
	 */
	public function generate()
	{
		$og = App::make('vinicius73.seotools.generators.opengraph');
		$og->fromObject($this);
		return $og->generate();
	}

	/**
	 * @return $this
	 */
	public function reset()
	{
		$this->data = self::$defaults;
		return $this;
	}
}
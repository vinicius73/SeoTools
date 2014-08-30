<?php namespace Vinicius73\SEO\Generators;

use Vinicius73\SEO\Contracts\MetaAware;

class MetaGenerator
{
	/**
	 * The meta title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The meta title session.
	 *
	 * @var string
	 */
	protected $title_session;

	/**
	 * The meta description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * The meta keywords.
	 *
	 * @var array
	 */
	protected $keywords;

	/**
	 * extra metatags
	 *
	 * @var array
	 */
	protected $metatags = array();

	/**
	 * The default configurations.
	 *
	 * @var array
	 */
	protected $defaults = array(
		'title'       => false,
		'description' => false,
		'separator'   => ' | ',
		'keywords'    => array()
	);

	protected $webmaster = array(
		'google'    => null,
		'bing'      => null,
		'alexa'     => null,
		'pinterest' => null,
		'yandex'    => null
	);

	public static $webmasterTags = array(
		'google'   => "google-site-verification",
		'bing'     => "msvalidate.01",
		'alexa'    => "alexaVerifyID",
		'pintrest' => "p:domain_verify",
		'yandex'   => "yandex-verification"
	);

	/**
	 * Create a new MetaGenerator instance.
	 *
	 * @param array $defaults
	 * @param array $webmaster
	 */
	public function __construct(array $defaults = array(), array $webmaster = array())
	{
		$this->defaults = array_merge($this->defaults, $defaults);

		$this->webmaster = array_merge($this->webmaster, $webmaster);
	}

	/**
	 * Render the meta tags.
	 *
	 * @return string
	 */
	public function generate()
	{
		$this->loadWebmasterTags();

		$title       = $this->getTitle();
		$description = $this->getDescription();
		$keywords    = $this->getKeywords();
		$metatags    = $this->metatags;

		$html = array();

		$html[] = "<title>$title</title>";
		$html[] = "<meta name='description' itemprop='description' content='{$description}' />";

		if (!empty($keywords)) {
			$html[] = "<meta name='keywords' content='{$keywords}' />";
		}

		foreach ($metatags as $key => $value):
			$name    = $value[0];
			$content = $value[1];
			$html[]  = "<meta {$name}='{$key}' content='{$content}' />";
		endforeach;

		return implode(PHP_EOL, $html);
	}

	/**
	 * Use the meta data of a MetaAware object.
	 *
	 * @param MetaAware $object
	 */
	public function fromObject(MetaAware $object)
	{
		$data = $object->getMetaData();

		if (array_key_exists('title', $data)) {
			$this->setTitle($data['title']);
		}

		if (array_key_exists('description', $data)) {
			$this->setDescription($data['description']);
		}

		if (array_key_exists('keywords', $data)) {
			$this->setKeywords($data['keywords']);
		}
	}

	/**
	 * Set the Meta title.
	 *
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$title = strip_tags($title);

		$this->title_session = $title;

		$this->title = $title . $this->getDefault('separator') . $this->getDefault('title');
	}

	/**
	 * Set the Meta description.
	 *
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$description = strip_tags($description);

		if (mb_strlen($description) > 160) {
			$description = mb_substr($description, 0, 160);
		}

		$this->description = $description;
	}

	/**
	 * Set the Meta keywords.
	 *
	 * @param string|array $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	/**
	 * @param string|array $defaultkey
	 * @param string $value
	 */
	public function setDefaults($defaultkey, $value = null)
	{
		if (is_array($defaultkey)):
			foreach ($defaultkey as $key => $value):
				$this->defaults[$key] = $value;
			endforeach;
		else:
			$this->defaults[$defaultkey] = $value;
		endif;
	}

	/**
	 * @param string $keyword
	 */
	public function addKeyword($keyword)
	{
		if (!is_array($this->keywords)):
			$this->keywords = explode(', ', $this->keywords);
		endif;

		$this->keywords[] = $keyword;
	}

	/**
	 * @param        $meta
	 * @param null $value
	 * @param string $name
	 */
	public function addMeta($meta, $value = null, $name = 'name')
	{
		if (is_array($meta)):
			foreach ($meta as $key => $value):
				$this->metatags[$key] = array($name, $value);
			endforeach;
		else:
			$this->metatags[$meta] = array($name, $value);
		endif;
	}

	/**
	 * Get the Meta title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title ? : $this->getDefault('title');
	}

	/**
	 * @return string
	 */
	public function getTitleSession()
	{
		return $this->title_session ? : $this->getTitle();
	}

	/**
	 * Get the Meta keywords.
	 *
	 * @return string
	 */
	public function getKeywords()
	{
		$keywords = $this->keywords ? : $this->getDefault('keywords');
		return (is_array($keywords)) ? implode(', ', $keywords) : $keywords;
	}

	/**
	 * Get the Meta description.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description ? : $this->getDefault('description');
	}

	/**
	 * Reset the title and description fields.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->title       = null;
		$this->description = null;
		$this->keywords    = array();
	}

	/**
	 * Get a default configuration.
	 *
	 * @param string $default
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getDefault($default)
	{
		if (array_key_exists($default, $this->defaults)) {
			return $this->defaults[$default];
		}

		$class = get_class($this);
		throw new \InvalidArgumentException("{$class}: default configuration $default does not exist.");
	}

	/**
	 * Load Webmaster tags
	 */
	public function loadWebmasterTags()
	{
		foreach ($this->webmaster as $name => $value):
			if (!empty($value)):
				$meta = array_get(self::$webmasterTags, $name, $name);
				$this->addMeta($meta, $value);
			endif;
		endforeach;
	}
}

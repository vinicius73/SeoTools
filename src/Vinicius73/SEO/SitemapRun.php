<?php namespace Vinicius73\SEO;


class SitemapRun
{

	/**
	 * @var \Vinicius73\SEO\Generators\SitemapGenerator
	 */
	public $generator;

	public function __construct($generator)
	{
		$this->generator = $generator;
	}

	/**
	 * Run generator commands
	 */
	public function run()
	{
		$this->generator->addRaw(
			array(
				  'location'         => 'example.com',
				  'last_modified'    => '2013-01-28',
				  'change_frequency' => 'weekly',
				  'priority'         => '0.65'
			)
		);

		$this->generator->addRaw(
			array(
				  'location'         => 'example.com/test',
				  'last_modified'    => '2013-12-28',
				  'change_frequency' => 'weekly',
				  'priority'         => '0.95'
			)
		);

		$this->generator->addRaw(
			array(
				  'location'         => 'example.com/test/2',
				  'last_modified'    => '2013-12-25',
				  'change_frequency' => 'weekly',
				  'priority'         => '0.99'
			)
		);

		return $this->generator->generate();
	}
}
<?php namespace Calotype\SEO;

class SitemapRun
{

	/**
	 * @var \Calotype\SEO\Generators\SitemapGenerator
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
	}
}
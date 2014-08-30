<?php namespace Vinicius73\SEO\Providers;

use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Vinicius73\SEO\Generators\MetaGenerator;
use Vinicius73\SEO\Generators\OpenGraphGenerator;
use Vinicius73\SEO\Generators\SitemapGenerator;
use Vinicius73\SEO\OpenGraph as OpenGraphHelper;

class SEOServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['config']->package('vinicius73/seotools', __DIR__ . '/../../../config');

		$this->registerBindings();
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$app = $this->app;

		// Generate sitemap.xml route
		if ($app['config']->get('seotools::sitemap.enabled')):
			$this->app['router']->get(
				'sitemap.xml',
				function () use ($app) {
					if ($app['config']->get('seotools::sitemap.cache')):
						$sitemap = $app['cache']->remember(
							'seotools::sitemap.xml',
							$app['config']->get('seotools::sitemap.cachetime'),
							function () use ($app) {
								$app['vinicius73.seotools.generators.sitemap.run']->run();

								return $app['vinicius73.seotools.generators.sitemap']->generate();
							}
						);
					else:
						$sitemap = $app['vinicius73.seotools.generators.sitemap.run']->run();
					endif;

					$response = new Response($sitemap, 200);
					$response->header('Content-Type', 'text/xml');

					return $response;
				}
			);
		endif;
	}

	/**
	 * Register the bindings.
	 *
	 * @return void
	 */
	public function registerBindings()
	{
		// Register the sitemap.xml generator
		$this->app->singleton(
			'vinicius73.seotools.generators.sitemap',
			function ($app) {
				return new SitemapGenerator();
			}
		);

		// Register the sitemap configuration
		$this->app->singleton(
			'vinicius73.seotools.generators.sitemap.run',
			function ($app) {
				$class = $app['config']->get('seotools::sitemap.classrun');

				return new $class($app['vinicius73.seotools.generators.sitemap']);
			}
		);

		// Register the meta tags generator
		$this->app->singleton(
			'vinicius73.seotools.generators.meta',
			function ($app) {
				$defaults = $app['config']->get('seotools::meta.defaults');
				$webmaster = $app['config']->get('seotools::webmaster');

				return new MetaGenerator($defaults, $webmaster);
			}
		);

		// Register the open graph properties generator
		$this->app->singleton(
			'vinicius73.seotools.generators.opengraph',
			function ($app) {
				return new OpenGraphGenerator();
			}
		);

		// Register the open graph properties generator helper
		$this->app->singleton(
			'vinicius73.seotools.generators.opengraph.helper',
			function ($app) {
				$defaults = $app['config']->get('seotools::opengraph.defaults');

				return new OpenGraphHelper(array(), $defaults);
			}
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'vinicius73.seotools.generators.meta',
			'vinicius73.seotools.generators.sitemap',
			'vinicius73.seotools.generators.opengraph',
			'vinicius73.seotools.generators.opengraph.helper'
		);
	}

}

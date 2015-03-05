# Vinicius73 / SEOTools

[![Total Downloads](https://poser.pugx.org/vinicius73/seotools/downloads.png)](https://packagist.org/packages/vinicius73/seotools)

> **Warning!** This package still needs to have its optimized test. 
----
> This package is a fork of https://github.com/Calotype/SEO

SEOTools is a package for **Laravel 4** that provides helpers for some common SEO techniques.

> ##For **Laravel 5** use [artesaos/seotools](https://github.com/artesaos/seotools)

## Features

- Ease of set titles and meta tags 
- friendly interface 
- Easy setup and custumização 
- Support OpenGraph 
- Support SiteMaps and SiteMaps Index
- Support images in SiteMap

## Installation

### Composer / Packagist

Require the package in your `composer.json`.

```
"vinicius73/seotools": "dev-master"
```

Run composer install or update to download the package.

```bash
$ composer update
```

### Providers

Just register the service provider and the facades in `app/config/app.php` and you are good to go.

```php
// Service provider
'Vinicius73\SEO\Providers\SEOServiceProvider',

// Facades (can customize if preferred)
'SEOMeta'     => 'Vinicius73\SEO\Facades\Meta',
'SEOSitemap'  => 'Vinicius73\SEO\Facades\Sitemap',
'OpenGraph'   => 'Vinicius73\SEO\Facades\OpenGraphHelper',
```

## Configuration
Run your terminal: `php artisan config:publish "vinicius73/seotools"`  
The configuration files are available from: `app/config/packages/vinicius73/seotools`

## Using
Using SEOTools is very easy and friendly.   
Recommend using the `barryvdh / laravel-ide-helper` that make it much easier to develop if you use an IDE like NetBeans or PhpStorm

### MetaGenerator e OpenGraph  

```php
class CommomController extends BaseController
{

	/**
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		SEOMeta::setTitle('Home');
        SEOMeta::setDescription('Isto é a minha descrição de página'); // is automatically limited to 160 characters
        OpenGraph::addImage('full-url-to-image-1.png');
        OpenGraph::addImage('full-url-to-image-2.png');
        
		$posts = Post::all();

        return View::make('myindex', compact('posts'));
	}
    
    /**
     * @return \Illuminate\View\View
	 */
    publicc function show($id)
    {
        $post = Post::find($id);
        
        SEOMeta::setTitle($post->title);
        SEOMeta::setDescription($post->resume);
        SEOMeta::addMeta('article:published_time', $post->published_date->toW3CString(), 'property');
        SEOMeta::addMeta('article:section', $post->category, 'property');
        // Vinicius73\SEO\Generators\MetaGenerator::addMeta($meta, $value, $name);
        SEOMeta::setKeywords($post->tags);
        // Vinicius73\SEO\Generators\MetaGenerator::setKeywords(['key1','key2','key3']);
        // Vinicius73\SEO\Generators\MetaGenerator::setKeywords('key1, key2, key3');
        OpenGraph::addImage($post->thumbnail_url);
        
        return View::make('myshow', compact('post'));
    }
}
```

### SiteMapGenerator
By default SiteMapGenerator [controller uses a model](https://github.com/vinicius73/SeoTools/blob/master/src/Vinicius73/SEO/SitemapRun.php) that aims to facilitate the creation of sitemaps.   
Its use is not mandatory and may be used freely quelquer route or controller, you can disable it in the configuration file. 

#### Criando controller para SiteMap
Change  `classrun` in `app/config/packages/vinicius73/seotools` -> `'classrun'  => 'SitemapRun',`   
You can map any class yours.

> Remember to map the additional sitemaps you create by `routes.php`

```php
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
    	return $this->index();
	}
    
    public function index()
    {
        $this->generator->addRaw(
    		array(
				  'location'         => '/sitemap-posts.xml',
				  'last_modified'    => '2013-12-28',
				  'change_frequency' => 'weekly',
				  'priority'         => '0.95'
			)
		);
        
        return $this->response($this->generator->generate());
    }
    
    public function posts()
    {
        $posts = Post::all();
        
        foreach($posts as $post)
        {
            $images = $post->images;
            
            $element = array(
        			  'location'         => route('route.to.post.show', $post->id),
    				  'last_modified'    => $post->published_date->toW3CString(),
    				  'change_frequency' => 'weekly',
    				  'priority'         => '0.90'
    			);
                
            if ($images):
    			$element['images'] = array();
				foreach ($images as $image):
					$element['images'][] = $image->url();
				endforeach;
			endif;
            
            $this->generator->addRaw($element);
        }
        
        return $this->response($this->generator->generate());
    }
    
    /**
     * @param $sitemap
	 *
	 * @return \Illuminate\Http\Response
	 */
	private function response($sitemap)
	{
		return Response::make($sitemap, 200, array('Content-Type' => 'text/xml'));
	}
}
```

### In Your View

```html
<html>
<head>
	{{SEOMeta::generate()}}
	{{OpenGraph::generate()}}
</head>
<body>

</body>
</html>
```

```html
<html>
<head>
	<title>Title | SubTitle</title>
	<meta name='description' itemprop='description' content='description...' />
	<meta name='keywords' content='key1, key2, key3' />
	<meta property='article:published_time' content='2014-01-31T20:30:11-02:00' />
	<meta property='article:section' content='news' />
	<meta property="og:title" content="title" />
	<meta property="og:description" content="description..." />
	<meta property="og:url" content="curent_or_custom_url" />
	<meta property="og:image" content="full_url_to_image.jpg" />
	<meta property="og:site_name" content="Site name from config" />
</head>
<body>

</body>
</html>
```

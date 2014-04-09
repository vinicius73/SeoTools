# Vinicius73 / SEOTools

[![Total Downloads](https://poser.pugx.org/vinicius73/seotools/downloads.png)](https://packagist.org/packages/vinicius73/seotools)

> **Atenção!** Este pacote ainda precisa ter seu teste otimizada.
----
> This package is a fork of https://github.com/Calotype/SEO

SEOTools é um pacote para **Laravel 4** que fornece helpers para algumas técnicas de SEO.

## Features

- Facilidade de definir títulos e meta tags 
- Interface amigável 
- Fácil instalação e custumização 
- Suporte a OpenGraph 
- Suporte a SiteMaps e SiteMaps Index 
- Suporte a Imagens em SiteMap

## Installation

### Composer / Packagist

Exigir que o pacote em seu `composer.json`.

```
"vinicius73/seotools": "dev-master"
```

Executar _composer install ou update_

```bash
$ composer update
```

### Providers
Basta registrar os providers e facades em `app/config/app.php`

```php
// Service provider
'Vinicius73\SEO\Providers\SEOServiceProvider',

// Facades (can customize if preferred)
'SEOMeta'     => 'Vinicius73\SEO\Facades\Meta',
'SEOSitemap'  => 'Vinicius73\SEO\Facades\Sitemap',
'OpenGraph'   => 'Vinicius73\SEO\Facades\OpenGraphHelper',
```

## Configuração
Execute no seu terminal : `php artisan config:publish "vinicius73/seotools"`  
Os arquivos de configuração estarão disponíveis em: `app/config/packages/vinicius73/seotools`

## Uso
Usar o SEOTools é muito fácil e amigável.  
Recomendo o uso do pacote `barryvdh/laravel-ide-helper` que fácilitara muito mais o desenvolvimento, se você usa um IDE como NetBeans ou PhpStorm

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
        SEOMeta::setDescription('Isto é a minha descrição de página'); // é altomáticamente limitada a 255 caracteres
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
Por padrão o SiteMapGenerator usa um [modelo de controller](https://github.com/vinicius73/SeoTools/blob/master/src/Vinicius73/SEO/SitemapRun.php) que visa facilitar a criação de sitemaps.  
Seu uso não é obrigatório, podendo ser usado livremente em quelquer rota ou controller, você pode desabilita-lo pelo arquivo de configuração.  

#### Criando controller para SiteMap
Mude a propriedade `classrun` em `app/config/packages/vinicius73/seotools` -> `'classrun'  => 'SitemapRun',`   
Você pode mapear qualquer classe sua.  
> Lembre-se de mapear os sitemaps adicionais que você criar, pelo `routes.php`

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
        			  'location'         => route('route.to.post.show',$post->id),
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
# fwk-cmf

Very simple CMF debuts...

## Installation

### 1: Install the sources

Via [Composer](http://getcomposer.org):

```
{
    "require": {
        "nitronet/fwk-cmf": "dev-master",
    }
}
```

If you don't use Composer, you can still [download](https://github.com/nitronet/fwk-cmf/zipball/master) this repository and add it
to your ```include_path``` [PSR-0 compatible](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)

### 2: Configure Plugin

index.php:
``` php
$app->plugin(new CmfPlugin(array(
    'config'    => __DIR__ .'/../app/site/site.yml'
),
array(
    new TwigFilesystemProvider(__DIR__ .'/../app/site', 'twig')
),
array(
    new \Nitronet\Fwk\CMF\Providers\Routes\ConfigRoutesProvider()
)));
```

## Contributions / Community

- Issues on Github: https://github.com/nitronet/fwk-cmf/issues
- Follow *Fwk* on Twitter: [@phpfwk](https://twitter.com/phpfwk)

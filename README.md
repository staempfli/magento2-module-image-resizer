# Magento 2 Image Resizer

Magento 2 Module to add simple image resizing capabilities in all blocks and .phtml templates

## Installation

```
$ composer require "staempfli/magento2-module-image-resizer":"~1.0"
```

## Usage

`ImageResizerHelper is automatically available in all frontend Blocks. 
You can resize your images just calling a method:

```php
/** @var \Staempfli\ImageResizer\Helper\Resizer $resizerHelper */
$resizerHelper = $block->getImageResizerHelper();
$resizedImageUrl = $resizerHelper->resizeAndGetUrl(<originalImageUrl>, $width, $height, [$resizeSettings]); 
```

You can do that directly on the .phtml or in your custom Block.

## Cache

Resized images are saved in cache to improve performance. That way, if an image was already resized, we just use the one in cache.

If you need to, you can clear the resized images cache on the Admin Cache Management

![Admin Clear Resized Images Cache](docs/img/admin-clear-cache.png "Clear Resized Images Cache")

## Prerequisites

- PHP >= 7.0.*
- Magento >= 2.1.*

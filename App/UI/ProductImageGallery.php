<?php

namespace App\UI;

/**
 * Product Image Gallery
 *
 * @link https://get.foundation/building-blocks/blocks/ecommerce-product-image-gallery.html
 */
class ProductImageGallery extends \PHPFUI\HTML5Element
	{
	private ?\PHPFUI\Image $mainImage = null;

	private readonly \PHPFUI\UnorderedList $ul;

	public function __construct(private readonly \PHPFUI\Interfaces\Page $page)
		{
		parent::__construct('div');
		$this->addClass('row align-center');
		$gallery = new \PHPFUI\HTML5Element('div');
		$gallery->addClass('product-image-gallery');
		$this->mainImage = new \PHPFUI\Image('', '');
		$this->mainImage->addClass('pdp-product-image');
		$js = '$(".sim-thumb").on("click",function(){$("#' . $this->mainImage->getId() . '").attr("src",$(this).data("image"));})';
		$this->page->addJavaScript($js);
		$gallery->add($this->mainImage);
		$gallery->add('<br>');
		$this->ul = new \PHPFUI\UnorderedList();
		$this->ul->addClass('menu product-thumbs align-center');
		$gallery->add($this->ul);
		$this->add($gallery);
		}

	public function addImages(\PHPFUI\Image $image, \PHPFUI\Image $thumb) : static
		{
		if (empty($this->mainImage->getAttribute('src')))
			{
			$this->mainImage->setAttribute('src', $image->getAttribute('src'));
			$this->mainImage->setAttribute('alt', $image->getAttribute('alt'));
			}

		$link = new \PHPFUI\HTML5Element('a');
		$link->addClass('sim-thumb');
		$link->addAttribute('data-image', $image->getAttribute('src'));
		$link->add($thumb);
		$this->ul->addItem(new \PHPFUI\ListItem($link));

		return $this;
		}
	}

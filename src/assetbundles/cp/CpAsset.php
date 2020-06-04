<?php

namespace Masuga\CpFilters\assetbundles\cp;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

class CpAsset extends AssetBundle
{

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->sourcePath = "@Masuga/CpFilters/assetbundles/cp/dist";

		$this->depends = [
			CraftCpAsset::class,
		];

		$this->js = [
			'js/cp.js',
		];

		$this->css = [
			'css/cp.css'
		];

		parent::init();
	}
}

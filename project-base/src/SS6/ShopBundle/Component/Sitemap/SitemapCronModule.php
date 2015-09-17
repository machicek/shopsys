<?php

namespace SS6\ShopBundle\Component\Sitemap;

use SS6\ShopBundle\Component\Cron\CronModuleInterface;
use SS6\ShopBundle\Component\Sitemap\SitemapFacade;
use Symfony\Bridge\Monolog\Logger;

class SitemapCronModule implements CronModuleInterface {

	/**
	 * @var \SS6\ShopBundle\Component\Sitemap\SitemapFacade
	 */
	private $sitemapFacade;

	public function __construct(SitemapFacade $sitemapFacade) {
		$this->sitemapFacade = $sitemapFacade;
	}

	/**
	 * @param \Symfony\Bridge\Monolog\Logger $logger
	 */
	public function run(Logger $logger) {
		$this->sitemapFacade->generateForAllDomains();
	}

}
<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tracy;

use Nette;
use Tracy;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Panel implements Tracy\IBarPanel
{
	use Nette\SmartObject;

	/** @var Translette\Translation\Translator|null */
	private $translator;

	/** @var array */
	private $localeResolvers = [];

	/** @var array */
	private $resources = [];

	/** @var int */
	private $resourcesCount = 0;

	/** @var array */
	private $ignoredResources = [];

	/** @var int */
	private $ignoredResourcesCount = 0;


	/**
	 * @param Translette\Translation\Translator $translator
	 */
	public function __construct(Translette\Translation\Translator $translator)
	{
		$this->translator = $translator;
		$translator->setTracyPanel($this);
	}


	/**
	 * @return string|null
	 */
	public function getTab(): ?string
	{
		// https://www.flaticon.com/free-icon/book_1017764
		$icon = '<?xml version="1.0" encoding="iso-8859-1"?><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><g><path d="M501.871,62.479c-2.332-0.831-57.934-20.278-125.48-20.278c-56.677,0-104.959,13.698-120.391,18.585 c-15.432-4.887-63.714-18.585-120.391-18.585c-67.546,0-123.148,19.447-125.48,20.278C4.056,64.628,0,70.367,0,76.815v311.166 c0,4.938,2.413,9.571,6.448,12.42c7.45,5.26,13.804,1.796,20.998-0.375v54.163c0,4.532,2.028,8.831,5.516,11.721 c3.498,2.89,8.101,4.066,12.552,3.214l160.49-30.721c7.056,15.508,23.306,27.93,41.093,30.366 c5.625,2.881,42.289,1.767,58.097-31.066c178.589,33.339,168.078,31.695,170.711,31.695c8.408,0,15.208-6.834,15.208-15.208 v-52.074c11.476,3.94,20.886-4.377,20.886-14.134V76.815C512,70.367,507.944,64.628,501.871,62.479z M200.864,408.422 L57.863,435.797v-43.496c51.077-10.798,95.647-10.936,143.001-2.443V408.422z M30.417,367.531c0-17.386,0-263.381,0-279.553 c69.323-20.127,139.327-20.582,210.374,0.01c0,17.059,0,270.255,0,279.543C171.804,349.193,102.75,348.195,30.417,367.531z M279.573,418.541c-0.261,2.61-1.624,5.61-2.798,7.959c-11.781,20.641-41.827,14.207-45.453-8 c-0.076-0.689-0.018-5.622-0.041-21.971c20.028,5.214,22.582,8.342,29.829,5.779c0.103,0,6.14-2.236,18.463-5.475V418.541z M460.697,435.868L309.99,407.661V390.07c50.108-9.15,97.329-8.54,150.706,3.68V435.868z M481.583,367.531 c-73.139-19.482-142.454-18.188-210.374,0c0-9.356,0-262.543,0-279.553c72.197-20.977,141.968-19.769,210.374,0.01 C481.583,97.901,481.583,356.24,481.583,367.531z"/></g></g><g><g><path d="M431.314,121.336c-58.098-20.853-106.991-0.819-109.359-0.162c-7.99,2.565-12.39,11.133-9.825,19.132 c2.575,8,11.133,12.4,19.132,9.825c56.487-18.099,85.718,0.74,94.911,0.74c6.236,0,12.086-3.873,14.316-10.078 C443.344,132.863,439.184,124.154,431.314,121.336z"/></g></g><g><g><path d="M431.316,204.467c-59.056-21.206-106.523-0.608-109.354-0.166c-7.997,2.568-12.398,11.133-9.829,19.13 c2.568,7.998,11.132,12.399,19.13,9.829c2.404-0.381,41.661-17.442,89.774-0.166c7.872,2.827,16.604-1.238,19.454-9.174 C443.327,216.016,439.22,207.306,431.316,204.467z"/></g></g><g><g><path d="M431.316,287.595c-59.058-21.205-106.48-0.614-109.354-0.166c-7.997,2.568-12.398,11.133-9.829,19.13 c2.568,7.997,11.132,12.399,19.13,9.829c2.413-0.382,41.6-17.466,89.774-0.166c7.888,2.83,16.609-1.251,19.454-9.174 C443.327,299.144,439.22,290.433,431.316,287.595z"/></g></g><g><g><path d="M190.532,121.336c-32.671-11.734-69.825-12.862-104.361-1.764c-2.138,1.099-11.554,1.666-14.783,11.264 c-3.874,11.704,7.159,23.136,19.092,19.295c2.445-0.388,41.592-17.467,89.771-0.162c7.956,2.856,16.633-1.312,19.447-9.176 C202.537,132.884,198.431,124.175,190.532,121.336z"/></g></g><g><g><path d="M190.528,204.467c-59.056-21.206-106.523-0.608-109.354-0.166c-7.997,2.568-12.398,11.133-9.829,19.13 c2.568,7.998,11.131,12.399,19.13,9.829c2.407-0.381,41.661-17.442,89.774-0.166c7.872,2.827,16.604-1.238,19.454-9.174 C202.541,216.016,198.434,207.306,190.528,204.467z"/></g></g><g><g><path d="M190.528,287.595c-59.058-21.205-106.48-0.614-109.354-0.166c-7.997,2.568-12.398,11.133-9.829,19.13 c2.568,7.997,11.131,12.399,19.13,9.829c2.468-0.391,41.599-17.466,89.774-0.166c7.888,2.83,16.609-1.251,19.454-9.174 C202.541,299.144,198.434,290.433,190.528,287.595z"/></g></g></svg>';
		return '<span title="Translete/Translation">' . $icon  . ($this->translator->locale !== null ? ' <strong>' . $this->translator->locale . '</strong>' : null) . '</span>';
	}


	/**
	 * @return string|null
	 */
	public function getPanel(): ?string
	{
		$panel = [];


		$panel[] = '<h1>Translator settings</h1>';
		$panel[] = '<table style="width: 100%"><tr></th><th>Name</th><th>Value</th></tr>';
		$panel[] = '<tr><td>Default locale</td><td >' . $this->translator->defaultLocale . '</td></tr>';
		$panel[] = '<tr><td>Locales whitelist</td><td >' . implode(', ', $this->translator->localesWhitelist) . '</td></tr>';
		$panel[] = '</table>';


		// locale resolvers
		if (count($this->localeResolvers) > 0) {
			$panel[] = '<br><br>';
			$panel[] = '<h1>Locale resolvers</h1>';
			$panel[] = '<table style="width: 100%"><tr><th>#</th></th><th>Resolver</th><th>Locale</th></tr>';

			$counter = 1;
			foreach ($this->localeResolvers as $v1) {
				$reflection = new \ReflectionClass($v1);
				$locale = $v1->resolve($this->translator);

				$panel[] = '<tr><td>' . $counter++ . '.</td><td title="' . $reflection->getName() . '">' . $reflection->getShortName() . '</td><td>' . ($locale !== null ? $locale : '<i>n/a</i>') . '</td></tr>';
			}

			$panel[] = '</table>';
		}


		// resources
		if (count($this->resources) > 0) {
			ksort($this->resources);

			$panel[] = '<br><br>';
			$panel[] = '<h1>Loaded resources: ' . $this->resourcesCount . '</h1>';
			$panel[] = self::createResourcePanelHelper($this->resources);
		}


		// ignored resources
		if (count($this->ignoredResources) > 0) {
			ksort($this->ignoredResources);

			$panel[] = '<br><br>';
			$panel[] = '<h1>Ignored resources: ' . $this->ignoredResourcesCount . ', <small>whitelist: ' . implode(', ', array_map('htmlspecialchars', $this->translator->localesWhitelist)) . '</small></h1>';
			$panel[] = self::createResourcePanelHelper($this->ignoredResources);
		}


		return count($panel) === 0 ? null : implode($panel);
	}


	/**
	 * @internal
	 *
	 * @param array $resources
	 * @return string
	 */
	private static function createResourcePanelHelper(array $resources): string
	{
		$string = '';

		foreach ($resources as $k1 => $v1) {
			foreach ($v1 as $k2 => $v2) {
				$string .= '<tr>';
				$string .= '<td>' . htmlspecialchars($k1) . '</td>';
				$string .= '<td>' . htmlspecialchars($v2) . '</td>';
				$string .= '<td>' . htmlspecialchars(dirname($k2)) . '/<strong>' . htmlspecialchars(basename($k2)) . '</strong></td>';
				$string .= '</tr>';
			}
		}

		return '<table style="width: 100%"><tr><th>Locale</th><th>Domain</th><th>Resource file name</th></tr>' . $string . '</table>';
	}


	/**
	 * @param Translette\Translation\LocalesResolvers\ResolverInterface $resolver
	 * @return self
	 */
	public function addLocaleResolver(Translette\Translation\LocalesResolvers\ResolverInterface $resolver): self
	{
		$this->localeResolvers[] = $resolver;
		return $this;
	}


	/**
	 * @param string|null $format
	 * @param string|array|null $resource
	 * @param string|null $locale
	 * @param string|null $domain
	 * @return self
	 */
	public function addResource(?string $format, $resource, ?string $locale, ?string $domain): self
	{
		if (is_array($resource)) {
			$resource = 'array ' . md5(serialize($resource));
		}

		$this->resources[$locale][$resource] = $domain;
		$this->resourcesCount++;
		return $this;
	}


	/**
	 * @param string|null $format
	 * @param string|array|null $resource
	 * @param string|null $locale
	 * @param string|null $domain
	 * @return self
	 */
	public function addIgnoredResource(?string $format, $resource, ?string $locale, ?string $domain): self
	{
		if (is_array($resource)) {
			$resource = 'array ' . md5(serialize($resource));
		}

		$this->ignoredResources[$locale][$resource] = $domain;
		$this->ignoredResourcesCount++;
		return $this;
	}
}

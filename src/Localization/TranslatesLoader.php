<?php declare(strict_types = 1);

namespace Smartsupp\Localization;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class TranslatesLoader
{

	/** @var bool */
	private $debugMode;

	/** @var ITranslateStorage */
	private $storage;

	/** @var Cache */
	private $cache;


	public function __construct(
		bool $debugMode,
		ITranslateStorage $storage,
		IStorage $cacheStorage
	)
	{
		$this->debugMode = $debugMode;
		$this->storage = $storage;
		$this->cache = new Cache($cacheStorage, 'Smartsupp.TranslatesLoader');
	}


	public function loadTranslates(string $section, string $lang, ?string $defaultLang = null): array
	{
		$keyParts = [$lang, $section];
		if ($this->debugMode) {
			$keyParts[] = $this->getLastChange($section, $lang, $defaultLang);
		}

		$key = \implode('_', $keyParts);
		return $this->cache->load($key, function () use ($section, $lang, $defaultLang): array {
			return $this->getTranslates($section, $lang, $defaultLang);
		});
	}


	private function getTranslates(string $section, string $lang, ?string $defaultLang = null): array
	{
		$translates = $this->storage->getTranslates($section, $lang);
		if ($defaultLang && $defaultLang !== $lang) {
			$translates = \array_merge($this->storage->getTranslates($section, $defaultLang), $translates);
		}
		\ksort($translates);
		return $translates;
	}


	private function getLastChange(string $section, string $lang, ?string $defaultLang = null): int
	{
		if ($defaultLang !== null) {
			return \max($this->storage->getLastChange($section, $lang), $this->storage->getLastChange($section, $defaultLang));
		}

		return $this->storage->getLastChange($section, $lang);
	}

}

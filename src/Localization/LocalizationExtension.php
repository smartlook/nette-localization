<?php declare(strict_types = 1);

namespace Smartsupp\Localization;

use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class LocalizationExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'translatesDir' => Expect::string(),
			'debugMode' => Expect::bool('%debugMode%'),
			'sections' => Expect::array(),
			'alias' => Expect::array(),
			'parameters' => Expect::array(),
			'filters' => Expect::array(),
		])->castTo('array');
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$this->config = Helpers::expand($this->config, $this->getContainerBuilder()->parameters);

		$builder->addDefinition($this->prefix('translatesStorage'))
			->setFactory(DirectoryStorage::class, [$this->config['translatesDir']]);

		$builder->addDefinition($this->prefix('translatesLoader'))
			->setFactory(TranslatesLoader::class, [$this->config['debugMode']]);

		$translatorFactory = $builder->addDefinition($this->prefix('translatorFactory'))
			->setFactory(TranslatorFactory::class)
			->addSetup('$defaultSections', [$this->config['sections']])
			->addSetup('setParameters', [$this->config['parameters']]);

		if (\count($this->config['alias'])) {
			foreach ($this->config['alias'] as $from => $to) {
				$translatorFactory->addSetup('setAlias', [$from, $to]);
			}
		}

		if (\count($this->config['filters'])) {
			foreach ($this->config['filters'] as $filter) {
				$translatorFactory->addSetup('addFilter', [$filter]);
			}
		}
	}

}

<?php declare(strict_types = 1);

namespace Smartsupp\Localization;

class DirectoryStorage implements ITranslateStorage
{

	/** @var string */
	private $dir;


	public function __construct(string $dir)
	{
		$this->dir = $dir;
	}


	public function getTranslates(string $section, string $lang): array
	{
		$path = $this->dir . '/' . $section . '/' . $lang . '.json';
		if (!\is_file($path)) {
			return [];
		}

		$translates = [];
		$data = \json_decode(\file_get_contents($path), true);
		$data = \array_filter($data, function ($value) {
			return $value !== '';
		});
		$this->expandKeys($translates, $data);
		return $translates;
	}


	public function getLastChange(string $section, string $lang): int
	{
		$path = $this->dir . '/' . $section . '/' . $lang . '.json';
		return \is_file($path) ? \filemtime($path) : 0;
	}


	/**
	 * Expand structured translates
	 */
	private function expandKeys(array &$translates, array $data, ?string $prefix = null): void
	{
		foreach ($data as $key => $value) {
			if (\is_array($value)) {
				$this->expandKeys($translates, $value, $prefix ? "$prefix.$key" : $key);
			} else {
				$translates[$prefix ? "$prefix.$key" : $key] = $value;
			}
		}
	}

}

<?php declare(strict_types = 1);

namespace Smartsupp\Localization;

use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

class Translator implements ITranslator
{

	/** @var array  translation table */
	private $dictionary = [];

	/** @var array */
	private $parameters = [];

	/** @var array */
	private $filters = [];


	public function setTranslates(array $dictionary): void
	{
		$this->dictionary = $dictionary;
	}


	/**
	 * @return string[]
	 */
	public function getTranslates(): array
	{
		return $this->dictionary;
	}


	public function setParameters(array $parameters): void
	{
		$this->parameters = array_merge($this->parameters, $parameters);
	}


	public function getParameters(): array
	{
		return $this->parameters;
	}


	public function addFilter(callable $filter): void
	{
		if (!Validators::isCallable($filter)) {
			throw new AssertionException("Filter is not callable");
		}
		$this->filters[] = $filter;
	}


	public function hasMessage(string $key): bool
	{
		return isset($this->dictionary[$key]);
	}


	/**
	 * Translates the given string. NEPODPORUJE PLURAL
	 * @param  string $key translation string
	 * @param  mixed $args argument (first of arguments)
	 * @return string
	 */
	public function translate($key, $args = null)
	{
		if (isset($this->dictionary[$key])) {
			$message = $this->dictionary[$key];
		} elseif (preg_match('/^([A-Za-z]\w+\.)+\w+$/', $key)) {
			$message = '|' . $key . '|';
		} else {
			$message = $key;
		}

		if ($args !== null) {
			if (is_array($args)) {
				$message = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($args) {
					return array_key_exists($matches[1], $args) ? $args[$matches[1]] : $matches[0];
				}, $message);
			} else {
				$args = func_get_args();
				array_shift($args);
				$message = vsprintf($message, $args);
			}
		}

		if (count($this->parameters) > 0) {
			$message = $this->applyParameters($message);
		}

		foreach ($this->filters as $filter) {
			$message = call_user_func_array($filter, [$message, $key]);
		}

		return $message;
	}


	private function applyParameters(string $string): string
	{
		if (strpos($string, '{') === false) {
			return $string;
		}
		return preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
			return isset($this->parameters[$matches[1]]) ? $this->applyParameters($this->parameters[$matches[1]]) : $matches[0];
		}, $string);
	}

}

<?php
namespace Core;

class Storage
{
	private $instances = [];
	private $config;


	/**
	 * @param string $keyName
	 * @param object | \Closure | array $data
	 */
	public function setShared(string $keyName, $data) : void
	{
		if (is_object($data)) {
			$this->instances[$keyName] = $data;
		}

		if (is_callable($data)) {
			$this->instances[$keyName] = $data();
		}

		if (is_array($data)) {

			if (!empty($data['arguments'])) {

                $arguments = [];

				foreach ($data['arguments'] as $params) {
					$arguments[] = $this->get($params['name']);
				}

				$this->instances[$keyName] = new $data['className'](...$arguments);
			} else {
				$this->instances[$keyName] = new $data['className'];
			}
		}
	}

	/**
	 * @param string $className
	 * @return object|null
	 */
	public function get(string $className): ?object
	{
		if (is_callable($this->instances[$className])) {
			return $this->instances[$className]();
		}

		return $this->instances[$className];
	}

	/**
	 * Configuration data of site
	 * @param array $config
	 */
	public function setConfig(array $config) : void
	{
		$this->config = $config;
	}

	/**
	 * @param string $keyName
	 * @return mixed
	 */
	public function getConfig(string $keyName)
	{
		return $this->config[$keyName];
	}
}

<?php

namespace Fusio;

use PSX\Data\RecordInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TriggerAbstract implements TriggerInterface
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getParameters()
	{
		return array();
	}

	abstract public function execute(RecordInterface $record, array $parameters, Context $Context);
}
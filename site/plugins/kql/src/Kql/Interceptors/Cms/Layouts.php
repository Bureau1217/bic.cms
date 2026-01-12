<?php

namespace Kirby\Kql\Interceptors\Cms;

class Layouts extends Collection
{
	public const CLASS_ALIAS = 'layouts';

	public function allowedMethods(): array
	{
		return array_merge(
			parent::allowedMethods(),
			[
				'toBlocks'
			]
		);
	}

	public function toArray(): array
	{
		return $this->object->toArray();
	}
}

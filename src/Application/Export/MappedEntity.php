<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseExport\Application\Export;

/**
 * @psalm-immutable
 */
class MappedEntity {

	/**
	 * @param MappedStatement[] $statements
	 */
	public function __construct(
		public /* readonly */ string $id,
		public /* readonly */ array $statements
	) {
	}

}
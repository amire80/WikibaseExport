<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseExport\Application;

use Wikibase\DataModel\Entity\DispatchingEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;

class PropertyIdListParser {

	private EntityIdParser $idParser;

	public function __construct() {
		$this->idParser = $this->newPropertyIdParser();
	}

	private function newPropertyIdParser(): EntityIdParser {
		return new DispatchingEntityIdParser( [
			PropertyId::PATTERN => static function( string $serialization ) {
				return new NumericPropertyId( $serialization );
			},
		] );
	}

	/**
	 * Parses a list of strings to PropertyId objects,
	 * silently dropping everything that is not a valid property id.
	 *
	 * @param string[] $idStrings
	 * @return array<int, PropertyId>
	 * @throws EntityIdParsingException
	 */
	public function parse( array $idStrings ): array {
		$ids = [];

		foreach ( $idStrings as $idString ) {
			try {
				$id = $this->idParser->parse( $idString );

				if ( $id instanceof PropertyId ) {
					$ids[] = $id;
				}
			}
			catch ( EntityIdParsingException ) {
			}
		}

		return $ids;
	}

}

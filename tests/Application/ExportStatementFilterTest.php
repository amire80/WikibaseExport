<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseExport\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseExport\Application\ExportStatementFilter;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;

/**
 * @covers \ProfessionalWiki\WikibaseExport\Application\ExportStatementFilter
 */
class ExportStatementFilterTest extends TestCase {

	public function testMatchingStatement(): void {
		$filter = new ExportStatementFilter(
			[ new NumericPropertyId( 'P1' ), new NumericPropertyId( 'P2' ) ],
			TimeQualifierStatementFilterTest::newJan2000ToDec2005(),
			TimeQualifierStatementFilterTest::newTimeQualifierProperties()
		);

		$statement = new Statement(
			mainSnak: new PropertyNoValueSnak( new NumericPropertyId( 'P2' ) ),
			qualifiers: new SnakList( [
				new PropertyValueSnak(
					new NumericPropertyId( TimeQualifierStatementFilterTest::POINT_IN_TIME_ID ),
					TimeQualifierStatementFilterTest::newDay( '+2001-01-01T00:00:00Z' )
				)
			] )
		);

		$this->assertTrue( $filter->statementMatches( $statement ) );
	}

}

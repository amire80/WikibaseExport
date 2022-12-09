<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseExport\EntryPoints;

use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseExport\Application\Export\ExportRequest;
use ProfessionalWiki\WikibaseExport\Application\PropertyIdListParser;
use ProfessionalWiki\WikibaseExport\Presentation\HttpExportPresenter;
use ProfessionalWiki\WikibaseExport\Presentation\WideCsvPresenter;
use ProfessionalWiki\WikibaseExport\WikibaseExportExtension;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikimedia\ParamValidator\ParamValidator;

class ExportApi extends SimpleHandler {

	private const PARAM_SUBJECT_IDS = 'subject_ids';
	private const PARAM_GROUPED_STATEMENT_PROPERTY_IDS = 'grouped_statement_property_ids';
	private const PARAM_UNGROUPED_STATEMENT_PROPERTY_IDS = 'ungrouped_statement_property_ids';
	private const PARAM_START_YEAR = 'start_year';
	private const PARAM_END_YEAR = 'end_year';

	public function run(): Response {
		$presenter = $this->newHttpPresenter();

		$exporter = WikibaseExportExtension::getInstance()->newExportUseCase( $presenter, $this->getAuthority() );

		$exporter->export( $this->buildExportRequest() );

		return $presenter->getResponse();
	}

	private function newWideCsvPresenter(): WideCsvPresenter {
		return new WideCsvPresenter();
	}

	private function newHttpPresenter(): HttpExportPresenter {
		return new HttpExportPresenter(
			presenter: $this->newWideCsvPresenter(),
			responseFactory: $this->getResponseFactory()
		);
	}

	private function buildExportRequest(): ExportRequest {
		$params = $this->getValidatedParams();

		return new ExportRequest(
			languageCode: MediaWikiServices::getInstance()->getContentLanguage()->getCode(), // TODO: get from instance
			subjectIds: $this->parseIds( $params[self::PARAM_SUBJECT_IDS] ),
			useLabelsInHeaders: true, // TODO
			groupedStatementPropertyIds: ( new PropertyIdListParser() )->parse( $params[self::PARAM_GROUPED_STATEMENT_PROPERTY_IDS] ),
			ungroupedStatementPropertyIds: ( new PropertyIdListParser() )->parse( $params[self::PARAM_UNGROUPED_STATEMENT_PROPERTY_IDS] ),
			startYear: (int)$params[self::PARAM_START_YEAR],
			endYear: (int)$params[self::PARAM_END_YEAR]
		);
	}

	/**
	 * @param string[] $ids
	 * @return EntityId[]
	 */
	private function parseIds( array $ids ): array {
		$idObjects = [];

		$parser = new BasicEntityIdParser();

		foreach ( $ids as $id ) {
			try {
				$idObjects[] = $parser->parse( $id );
			}
			catch ( EntityIdParsingException ) {
			}
		}

		return $idObjects;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function getParamSettings(): array {
		return [
			self::PARAM_SUBJECT_IDS => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_ISMULTI_LIMIT1 => 256,
				ParamValidator::PARAM_ISMULTI_LIMIT2 => 1024,
			],
			self::PARAM_GROUPED_STATEMENT_PROPERTY_IDS => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_ISMULTI_LIMIT1 => 256,
				ParamValidator::PARAM_ISMULTI_LIMIT2 => 1024,
			],
			self::PARAM_START_YEAR => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => false,
			],
			self::PARAM_END_YEAR => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => false,
			],
			self::PARAM_UNGROUPED_STATEMENT_PROPERTY_IDS => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_ISMULTI_LIMIT1 => 256,
				ParamValidator::PARAM_ISMULTI_LIMIT2 => 1024,
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function needsWriteAccess() {
		return false;
	}

}

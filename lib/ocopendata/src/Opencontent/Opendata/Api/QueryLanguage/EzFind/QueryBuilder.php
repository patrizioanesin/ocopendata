<?php

namespace Opencontent\Opendata\Api\QueryLanguage\EzFind;

use Opencontent\Opendata\Api\ClassRepository;
use Opencontent\QueryLanguage\Parser\TokenFactory;
use Opencontent\QueryLanguage\QueryBuilder as BaseQueryBuilder;


class QueryBuilder extends BaseQueryBuilder
{
    public $fields = array(
        'q'
    );

    public $metaFields = array(
        'id',
        'remote_id',
        'name',
        'published',
        'modified',
        'section',
        'state',
    );

    public $parameters = array(
        'sort',
        'limit',
        'offset',
        'classes',
        'subtree'
    );

    public $operators = array(
        '=',
        '!=',
        'in',
        '!in',
        'contains',
        '!contains',
        'range',
        '!range'
    );

    public function __construct()
    {
        $classRepository = new ClassRepository();
        $availableFieldDefinitions = $classRepository->listAttributesGroupedByIdentifier();

//        echo '<pre>';
//        print_r( $availableFieldDefinitions );
//        die();

        $this->fields = array_merge(
            $this->fields,
            $this->metaFields,
            array_keys( $availableFieldDefinitions )
        );

        $sentenceConverter = new SentenceConverter(
            $availableFieldDefinitions,
            $this->metaFields
        );

        $parameterConverter = new ParameterConverter(
            $availableFieldDefinitions,
            $this->metaFields
        );

        $this->converter = new QueryConverter(
            $sentenceConverter,
            $parameterConverter
        );

        $this->tokenFactory = new TokenFactory(
            $this->fields,
            $this->operators,
            $this->parameters,
            $this->clauses
        );
    }

}
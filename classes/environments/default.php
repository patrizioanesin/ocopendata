<?php

use Opencontent\Opendata\Api\EnvironmentSettings;
use Opencontent\Opendata\Api\Values\Content;
use Opencontent\Opendata\Api\Values\ContentData;
use Opencontent\Opendata\Api\EnvironmentLoader;

class DefaultEnvironmentSettings extends EnvironmentSettings
{
    public function filterContent( Content $content )
    {
        $this->blockBlackListedContent( $content );
        $content = $this->removeBlackListedAttributes( $content );
        $content = $this->overrideIdentifier( $content );
        $content = $this->flatData( $content );
        $content = $this->filterMetaData( $content );
        return parent::filterContent( $content );
    }

    protected function filterMetaData( Content $content )
    {
        $parentNodes = array();
        foreach( $content->metadata->parentNodes as $parentNode )
        {
            $parentNodes[] = $parentNode['id'];
        }

        $content->metadata = new ContentData(
            array(
                'id' => $content->metadata->id,
                'remoteId' => $content->metadata->remoteId,
                'classIdentifier' => $content->metadata->classIdentifier,
                'sectionIdentifier' => $content->metadata->sectionIdentifier,
                'stateIdentifiers' => $content->metadata->stateIdentifiers,
                'published' => $content->metadata->published,
                'modified' => $content->metadata->modified,
                'parentNodes' => $parentNodes
            )
        );
        return $content;
    }

    protected function flatData( Content $content )
    {
        $flatData = array();
        foreach( $content->data as $language => $data )
        {
            foreach( $data as $identifier => $value )
            {
                $flatData[$language][$identifier] = $value['content'];
            }
        }
        $content->data = new ContentData( $flatData );
        return $content;
    }

    protected function overrideIdentifier( Content $content )
    {
        $overrideIdentifierSettings = (array) EnvironmentLoader::ini()->variable( 'ContentSettings', 'OverrideFieldIdentifierList' );
        $overrideIdentifierList = array();
        foreach( $overrideIdentifierSettings as $overrideIdentifierItem )
        {
            list( $old, $new ) = explode( ';', $overrideIdentifierItem );
            $overrideIdentifierList[$old] = $new;
        }
        $cleanData = array();
        foreach( $content->data as $language => $data )
        {
            foreach( $data as $identifier => $value )
            {
                list( $classIdentifier, $attributeIdentifier ) = explode( '/', $value['identifier'] );
                if ( isset( $overrideIdentifierList[$value['identifier']] ) )
                {
                    $identifier = $overrideIdentifierList[$value['identifier']];
                }
                elseif ( isset( $overrideIdentifierList[$attributeIdentifier] ) )
                {
                    $identifier = $overrideIdentifierList[$attributeIdentifier];
                }
                $cleanData[$language][$identifier] = $value;
            }
        }
        $content->data = new ContentData( $cleanData );
        return $content;
    }

    protected function removeBlackListedAttributes( Content $content )
    {
        $identifierBlackList = (array) EnvironmentLoader::ini()->variable( 'ContentSettings', 'IdentifierBlackListForExternal' );
        $datatypeBlackList = (array) EnvironmentLoader::ini()->variable( 'ContentSettings', 'DatatypeBlackListForExternal' );
        $cleanData = array();
        foreach( $content->data as $language => $data )
        {
            foreach( $data as $identifier => $value )
            {
                list( $classIdentifier, $attributeIdentifier ) = explode( '/', $value['identifier'] );
                if ( !in_array( $value['datatype'], $datatypeBlackList )
                     && !in_array( $attributeIdentifier, $identifierBlackList )
                     && !in_array( $value['identifier'], $identifierBlackList ) )
                    $cleanData[$language][$identifier] = $value;
            }
        }
        $content->data = new ContentData( $cleanData );
        return $content;
    }

    protected function blockBlackListedContent( Content $content )
    {
        $classIdentifierBlackList = (array) EnvironmentLoader::ini()->variable( 'ContentSettings', 'ClassIdentifierBlackListForExternal' );
        if ( in_array( $content->metadata->classIdentifier, $classIdentifierBlackList ) )
            throw new \Opencontent\Opendata\Api\Exception\ForbiddenException( $content->metadata->classIdentifier, 'read' );

    }
}
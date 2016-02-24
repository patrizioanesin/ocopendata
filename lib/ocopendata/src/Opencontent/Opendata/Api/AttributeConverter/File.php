<?php

namespace Opencontent\Opendata\Api\AttributeConverter;

use eZContentObjectAttribute;
use eZContentClassAttribute;
use eZSys;
use eZFile;
use eZHTTPTool;
use eZURI;
use eZDir;
use Opencontent\Opendata\Api\Exception\InvalidInputException;
use Opencontent\Opendata\Api\PublicationProcess;

class File extends Base
{
    public function get( eZContentObjectAttribute $attribute )
    {
        $content = parent::get( $attribute );
        if ( $attribute instanceof eZContentObjectAttribute
             && $attribute->hasContent()
        )
        {
            /** @var \eZBinaryFile $file */
            $file = $attribute->content();

            $url = 'content/download/' . $attribute->attribute( 'contentobject_id' )
                   . '/' . $attribute->attribute( 'id' )
                   . '/' . $attribute->attribute( 'version' )
                   . '/' . $file->attribute( 'original_filename' );
            eZURI::transformURI( $url, false, 'full' );

            $content['content'] = array(
                'filename' => $file->attribute( 'original_filename' ),
                'url' => $url
            );
        }

        return $content;
    }

    public function set( $data, PublicationProcess $process )
    {
        return $this->getTemporaryFilePath( $data['filename'], $data['url'], $data['file'] );
    }

    public static function validate( $identifier, $data, eZContentClassAttribute $attribute )
    {
        if ( is_array( $data ) )
        {
            if ( !isset( $data['filename'] ) )
            {
                throw new InvalidInputException( 'Missing filename', $identifier, $data );
            }

            if ( isset( $data['url'] ) && !eZHTTPTool::getDataByURL( trim( $data['url'] ), true ) )
            {
                throw new InvalidInputException( 'Url not responding', $identifier, $data );
            }

            if ( isset( $data['file'] )
                 && !( base64_encode( base64_decode( $data['file'], true ) ) === $data['file'] )
            )
            {
                throw new InvalidInputException( 'Invalid base64 encoding', $identifier, $data );
            }

            if ( !isset( $data['url'] ) )
            {
                $data['url'] = null;
            }

            if ( !isset( $data['file'] ) )
            {
                $data['file'] = null;
            }

        }
        throw new InvalidInputException( 'Invalid data format', $identifier, $data );
    }

    public function type( \eZContentClassAttribute $attribute )
    {
        return array(
            'identifier' => 'file',
            'format' => array(
                'url' => 'public http uri',
                'file' => 'base64 encoded file (url alternative)',
                'filename' => 'string'
            )
        );
    }

    protected function getTemporaryFilePath( $filename, $url = null, $fileEncoded = null )
    {
        $data = null;
        if ( $url !== null )
        {
            $binary = eZHTTPTool::getDataByURL( $url );
            eZFile::create( $filename, self::tempDir(), $binary );
            $data = self::tempDir() . $filename;
        }
        elseif ( $fileEncoded !== null )
        {
            $binary = base64_decode( $fileEncoded );
            eZFile::create( $filename, self::tempDir(), $binary );
            $data = self::tempDir() . $filename;
        }

        return $data;
    }

    public static function clean()
    {
        eZDir::recursiveDelete( self::tempDir() );
    }

    protected static function tempDir()
    {
        //return sys_get_temp_dir()  . eZSys::fileSeparator();
        return eZDir::path( array( eZSys::cacheDirectory(), 'tmp' ), true );
    }

}
<?php

namespace Opencontent\Ckan\DatiTrentinoIt\DatasetGenerator;

use OcOpendataDatasetGeneratorInterface;
use eZContentObjectTreeNode;
use eZContentFunctions;
use OCOpenDataTools;
use eZContentObject;
use Opencontent\Opendata\Api\ContentSearch;
use Opencontent\Opendata\Api\EnvironmentLoader;
use eZINI;
use eZUser;
use Exception;
use eZContentClass;

class OpenPA implements OcOpendataDatasetGeneratorInterface
{

    public static $remoteIds = array(
        'area' => 'opendata_area',
        'container' => 'opendata_datasetcontainer'
    );

    public static $parameters = array(
        'accordo' => array('Plurale' => 'Accordi', 'Descrizione' => 'Tutti gli accordi'),
        'convenzione' => array('Plurale' => 'Convenzioni', 'Descrizione' => 'Tutte le convenzioni'),
        'concessioni' => array('Plurale' => 'Concessioni', 'Descrizione' => 'Elenchi delle concessioni'),
        'event' => array('Plurale' => 'Eventi', 'Descrizione' => 'Tutti gli eventi'),
        'procedimento' => array('Plurale' => 'Procedimenti', 'Descrizione' => 'Tutti i procedimenti'),
        'tasso_assenza' => array('Plurale' => 'Tassi di assenza', 'Descrizione' => 'Tassi di assenza'),
        'area_tematica' => array('Plurale' => 'Aree tematiche', 'Descrizione' => 'Tutte le aree tematiche'),
        'associazione' => array('Plurale' => 'Associazioni', 'Descrizione' => 'Tutte le associazioni'),
        'ente' => array('Plurale' => 'Enti', 'Descrizione' => 'Tutti gli enti'),
        'ente_controllato' => array('Plurale' => 'Enti controllati', 'Descrizione' => 'Tutti gli enti controllati'),
        'interpellanza' => array('Plurale' => 'Interpellanze', 'Descrizione' => 'Tutte le interpellanze'),
        'interrogazione' => array('Plurale' => 'Interrogazioni', 'Descrizione' => 'Tutte le interrogazioni'),
        'mozione' => array('Plurale' => 'Mozioni', 'Descrizione' => 'Tutte le mozioni'),
        'sovvenzione_contributo' => array(
            'Plurale' => 'Sovvenzioni e contributi',
            'Descrizione' => 'Tutte le sovvenzioni ed i contributi'
        ),
        'seduta_consiglio' => array(
            'Plurale' => 'Sedute di consiglio',
            'Descrizione' => 'Tutte le sedute di consiglio'
        ),
        'rapporto' => array('Plurale' => 'Rapporti', 'Descrizione' => 'Tutti i rapporti'),
        'albo_elenco' => array('Plurale' => 'Albi ed elenchi', 'Descrizione' => 'Tutti gli albi ed elenchi'),
        'avviso' => array('Plurale' => 'Avvisi', 'Descrizione' => 'Tutti gli avvisi'),
        'bando' => array('Plurale' => 'Bandi di gara', 'Descrizione' => 'Tutti i bandi'),
        'bilancio_di_previsione' => array(
            'Plurale' => 'Bilanci di previsione',
            'Descrizione' => 'Tutti i bilanci previsionali'
        ),
        'concorso' => array('Plurale' => 'Concorsi', 'Descrizione' => 'Tutti i concorsi'),
        'conferimento_incarico' => array(
            'Plurale' => 'Conferimenti di incarico',
            'Descrizione' => 'Tutti i conferimenti di incarico'
        ),
        'consulenza' => array('Plurale' => 'Consulenze', 'Descrizione' => 'Tutte le consulenze'),
        'decreto_sindacale' => array('Plurale' => 'Decreti sindacali', 'Descrizione' => 'Tutti i decreti sindacali'),
        'dipendente' => array('Plurale' => 'Dipendenti', 'Descrizione' => 'Tutti i dipendenti'),
        'disciplinare' => array('Plurale' => 'Disciplinari', 'Descrizione' => 'Tutti i disciplinari'),
        'documento' => array('Plurale' => 'Documenti', 'Descrizione' => 'Tutti i documenti'),
        'gruppo_consiliare' => array('Plurale' => 'Gruppi consiliari', 'Descrizione' => 'Tutti i gruppi consiliari'),
        'modulo' => array('Plurale' => 'Moduli', 'Descrizione' => 'Tutti i moduli'),
        'organo_politico' => array('Plurale' => 'Organi politici', 'Descrizione' => 'Tutti gli organi politici'),
        'piano_progetto' => array('Plurale' => 'Piani e progetti', 'Descrizione' => 'Tutti i piani e progetti'),
        'politico' => array('Plurale' => 'Politici', 'Descrizione' => 'Tutti i politici'),
        'pubblicazione' => array('Plurale' => 'Pubblicazioni', 'Descrizione' => 'Tutte le pubblicazioni'),
        'luogo' => array(
            'Plurale' => 'Luoghi e punti di interesse',
            'Descrizione' => 'Tutti i luoghi ed i punti di interesse'
        ),
        'regolamento' => array('Plurale' => 'Regolamenti', 'Descrizione' => 'Tutti i regolamenti'),
        'rendiconto' => array('Plurale' => 'Rendiconti', 'Descrizione' => 'Tutti i rendiconti'),
        'sala_pubblica' => array('Plurale' => 'Sale pubbliche', 'Descrizione' => 'Tutte le sale pubbliche'),
        'servizio' => array('Plurale' => 'Servizi', 'Descrizione' => 'Tutti i servizi'),
        'ufficio' => array('Plurale' => 'Uffici', 'Descrizione' => 'Tutti gli uffici'),
        'servizio_sul_territorio' => array(
            'Plurale' => 'Servizi sul territorio',
            'Descrizione' => 'Tutti i servizi sul territorio'
        ),
        'statuto' => array('Plurale' => 'Statuti', 'Descrizione' => 'Tutti gli statuti')
    );

    public function createFromClassIdentifier($classIdentifier, $parameters = array(), $dryRun = null)
    {
        $tools = new OCOpenDataTools();

        if ( empty( $parameters ) && isset( self::$parameters[$classIdentifier] )){
            $parameters = self::$parameters[$classIdentifier];
        }

        //controllo se l'organizzazione è valida
        $tools->getOrganizationBuilder()->build();

        $pagedata = new \OpenPAPageData();
        $contacts = $pagedata->getContactsData();

        $siteUrl = rtrim(eZINI::instance()->variable('SiteSettings', 'SiteURL'), '/');
        $siteName = eZINI::instance()->variable('SiteSettings', 'SiteName');

        $exists = eZContentObjectTreeNode::fetchByRemoteID($this->generateNodeRemoteId($classIdentifier));
        //        if ( $exists instanceof eZContentObjectTreeNode ){
        //            throw new \Exception( "Dataset autogenerated for $classIdentifier already exists" );
        //        }

        $areaRemoteId = self::$remoteIds['area'];
        $area = eZContentObject::fetchByRemoteID($areaRemoteId);
        if (!$area instanceof eZContentObject) {
            throw new \Exception("Area opendata (remote $areaRemoteId) not found");
        }

        $containerRemoteId = self::$remoteIds['container'];
        $container = eZContentObject::fetchByRemoteID($containerRemoteId);
        if (!$container instanceof eZContentObject) {
            throw new \Exception("Dataset container (remote $containerRemoteId) not found");
        }

        $class = eZContentClass::fetchByIdentifier($classIdentifier);
        if (!$class instanceof eZContentClass) {
            throw new \Exception("Class $classIdentifier not found");
        }


        $attributeList = array();

        $contentSearch = new ContentSearch();
        $contentEnvironment = EnvironmentLoader::loadPreset('content');
        $geoEnvironment = EnvironmentLoader::loadPreset('geo');

        $undecodeQuery = "classes '$classIdentifier'";
        $query = urlencode($undecodeQuery);

        $hasResource = false;
        $hasGeoResource = false;

        $title = 'Contenuti di tipo ' . $class->attribute('name') . ' del ' . $siteName;
        $notes = 'Tutti i contenuti di tipo ' . $class->attribute('name') . ' del ' . $siteName;
        $tags = $classIdentifier;
        $resourceTitle = 'Contenuti di tipo ' . $class->attribute('name');
        if (isset( $parameters['Plurale'], $parameters['Descrizione'] )) {
            $title = $parameters['Plurale'] . ' del ' . $siteName;
            $notes = $parameters['Descrizione'] . ' pubblicati sul sito istituzionale del ' . $siteName;
            $tags = strtolower($parameters['Plurale']);
            $resourceTitle = $parameters['Plurale'];
        }


        $contentSearch->setEnvironment($contentEnvironment);
        if ($this->anonymousSearch($contentSearch, $undecodeQuery)) {
            $resourceFieldPrefix = 'resource_1_';
            $attributeList[$resourceFieldPrefix . 'api'] = "http://$siteUrl/api/opendata/v2/content/search/$query";
            $attributeList[$resourceFieldPrefix . 'name'] = $resourceTitle . ' in formato JSON';
            $attributeList[$resourceFieldPrefix . 'description'] = $class->attribute('description');
            $attributeList[$resourceFieldPrefix . 'format'] = 'JSON';
            $attributeList[$resourceFieldPrefix . 'charset'] = 'UTF-8';

            $resourceFieldPrefix = 'resource_2_';
            $attributeList[$resourceFieldPrefix . 'api'] = "http://$siteUrl/exportas/custom/csv_search/$query";;
            $attributeList[$resourceFieldPrefix . 'name'] = $resourceTitle . ' in formato CSV';
            $attributeList[$resourceFieldPrefix . 'description'] = $class->attribute('description');
            $attributeList[$resourceFieldPrefix . 'format'] = 'CSV';
            $attributeList[$resourceFieldPrefix . 'charset'] = 'UTF-8';
            $hasResource = true;
        }

        $resourceFieldPrefix = 'resource_3_';
        $contentSearch->setEnvironment($geoEnvironment);
        if ($this->anonymousSearch($contentSearch, $undecodeQuery)) {
            $attributeList[$resourceFieldPrefix . 'api'] = "http://$siteUrl/api/opendata/v2/content/geo/$query";
            $attributeList[$resourceFieldPrefix . 'name'] = $resourceTitle . ' in formato GeoJSON';
            $attributeList[$resourceFieldPrefix . 'description'] = $class->attribute('description');
            $attributeList[$resourceFieldPrefix . 'format'] = 'GeoJSON';
            $attributeList[$resourceFieldPrefix . 'charset'] = 'UTF-8';
            $hasGeoResource = true;
        }

        if (!$hasResource) {
            throw new \Exception("Nessuna risorsa trovata per $classIdentifier");
        }

        if ($hasGeoResource) {
            $resourceFieldPrefix = 'resource_4_';
        }

        $attributeList[$resourceFieldPrefix . 'api'] = "http://$siteUrl/api/opendata/v2/classes/$classIdentifier";
        $attributeList[$resourceFieldPrefix . 'name'] = 'Descrizione dei campi in formato JSON';
        $attributeList[$resourceFieldPrefix . 'format'] = 'JSON';
        $attributeList[$resourceFieldPrefix . 'charset'] = 'UTF-8';

        $attributeList['title'] = $title;
        $attributeList['author'] = $siteName . '|' . $contacts['email'];
        $attributeList['maintainer'] = $siteName . '|' . $contacts['email'];;
        $attributeList['url_website'] = 'http://' . $siteUrl . '/' . $area->attribute('main_node')->attribute('url_alias') . '|' . $area->attribute('name');
        $attributeList['notes'] = $notes;
        $attributeList['tech_documentation'] = null;
        $linkHelp = "http://$siteUrl/opendata/help/classes/$classIdentifier";
        $attributeList['fields_description_text'] = "I dati di questo dataset vengono erogati in modalità *as a service*: per maggiori informazioni sulle modalità di utilizzo del servizio si rimanda alla [guida delle API di ComunWeb](https://github.com/opendatatrentino/openservices) e [alla pagina di descrizione dei campi]($linkHelp).";
        $attributeList['geo'] = str_replace('Comune di', '', $siteName);
        $attributeList['frequency'] = 'Continuo';
        $attributeList['license_id'] = 'CC-BY';
        $attributeList['tags'] = $tags;
        $attributeList['versione'] = '1.0';
        //        $attributeList['tags'] = $classIdentifier . ', json, csv';
        //        if ( $hasGeoResource )
        //            $attributeList['tags'] .= ', geojson';

        $params = array();
        $params['class_identifier'] = $tools->getIni()->variable('GeneralSettings', 'DatasetClassIdentifier');
        $params['parent_node_id'] = $container->attribute('main_node_id');
        $params['attributes'] = $attributeList;

        if ($dryRun) {
            return true;
        }

        /** @var eZUser $user */
        $user = eZUser::fetchByName( 'admin' );
        eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

        if ($exists) {
            $object = $exists->object();
            eZContentFunctions::updateAndPublishObject($object, $params);
            eZContentObject::clearCache();
            $object = eZContentObject::fetch( $object->attribute('id') );
        } else {
            $object = eZContentFunctions::createAndPublishObject($params);
        }
        if ($object instanceof eZContentObject) {
            $mainNode = $object->attribute('main_node');
            if ($mainNode instanceof eZContentObjectTreeNode) {
                $mainNode->setAttribute('remote_id', $this->generateNodeRemoteId($classIdentifier));
                $mainNode->store();
            }
        }

        return $object;
    }

    protected function generateNodeRemoteId($classIdentifier)
    {
        return 'auto_dataset_' . $classIdentifier;
    }

    protected function anonymousSearch(ContentSearch $contentSearch, $query)
    {
        $anonymousId = eZINI::instance()->variable('UserSettings', 'AnonymousUserID');
        $loggedUser = eZUser::currentUser();
        $anonymousUser = \eZUser::fetch($anonymousId);

        if ($anonymousUser instanceof eZUser) {
            eZUser::setCurrentlyLoggedInUser($anonymousUser, $anonymousUser->attribute('contentobject_id'), 1);
        }
        try {
            $result = $contentSearch->search($query);
            $count = $result->totalCount;
        } catch (Exception $e) {
            eZUser::setCurrentlyLoggedInUser($loggedUser, $loggedUser->attribute('contentobject_id'), 1);
            $count = 0;
        }

        eZUser::setCurrentlyLoggedInUser($loggedUser, $loggedUser->attribute('contentobject_id'), 1);

        return $count > 0;

    }

}
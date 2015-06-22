<?php 

namespace Gedcomx\Tests\Functional;

use Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchStateFactory;
use Gedcomx\Rs\Client\Util\GedcomxPlaceSearchQueryBuilder;
use Gedcomx\Rs\Client\Util\HttpStatus;
use Gedcomx\Tests\ApiTestCase;
use Gedcomx\Tests\SandboxCredentials;
use Gedcomx\Rs\Client\Options\QueryParameter;

class PlacesTests extends ApiTestCase
{
    /**
     * @var \Gedcomx\Rs\Client\VocabElementListState
     */
    private $vocabListState;
    /**
     * @var \Gedcomx\Vocab\VocabElement[]
     */
    private $vocabElements;
    /**
     * @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces
     */
    private $collection;

    /**
     * @vcr PlacesTests/testReadPlace
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_usecase
     */
    public function testReadPlace()
    {
        $query = new GedcomxPlaceSearchQueryBuilder();
        $query->name("Paris");
        $query->parentId('442102', false, true);

        $factory = new FamilySearchStateFactory();
        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces $collection */
        $collection = $factory->newPlacesState()
                              ->authenticateViaOAuth2Password(
                                  SandboxCredentials::USERNAME,
                                  SandboxCredentials::PASSWORD,
                                  SandboxCredentials::API_KEY);
        $results = $collection->searchForPlaces($query);
        $this->assertEquals(
            HttpStatus::OK,
            $results->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__."(search results)", $results)
        );
        $places = $results->getResults();

        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaceDescriptionState $description */
        $description = $results->readPlaceDescription($places[0]);
        $this->assertEquals(
            HttpStatus::OK,
            $description->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__."(place description)", $description)
        );

        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces $place */
        $place = $description->readPlace();
        $this->assertEquals(
            HttpStatus::OK,
            $place->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__."(read place)", $place)
        );
        $this->assertNotNull($place->getEntity());
        $this->assertNotEmpty($description->getPlaceDescription());
    }

    /**
     * @vcr PlacesTests/testReadPlaceDescription
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_Description_usecase
     */
    public function testReadPlaceDescription()
    {
        $query = new GedcomxPlaceSearchQueryBuilder();
        $query->name("Paris");
        $query->parentId('442102', false, true);

        $factory = new FamilySearchStateFactory();
        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces $collection */
        $collection = $factory->newPlacesState()
                              ->authenticateViaOAuth2Password(
                                  SandboxCredentials::USERNAME,
                                  SandboxCredentials::PASSWORD,
                                  SandboxCredentials::API_KEY);
        $results = $collection->searchForPlaces($query);
        $this->assertEquals(
            HttpStatus::OK,
            $results->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__.'(search results)', $results)
        );

        $places = $results->getResults();

        $description = $results->readPlaceDescription($places[0]);
        $this->assertEquals(
            HttpStatus::OK,
            $description->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__, $description)
        );
        $this->assertNotNull($description->getEntity(), "Description entity is null.");
        $this->assertNotEmpty($description->getPlaceDescription(), "Place description not found.");
    }

    /**
     * @vcr PlacesTests/testReadPlaceDescriptionChildren
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_Description_Children_usecase
     */
    public function testReadPlaceDescriptionChildren()
    {
        $query = new GedcomxPlaceSearchQueryBuilder();
        $query->name("Utah")->typeId('47');

        $factory = new FamilySearchStateFactory();
        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces $collection */
        $collection = $factory->newPlacesState()
                              ->authenticateViaOAuth2Password(
                                  SandboxCredentials::USERNAME,
                                  SandboxCredentials::PASSWORD,
                                  SandboxCredentials::API_KEY);

        /** @var \Gedcomx\Rs\Client\PlaceSearchResultsState $results */
        $results = $collection->searchForPlaces($query);
        $this->assertEquals(
            HttpStatus::OK,
            $results->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__.'(search results)', $results)
        );
        $this->assertNotNull($results->getEntity(), "Search results entity is null.");
        /** @var \Gedcomx\Atom\Entry[] $places */
        $places = $results->getResults();


        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaceDescriptionState $description */
        $description = $results->readPlaceDescription($places[0]);
        $this->assertEquals(
            HttpStatus::OK,
            $description->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__.'(read description)', $description)
        );
        $this->assertNotNull($description->getEntity(), "Place description entity is null.");

        /** @var \Gedcomx\Rs\Client\PlaceDescriptionsState $children */
        $children = $description->readChildren();
        $this->assertEquals(
            HttpStatus::OK,
            $children->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__, $children)
        );
        $this->assertNotNull($children->getEntity(), "Children entity is null.");
        $this->assertNotEmpty($children->getPlaceDescriptions(), "Source description children not returned.");
    }

    /**
     * testReadPlaceGroup
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_Group_usecase
     * @see this::testPlaceTypeGroups
     */

    /**
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_Type_usecase
     * 
     * We don't record this because the list of place types is large.
     */
    public function testReadPlaceType()
    {
        ob_end_flush();
        ob_flush();
        
        $this->fetchVocabElements();

        $type = $this->collection->readPlaceTypeById($this->vocabElements[0]->getId(), QueryParameter::count('5'));

        $this->assertEquals(
            HttpStatus::OK,
            $type->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__,$type)
        );

        $element = $type->getVocabElement();
        $this->assertNotNull($element, "Vocabulary element should not be null.");
        $this->assertNotEmpty($element->getDescriptions(), "Vocabulary descriptions should not be empty.");
    }

    /**
     * testReadPlaceTypeGroup
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_Type_Group_usecase
     * @see this::testPlaceTypeGroups
     */

    /**
     * @vcr PlacesTests/testPlaceTypeGroups
     * @link https://familysearch.org/developers/docs/api/places/Read_Place_Type_Groups_usecase
     */
    public function testPlaceTypeGroups()
    {
        $factory = new FamilySearchStateFactory();
        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces $collection */
        $collection = $factory->newPlacesState()
                              ->authenticateViaOAuth2Password(
                                  SandboxCredentials::USERNAME,
                                  SandboxCredentials::PASSWORD,
                                  SandboxCredentials::API_KEY
                              );

        /**
         * Read the list of group types.
         * @link https://familysearch.org/developers/docs/api/places/Read_Place_Type_Groups_usecase
         */
        $groupTypesState = $collection->readPlaceTypeGroups();
        $this->assertEquals(
            HttpStatus::OK,
            $groupTypesState->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__."(List group types)",$groupTypesState)
        );
        $groupTypes = $groupTypesState->getVocabElementList()->getElements();
        $this->assertNotEmpty($groupTypes);
        /**
         * Read the list of types associated with a group.
         * @link https://familysearch.org/developers/docs/api/places/Read_Place_Type_Group_usecase
         */
        $groupTypeState = $collection->readPlaceTypeGroupById($groupTypes[0]->getId());
        $this->assertEquals(
            HttpStatus::OK,
            $groupTypeState->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__."(List groups in type)",$groupTypeState)
        );
        $groups = $groupTypeState->getVocabElementList()->getElements();
        $this->assertNotEmpty($groups);
        /**
         * Read a group from the list.
         * @link https://familysearch.org/developers/docs/api/places/Read_Place_Group_usecase
         */
        $groupState = $collection->readPlaceGroupById(30);
        $this->assertEquals(
            HttpStatus::OK,
            $groupState->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__."(Get group)",$groupState)
        );
        $this->assertNotEmpty($groupState->getPlaceGroup());
    }

    /**
     * @link https://familysearch.org/developers/docs/api/places/Place_Types_resource
     * 
     * We don't record this because the list of place types is large.
     */
    public function testReadPlaceTypes()
    {
        $this->fetchVocabElements();

        $this->assertEquals(
            HttpStatus::OK,
            $this->vocabListState->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__,$this->vocabListState)
        );
        $this->assertNotEmpty($this->vocabElements, "Vocabulary list is empty.");
        $this->assertInstanceOf(
            '\Gedcomx\Vocab\VocabElement',
            $this->vocabElements[0],
            'Vocab list does not appear to have parsed correctly.'
        );
    }

    /**
     * @vcr PlacesTests/testSearchForPlaces
     * @link https://familysearch.org/developers/docs/api/places/Search_For_Places_usecase
     */
    public function testSearchForPlaces()
    {
        $query = new GedcomxPlaceSearchQueryBuilder();
        $query->name("Paris");
        $factory = new FamilySearchStateFactory();

        /** @var \Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchPlaces $collection */
        $collection = $factory->newPlacesState()
                              ->authenticateViaOAuth2Password(
                                  SandboxCredentials::USERNAME,
                                  SandboxCredentials::PASSWORD,
                                  SandboxCredentials::API_KEY);
        
        // Only ask for 5 results so that we don't record a ton of data
        $response = $collection->searchForPlaces($query, QueryParameter::count(5));
        $this->assertEquals(HttpStatus::OK, $response->getResponse()->getStatusCode());
        $this->assertNotNull($response->getEntity(), "Search results entity is null.");

        /** @var \Gedcomx\Atom\Entry[] $results */
        $results = $response->getResults();
        $this->assertNotEmpty($results, "Search should have returned results.");

        /** @var \Gedcomx\Gedcomx $gx */
        $gx = $results[0]->getContent()->getGedcomx();
        $this->assertNotEmpty($gx->getPlaces(), "Places information missing.");
    }

    private function fetchVocabElements(){
        if ($this->vocabElements == null) {
            $factory = new FamilySearchStateFactory();
            $this->collection = $factory->newPlacesState()
                                        ->authenticateViaOAuth2Password(
                                            SandboxCredentials::USERNAME,
                                            SandboxCredentials::PASSWORD,
                                            SandboxCredentials::API_KEY
                                        );
            $this->vocabListState = $this->collection->readPlaceTypes();
            $this->vocabElements = $this->vocabListState->getVocabElementList()->getElements();
        }
    }
    
}
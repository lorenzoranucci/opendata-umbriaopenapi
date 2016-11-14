<?php


namespace Umbria\OpenApiBundle\Controller\Tourism;

use DateTime;
use Doctrine\ORM\EntityManager;
use EasyRdf_Graph;
use EasyRdf_Resource;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\DiExtraBundle\Annotation as DI;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\Paginator;
use Nelmio\ApiDocBundle\Annotation as ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Umbria\OpenApiBundle\Entity\Category;
use Umbria\OpenApiBundle\Entity\Tourism\GraphsEntities\Attractor;
use Umbria\OpenApiBundle\Entity\Tourism\GraphsEntitiesInnerObjects\AttractorDescription;
use Umbria\OpenApiBundle\Entity\Tourism\Setting;
use Umbria\OpenApiBundle\Entity\Type;
use Umbria\OpenApiBundle\Repository\Tourism\GraphsEntities\AttractorRepository;
use Umbria\OpenApiBundle\Serializer\View\EntityResponse;
use Umbria\OpenApiBundle\Service\FilterBag;


/**
 * Class AttractorController
 * @package Umbria\OpenApiBundle\Controller\Tourism
 *
 * @author Lorenzo Franco Ranucci <loryzizu@gmail.com>
 */
class AttractorController extends BaseController
{
    const DEFAULT_PAGE_SIZE = 100;
    const DATASET_TOURISM_ATTRACTOR = 'tourism-attractor';
    private $filterBag;
    private $paginator;

    private $em;
    /**@var AttractorRepository attractorRepo */
    private $attractorRepo;

    private $settingsRepo;
    private $categoryRepo;
    private $typeRepo;

    private $graph;

    /**
     * @DI\InjectParams({
     *      "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *      "filterBag" = @DI\Inject("umbria_open_api.filter_bag"),
     *      "paginator" = @DI\Inject("knp_paginator")
     * })
     * @param $em EntityManager
     * @param $filterBag FilterBag
     * @param $paginator Paginator
     */
    public function __construct($em, $filterBag, $paginator)
    {
        parent::__construct($em);
        $this->filterBag = $filterBag;
        $this->paginator = $paginator;
        $this->attractorRepo = $em->getRepository('UmbriaOpenApiBundle:Tourism\GraphsEntities\Attractor');
        $this->settingsRepo = $em->getRepository('UmbriaOpenApiBundle:Tourism\Setting');
        $this->categoryRepo = $em->getRepository('UmbriaOpenApiBundle:Category');
        $this->typeRepo = $em->getRepository('UmbriaOpenApiBundle:Type');
        $this->em = $em;
    }

    /**
     * @Rest\Options(pattern="/open-api/tourism-attractor")
     */
    public function optionsTourismAttractorAction()
    {
        $response = new Response();

        $response->setContent(json_encode(array(
            'Allow' => 'GET,OPTIONS',
            'GET' => array(
                'description' => 'A get request for attractors',
            ),
        )));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'json');

        return $response;
    }

    /**
     * @Rest\Get(pattern="/open-api/tourism-attractor")
     *
     * @param Request $request
     *
     * @return View
     *
     * @internal param Request $request
     *
     * @ApiDoc\ApiDoc(
     *  section = "Tourism",
     *  description = "Lista attrattori turistici regione Umbria",
     *  tags = {
     *      "beta"
     *  },
     *  parameters={
     *      {"name"="start", "dataType"="integer", "required"=false, "description"="Indice elemento iniziale"},
     *      {"name"="limit", "dataType"="integer", "required"=false, "description"="Numero di elementi"},
     *      {"name"="label_like", "dataType"="string", "required"=false, "description"="Condizione 'LIKE' su denominazione"},
     *      {"name"="descriptions_like", "dataType"="string", "required"=false, "description"="Condizione 'LIKE' sulle descrizioni"},
     *      {"name"="category_like", "dataType"="string", "required"=false, "description"="Condizione 'LIKE' sulle categorie"},
     *      {"name"="lat_max", "dataType"="number", "required"=false, "description"="Latitudine massima"},
     *      {"name"="lat_min", "dataType"="number", "required"=false, "description"="Latitudine minima"},
     *      {"name"="lng_max", "dataType"="number", "required"=false, "description"="Longitudine massima"},
     *      {"name"="lng_min", "dataType"="number", "required"=false, "description"="Longitudine minima"}
     *
     *
     *  },
     *  statusCodes={
     *      200="Restituito in caso di successo"
     *  }
     * )
     */
    public function getTourismAttractorListAction(Request $request)
    {
        $daysToOld = $this->container->getParameter('attractor_days_to_old');
        $filters = $this->filterBag->getFilterBag($request);
        $offset = $filters->has('start') ? $filters->get('start') : 0;
        $limit = $filters->has('limit') ? $filters->get('limit') : self::DEFAULT_PAGE_SIZE;
        $labelLike = $filters->has('label_like') ? $filters->get('label_like') : null;
        $descriptionLike = $filters->has('descriptions_like') ? $filters->get('descriptions_like') : null;
        $categoryLike = $filters->has('category_like') ? $filters->get('category_like') : null;
        $latMax = $filters->has('lat_max') && $filters->get('lat_max') ? floatval($filters->get('lat_max')) : null;
        $latMin = $filters->has('lat_min') && $filters->get('lat_min') ? floatval($filters->get('lat_min')) : null;
        $lngMax = $filters->has('lng_max') && $filters->get('lng_max') ? floatval($filters->get('lng_max')) : null;
        $lngMin = $filters->has('lng_min') && $filters->get('lng_min') ? floatval($filters->get('lng_min')) : null;
        if ($limit == 0) {
            $limit = self::DEFAULT_PAGE_SIZE;
        }
        $page = floor($offset / $limit) + 1;

        /** @var Setting $setting */
        $setting = $this->settingsRepo->findOneBy(array('datasetName' => self::DATASET_TOURISM_ATTRACTOR));
        if ($setting != null) {
            $diff = $setting->getUpdatedAt()->diff(new DateTime('now'));
            if ($diff->days >= $daysToOld) {
                $setting->setDatasetName(self::DATASET_TOURISM_ATTRACTOR);
                $setting->setUpdatedAtValue();
                $this->em->persist($setting);
                $this->em->flush();
                $this->updateEntities();
            }
        } else {
            $setting = new Setting();
            $setting->setDatasetName(self::DATASET_TOURISM_ATTRACTOR);
            $setting->setUpdatedAtValue();
            $this->em->persist($setting);
            $this->em->flush();
            $this->updateEntities();
        }

        $qb = $this->em->createQueryBuilder();
        $builder = $qb
            ->select('a')
            ->from('UmbriaOpenApiBundle:Tourism\GraphsEntities\Attractor', 'a');


        if ($labelLike != null) {
            $builder = $qb
                ->andWhere($qb->expr()->like('a.name', '?2'))
                ->setParameter(2, $labelLike);
        }

        if ($descriptionLike != null) {
            $builder = $qb
                ->innerJoin('a.descriptions', 'd')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('d.title', '?1'),
                        $qb->expr()->like('d.text', '?1'),
                        $qb->expr()->like('a.comment', '?1')
                    )
                )
                ->setParameter(1, $descriptionLike);

        }

        if ($categoryLike != null) {
            $builder = $qb
                ->leftJoin('a.categories', 'cat')
                ->andWhere(
                    $qb->expr()->like('cat.name', ':categoryLike')
                )
                ->setParameter("categoryLike", $categoryLike);

        }

        if ($latMax != null ||
            $latMin != null ||
            $lngMax != null ||
            $lngMin != null
        ) {
            if ($latMax != null) {
                $builder =
                    $qb->andWhere(
                        $qb->expr()->lte("a.lat", ':latMax'),
                        $qb->expr()->isNotNull("a.lat"),
                        $qb->expr()->gt("a.lat", ':empty')
                    )
                        ->setParameter('latMax', $latMax)
                        ->setParameter('empty', '0');
            }
            if ($latMin != null) {
                $builder =
                    $qb->andWhere(
                        $qb->expr()->gte("a.lat", ':latMin'),
                        $qb->expr()->isNotNull("a.lat"),
                        $qb->expr()->gt("a.lat", ":empty")
                    )
                        ->setParameter('latMin', $latMin)
                        ->setParameter('empty', '0');
            }
            if ($lngMax != null) {
                $builder =
                    $qb->andWhere(
                        $qb->expr()->lte("a.lng", ':lngMax'),
                        $qb->expr()->isNotNull("a.lng"),
                        $qb->expr()->gt("a.lng", ":empty")
                    )
                        ->setParameter('lngMax', $lngMax)
                        ->setParameter('empty', '0');
            }
            if ($lngMin != null) {
                $builder =
                    $qb->andWhere(
                        $qb->expr()->gte("a.lng", ':lngMin'),
                        $qb->expr()->isNotNull("a.lng"),
                        $qb->expr()->gt("a.lng", ":empty")
                    )
                        ->setParameter('lngMin', $lngMin)
                        ->setParameter('empty', '0');
            }
        }

        /** @var AbstractPagination $resultsPagination */
        $resultsPagination = $this->paginator->paginate($builder, $page, $limit);
        /** @var AbstractPagination $countPagination */
        $countPagination = $this->paginator->paginate($builder, 1, 1);


        $view = new View(new EntityResponse($resultsPagination->getItems(), count($resultsPagination), $countPagination->getTotalItemCount()));

        return $view;
    }

    private function updateEntities()
    {

        $this->graph = EasyRdf_Graph::newAndLoad($this->container->getParameter('attractors_graph_url'));
        /**@var EasyRdf_Resource[] $resources */
        $resources = $this->graph->resources();
        foreach ($resources as $resource) {
            $resourceTypeArray = $resource->all("rdf:type");
            if ($resourceTypeArray != null) {
                foreach ($resourceTypeArray as $resourceType) {
                    if (trim($resourceType) == "http://linkedgeodata.org/ontology/Attraction") {//is attractor
                        $this->createOrUpdateEntity($resource);
                        break;
                    }
                }
            }

        }
        $now = new \DateTime();
        $this->deleteOldEntities($now);
    }

    /**
     * @param EasyRdf_Resource $attractorResource
     */
    private function createOrUpdateEntity($attractorResource)
    {
        /** @var Attractor $newAttractor */
        $newAttractor = null;
        $uri = $attractorResource->getUri();
        if ($uri != null) {
            $oldAttractor = $this->attractorRepo->find($uri);
            $isAlreadyPersisted = $oldAttractor != null;
            if ($isAlreadyPersisted) {
                $newAttractor = $oldAttractor;
            } else {
                $newAttractor = new Attractor();
            }
            $newAttractor->setUri($uri);
            $newAttractor->setLastUpdateAt(new \DateTime('now'));
            $newAttractor->setName(($p = $attractorResource->get("rdfs:label", null, "it")) != null ? $p->getValue() : null);

            /**@var EasyRdf_Resource[] $typesarray */
            $typesarray = $attractorResource->all("rdf:type");
            if ($typesarray != null) {
                /**@var Type[] $tempTypes */
                $tempTypes = array();
                $cnt = 0;
                foreach ($typesarray as $type) {
                    $oldType = $this->typeRepo->find($type->getUri());
                    if ($oldType != null) {
                        $tempTypes[$cnt] = $oldType;
                    } else {
                        $tempTypes[$cnt] = new Type();
                        $tempTypes[$cnt]->setUri($type->getUri());
                        $tempTypes[$cnt]->setName(($p = $type->get("rdfs:label")) != null ? $p->getValue() : null);
                        $tempTypes[$cnt]->setComment(($p = $type->get("<http://www.w3.org/2000/01/rdf-schema#comment>")) != null ? $p->getValue() : null);
                    }
                    $cnt++;
                }
                count($tempTypes) > 0 ? $newAttractor->setTypes($tempTypes) : $newAttractor->setTypes(null);
            }

            $newAttractor->setComment(($p = $attractorResource->get("<http://www.w3.org/2000/01/rdf-schema#comment>", null, "it")) != null ? $p->getValue() : null);
            $newAttractor->setProvenance(($p = $attractorResource->get("<http://purl.org/dc/elements/1.1/provenance>")) != null ? $p->getValue() : null);
            $newAttractor->setMunicipality(($p = $attractorResource->get("<http://dbpedia.org/ontology/municipality>")) != null ? $p->getValue() : null);
            $newAttractor->setIstat(($p = $attractorResource->get("<http://dbpedia.org/ontology/istat>")) != null ? $p->getValue() : null);
            $newAttractor->setSubject(($p = $attractorResource->get("<http://purl.org/dc/elements/1.1/subject>")) != null ? $p->getValue() : null);
            $newAttractor->setLat(($p = $attractorResource->get("<http://www.w3.org/2003/01/geo/wgs84_pos#lat>")) != null ? (float)$p->getValue() : null);
            $newAttractor->setLng(($p = $attractorResource->get("<http://www.w3.org/2003/01/geo/wgs84_pos#long>")) != null ? (float)$p->getValue() : null);

            $imagearray1 = $attractorResource->all("<http://dati.umbria.it/tourism/ontology/immagine_copertina>");
            $imagearray2 = $attractorResource->all("<http://dati.umbria.it/tourism/ontology/immagine_spalla_destra>");
            /**@var EasyRdf_Resource[] $imagearray */
            $imagearray = array_merge($imagearray1, $imagearray2);
            if ($imagearray != null) {
                $tempImage = array();
                $newAttractor->setImages(array());
                $cnt = 0;
                foreach ($imagearray as $image) {
                    $tempImage[$cnt] = $image->toRdfPhp()['value'];
                    $cnt++;
                }
                count($tempImage) > 0 ? $newAttractor->setImages($tempImage) : $newAttractor->setImages(null);
            }

            $newAttractor->setTextTitle(($p = $attractorResource->get("<http://dati.umbria.it/tourism/ontology/titolo_testo>", null, "it")) != null ? $p->getValue() : null);
            /*TODO link esterni associati*/
            $newAttractor->setResourceOriginUrl(($p = $attractorResource->get("<http://dati.umbria.it/tourism/ontology/url_risorsa>")) != null ? $p->getValue() : null);
            $newAttractor->setShortDescription(($p = $attractorResource->get("<http://dati.umbria.it/tourism/ontology/descrizione_sintetica>", null, "it")) != null ? $p->getValue() : null);

            if ($isAlreadyPersisted && ($oldDescriptions = $newAttractor->getDescriptions()) != null) {
                foreach ($oldDescriptions as $oldDescription) {
                    $this->em->remove($oldDescription);
                }
                $newAttractor->setDescriptions(null);
            }
            /**@var EasyRdf_Resource[] $descriptionArray */
            $descriptionArray = $attractorResource->all("<http://dati.umbria.it/tourism/ontology/descrizione>");
            if ($descriptionArray != null) {
                $tempDescriptions = array();
                $cnt = 0;
                foreach ($descriptionArray as $descriptionResource) {
                    if ($descriptionResource->get("<http://dati.umbria.it/tourism/ontology/testo>")->getLang() == "it") {
                        $descriptionTitle = ($p = $descriptionResource->get("<http://dati.umbria.it/tourism/ontology/titolo>")) != null ? $p->getValue() : null;
                        $descriptionText = $descriptionResource->get("<http://dati.umbria.it/tourism/ontology/testo>")->getValue();
                        $descriptionObject = new AttractorDescription();
                        $descriptionObject->setTitle($descriptionTitle);
                        $descriptionObject->setText($descriptionText);
                        $descriptionObject->setAttractor($newAttractor);
                        $tempDescriptions[$cnt] = $descriptionObject;
                        $cnt++;
                    }
                }
                if (count($tempDescriptions) > 0) {
                    $newAttractor->setDescriptions($tempDescriptions);
                }
            }

            /*TODO travel time*/


            /**@var EasyRdf_Resource[] $sameAsArray */
            $sameAsArray = $attractorResource->all("<http://www.w3.org/2002/07/owl#sameAs>");
            if ($sameAsArray != null) {
                $newAttractor->setSameAs($this->getExternalResources($sameAsArray, "http://dbpedia.org/sparql",
                    "http://www.w3.org/2000/01/rdf-schema#label", "http://dbpedia.org/ontology/abstract", "http://www.w3.org/ns/prov#wasDerivedFrom"));
            }
            /**@var EasyRdf_Resource[] $locatedInArray */
            $locatedInArray = $attractorResource->all("<http://www.geonames.org/ontology#locatedIn>");
            if ($locatedInArray != null) {
                $newAttractor->setLocatedIn($this->getExternalResources($locatedInArray, "http://it.dbpedia.org/sparql",
                    "http://www.w3.org/2000/01/rdf-schema#label", "http://dbpedia.org/ontology/abstract", "http://www.w3.org/ns/prov#wasDerivedFrom"));
            }

            /**@var EasyRdf_Resource[] $categoriesarray */
            $categoriesarray = $attractorResource->all("<http://dati.umbria.it/tourism/ontology/categoria>");
            if ($categoriesarray != null) {
                /**@var Category[] $tempCategories */
                $tempCategories = array();
                $cnt = 0;
                foreach ($categoriesarray as $category) {
                    $oldCategory = $this->categoryRepo->find($category->getUri());
                    if ($oldCategory != null) {
                        $tempCategories[$cnt] = $oldCategory;
                    } else {
                        $tempCategories[$cnt] = new Category();
                        $tempCategories[$cnt]->setUri($category->getUri());
                        $tempCategories[$cnt]->setName(($p = $category->get("rdfs:label")) != null ? $p->getValue() : null);
                        $tempCategories[$cnt]->setComment(($p = $category->get("<http://www.w3.org/2000/01/rdf-schema#comment>")) != null ? $p->getValue() : null);
                    }
                    $cnt++;
                }
                count($tempCategories) > 0 ? $newAttractor->setCategories($tempCategories) : $newAttractor->setCategories(null);
            }

            if (!$isAlreadyPersisted) {
                $this->em->persist($newAttractor);
            }

            $this->em->flush();
        }
    }

    private function deleteOldEntities($olderThan)
    {
        $this->attractorRepo->removeLastUpdatedBefore($olderThan);
    }
}

<?php

namespace Umbria\OpenApiBundle\Entity\Tourism\GraphsEntities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proposal
 *
 * @author Lorenzo Franco Ranucci
 *
 * @ORM\Table(name="tourism_proposal")
 * @ORM\Entity(repositoryClass="Umbria\OpenApiBundle\Repository\Tourism\GraphsEntities\ProposalRepository")
 */
class Proposal
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="uri", type="string", length=255, unique=true)
     */
    private $uri;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="provenance", type="string", length=255, nullable=true)
     */
    private $provenance;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="abstract", type="string", length=255, nullable=true)
     */
    private $abstract;

    /**
     * @var array
     *
     * @ORM\Column(name="images", type="array", nullable=true)
     */
    private $images;

    /**
     * @var array
     *
     * @ORM\Column(name="externalLinks", type="array", nullable=true)
     */
    private $externalLinks;

    /**
     * @var string
     *
     * @ORM\Column(name="textTitle", type="string", length=255, nullable=true)
     */
    private $textTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="resourceOriginUrl", type="string", length=255, nullable=true)
     */
    private $resourceOriginUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="shortDescription", type="string", length=255, nullable=true)
     */
    private $shortDescription;

    /**
     * @var array
     *
     * @ORM\Column(name="types", type="array", nullable=true)
     */
    private $types;

    /**
     * @var array
     *
     * @ORM\Column(name="categories", type="array", nullable=true)
     */
    private $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255, nullable=true)
     */
    private $language;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", nullable=true)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", nullable=true)
     */
    private $lng;

    /**
     * @var array
     *
     * @ORM\Column(name="travelTime", type="array", nullable=true)
     */
    private $travelTime;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uri
     *
     * @param string $uri
     *
     * @return Proposal
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Proposal
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set provenance
     *
     * @param string $provenance
     *
     * @return Proposal
     */
    public function setProvenance($provenance)
    {
        $this->provenance = $provenance;

        return $this;
    }

    /**
     * Get provenance
     *
     * @return string
     */
    public function getProvenance()
    {
        return $this->provenance;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Proposal
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set abstract
     *
     * @param string $abstract
     *
     * @return Proposal
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set images
     *
     * @param array $images
     *
     * @return Proposal
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set externalLinks
     *
     * @param array $externalLinks
     *
     * @return Proposal
     */
    public function setExternalLinks($externalLinks)
    {
        $this->externalLinks = $externalLinks;

        return $this;
    }

    /**
     * Get externalLinks
     *
     * @return array
     */
    public function getExternalLinks()
    {
        return $this->externalLinks;
    }

    /**
     * Set textTitle
     *
     * @param string $textTitle
     *
     * @return Proposal
     */
    public function setTextTitle($textTitle)
    {
        $this->textTitle = $textTitle;

        return $this;
    }

    /**
     * Get textTitle
     *
     * @return string
     */
    public function getTextTitle()
    {
        return $this->textTitle;
    }

    /**
     * Set resourceOriginUrl
     *
     * @param string $resourceOriginUrl
     *
     * @return Proposal
     */
    public function setResourceOriginUrl($resourceOriginUrl)
    {
        $this->resourceOriginUrl = $resourceOriginUrl;

        return $this;
    }

    /**
     * Get resourceOriginUrl
     *
     * @return string
     */
    public function getResourceOriginUrl()
    {
        return $this->resourceOriginUrl;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     *
     * @return Proposal
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set types
     *
     * @param array $types
     *
     * @return Proposal
     */
    public function setTypes($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Get types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Set categories
     *
     * @param array $categories
     *
     * @return Proposal
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Proposal
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set lat
     *
     * @param float $lat
     *
     * @return Proposal
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     *
     * @return Proposal
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set travelTime
     *
     * @param array $travelTime
     *
     * @return Proposal
     */
    public function setTravelTime($travelTime)
    {
        $this->travelTime = $travelTime;

        return $this;
    }

    /**
     * Get travelTime
     *
     * @return array
     */
    public function getTravelTime()
    {
        return $this->travelTime;
    }
}

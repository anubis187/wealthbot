<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 27.05.13
 * Time: 19:51
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\UserInterface;
use Wealthbot\ClientBundle\Model\Workflow;
use Wealthbot\ClientBundle\Model\WorkflowableInterface;
use Wealthbot\UserBundle\Entity\User;

class CeModel implements CeModelInterface
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $parentId;

    /**
     * @var \Wealthbot\AdminBundle\Entity\CeModel
     */
    protected $parent;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var integer
     */
    protected $ownerId;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    protected $owner;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var boolean
     */
    protected $isDeleted;

    /**
     * @var integer
     */
    protected $riskRating;

    /**
     * @var float
     */
    protected $commissionMin;

    /**
     * @var float
     */
    protected $commissionMax;

    /**
     * @var integer
     */
    protected $forecast;

    /**
     * @var float
     */
    protected $generousMarketReturn;

    /**
     * @var float
     */
    protected $lowMarketReturn;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $modelEntities;

    /**
     * @var array
     */
    private $groupedModelEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $children;

    /**
     * @var Boolean
     */
    protected $isAssumptionLocked;


    public function __construct($name = null)
    {
        $this->name = $name;
        $this->ownerId = null;
        $this->parentId = null;
        $this->riskRating = 0;
        $this->isUseQualified = false;
        $this->isDeleted = false;
        $this->isAssumptionLocked = false;

        $this->modelEntities = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->groupedModelEntities = array(
            'qualified' => array(),
            'non_qualified' => array()
        );
    }

    public function getId()
    {
        return $this->id;
    }

    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOwner(UserInterface $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParent(CeModelInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRiskRating($riskRating)
    {
        $riskRating = (integer)$riskRating;
        if ($riskRating < 0 ) throw new \InvalidArgumentException("Risk rating should be 0 or higher");

        $this->riskRating = $riskRating;

        return $this;
    }

    public function getRiskRating()
    {
        return $this->riskRating;
    }

    public function setType($type)
    {
        if ($type != self::TYPE_STRATEGY && $type != self::TYPE_CUSTOM) {
            throw new \InvalidArgumentException("Type has not valid value");
        }

        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function hasType($type)
    {
        return ($this->type === $type);
    }

    public function getTypeName()
    {
        return ($this->isCustom() ? 'Custom' : 'Strategy');
    }

    public function isStrategy()
    {
        return $this->hasType(self::TYPE_STRATEGY);
    }

    public function isCustom()
    {
        return $this->hasType(self::TYPE_CUSTOM);
    }

    public function setCommissionMin($commissionMin)
    {
        $this->commissionMin = $commissionMin;

        return $this;
    }

    public function getCommissionMin()
    {
        return $this->commissionMin;
    }

    public function setCommissionMax($commissionMax)
    {
        $this->commissionMax = $commissionMax;

        return $this;
    }

    public function getCommissionMax()
    {
        return $this->commissionMax;
    }

    public function getCommissions()
    {
        $commission = array();

        $min = $this->getCommissionMin();
        if (null !== $min) {
            $commission[] = $min;
        }

        $max = $this->getCommissionMax();
        if (null !== $max) {
            $commission[] = $max;
        }

        return $commission;
    }

    public function setForecast($forecast)
    {
        $this->forecast = $forecast;

        return $this;
    }

    public function getForecast()
    {
        return $this->forecast;
    }

    public function setGenerousMarketReturn($generousMarketReturn)
    {
        $this->generousMarketReturn = $generousMarketReturn;

        return $this;
    }

    public function getGenerousMarketReturn()
    {
        return $this->generousMarketReturn;
    }

    public function setLowMarketReturn($lowMarketReturn)
    {
        $this->lowMarketReturn = $lowMarketReturn;

        return $this;
    }

    public function getLowMarketReturn()
    {
        return $this->lowMarketReturn;
    }

    public function setIsAssumptionLocked($isAssumptionLocked)
    {
        $this->isAssumptionLocked = $isAssumptionLocked;

        return $this;
    }

    public function getIsAssumptionLocked()
    {
        return $this->isAssumptionLocked;
    }

    public function addModelEntity(CeModelEntityInterface $modelEntity)
    {
        $this->modelEntities[] = $modelEntity;

        return $this;
    }

    public function removeModelEntity(CeModelEntityInterface $modelEntity)
    {
        $this->modelEntities->removeElement($modelEntity);

        if ($modelEntity->getIsQualified()) {
            $this->removeQualifiedModelEntity($modelEntity);
        } else {
            $this->removeNonQualifiedModelEntity($modelEntity);
        }
    }

    public function getModelEntities()
    {
        return $this->modelEntities;
    }

    public function getQualifiedModelEntities()
    {
        return $this->groupedModelEntities['qualified'];
    }

    public function getNonQualifiedModelEntities()
    {
        return $this->groupedModelEntities['non_qualified'];
    }

    public function addChildren(CeModelInterface $children)
    {
        $this->children[] = $children;

        return $this;
    }

    public function removeChildren(CeModelInterface $children)
    {
        $this->children->removeElement($children);
    }


    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get array of assumption values
     *
     * @return array
     */
    public function getAssumption()
    {
        $assumption = array(
            'commission_min' => $this->getCommissionMin(),
            'commission_max' => $this->getCommissionMax(),
            'forecast' => $this->getForecast(),
            'generous_market_return' => $this->getGenerousMarketReturn(),
            'low_market_return' => $this->getLowMarketReturn()
        );

        return $assumption;
    }

    /**
     * Set assumption values
     *
     * @param array $assumption
     */
    public function setAssumption(array $assumption)
    {
        $this->setCommissionMin($assumption['commission_min']);
        $this->setCommissionMax($assumption['commission_max']);
        $this->setForecast($assumption['forecast']);
        $this->setGenerousMarketReturn($assumption['generous_market_return']);
        $this->setLowMarketReturn($assumption['low_market_return']);
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return self
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Copy model
     *
     * @return self
     */
    public function getCopy()
    {
        $clone = clone $this;

        $clone->id = null;
        $clone->ownerId = null;
        $clone->parentId = null;
        $clone->riskRating = 0;
        $clone->isUseQualified = false;
        $clone->isDeleted = false;
        $clone->isAssumptionLocked = false;

        $clone->modelEntities = new ArrayCollection();
        $clone->children = new ArrayCollection();
        $clone->groupedModelEntities = array(
            'qualified' => array(),
            'non_qualified' => array()
        );

        $clone->setRiskRating($this->getRiskRating());
        $clone->setAssumption($this->getAssumption());
        $clone->setIsAssumptionLocked(false);
        $clone->setName($this->getName());

        if (null !== $this->getType()) {
            $clone->setType($this->getType());
        }

        return $clone;
    }

    public function buildGroupModelEntities()
    {
        $this->groupedModelEntities = array(
            'qualified' => array(),
            'non_qualified' => array()
        );

        foreach ($this->getModelEntities() as $modelEntity) {
            if ($modelEntity->getIsQualified()) {
                $this->addQualifiedModelEntity($modelEntity);
            } else {
                $this->addNonQualifiedModelEntity($modelEntity);
            }
        }
    }

    private function addQualifiedModelEntity(CeModelEntityInterface $ceModelEntity)
    {
        if (!isset($this->groupedModelEntities['qualified'])) {
            $this->groupedModelEntities['qualified'] = array();
        }

        $this->groupedModelEntities['qualified'][] = $ceModelEntity;
    }

    private function removeQualifiedModelEntity(CeModelEntityInterface $ceModelEntity)
    {
        $existKey = array_search($ceModelEntity, $this->groupedModelEntities['qualified'], true);
        if ($existKey !== false) {
            unset($this->groupedModelEntities['qualified'][$existKey]);
        }
    }

    private function addNonQualifiedModelEntity(CeModelEntityInterface $ceModelEntity)
    {
        if (!isset($this->groupedModelEntities['non_qualified'])) {
            $this->groupedModelEntities['non_qualified'] = array();
        }

        $this->groupedModelEntities['non_qualified'][] = $ceModelEntity;
    }

    private function removeNonQualifiedModelEntity(CeModelEntityInterface $ceModelEntity)
    {
        $existKey = array_search($ceModelEntity, $this->groupedModelEntities['non_qualified'], true);
        if ($existKey !== false) {
            unset($this->groupedModelEntities['non_qualified'][$existKey]);
        }
    }
}
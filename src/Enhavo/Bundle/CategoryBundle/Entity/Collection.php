<?php
namespace Enhavo\Bundle\CategoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Enhavo\Bundle\CategoryBundle\Model\CollectionInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class Collection implements CollectionInterface, ResourceInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $categories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Collection
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
     * Add categories
     *
     * @param \Enhavo\Bundle\CategoryBundle\Entity\Category $categories
     * @return Collection
     */
    public function addCategory(\Enhavo\Bundle\CategoryBundle\Entity\Category $category)
    {
        $category->setCollection($this);
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param \Enhavo\Bundle\CategoryBundle\Entity\Category $categories
     */
    public function removeCategory(\Enhavo\Bundle\CategoryBundle\Entity\Category $category)
    {
        $category->setCollection(null);
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    public function __toString()
    {
        if($this->name === null) {
            return '';
        }
        return $this->name;
    }
}

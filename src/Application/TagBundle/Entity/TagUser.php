<?php

namespace Application\TagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\TagBundle\Entity\TagUser
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class TagUser
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer $tag_id
     *
     * @ORM\Column(name="tag_id", type="integer")
     */
    private $tag_id;

    /**
     * @var integer $user_id
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * Set tag_id
     *
     * @param integer $tagId
     */
    public function setTagId($tagId)
    {
        $this->tag_id = $tagId;
    }

    /**
     * Get tag_id
     *
     * @return integer 
     */
    public function getTagId()
    {
        return $this->tag_id;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }
}
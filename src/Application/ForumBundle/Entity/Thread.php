<?php

namespace Application\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\ForumBundle\Entity\Thread
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Thread
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $body
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var integer $forum_id
     *
     * @ORM\Column(name="forum_id", type="integer")
     */
    private $forum_id;

    /**
     * @var integer $user_id
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @var integer $visits
     *
     * @ORM\Column(name="visits", type="integer", nullable=true)
     */
    private $visits = 0;

    /**
     * @var integer $replies
     *
     * @ORM\Column(name="replies", type="integer", nullable=true)
     */
    private $replies = 0;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var integer $spam
     *
     * @ORM\Column(name="spam", type="integer", nullable=true)
     */
    private $spam = 0;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var datetime $date_update
     *
     * @ORM\Column(name="date_update", type="datetime")
     */
    private $date_update;

    /**
     * @var integer $featured
     *
     * @ORM\Column(name="featured", type="integer")
     */
    private $featured = 0;

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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param text $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get body
     *
     * @return text 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set forum_id
     *
     * @param integer $forumId
     */
    public function setForumId($forumId)
    {
        $this->forum_id = $forumId;
    }

    /**
     * Get forum_id
     *
     * @return integer 
     */
    public function getForumId()
    {
        return $this->forum_id;
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
     * Set visits
     *
     * @param integer $visits
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;
    }

    /**
     * Get visits
     *
     * @return integer 
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * Set replies
     *
     * @param integer $replies
     */
    public function setReplies($replies)
    {
        $this->replies = $replies;
    }

    /**
     * Get replies
     *
     * @return integer 
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
     * Set spam
     *
     * @param integer $spam
     */
    public function setSpam($spam)
    {
        $this->spam = $spam;
    }

    /**
     * Get spam
     *
     * @return integer 
     */
    public function getSpam()
    {
        return $this->spam;
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

    /**
     * Set date_update
     *
     * @param datetime $date
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->date_update = $dateUpdate;
    }

    /**
     * Get date_update
     *
     * @return datetime 
     */
    public function getDateUpdate()
    {
        return $this->date_update;
    }

    /**
     * Set featured
     *
     * @param integer $featured
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;
    }

    /**
     * Get featured
     *
     * @return integer
     */
    public function getFeatured()
    {
        return $this->featured;
    }
}
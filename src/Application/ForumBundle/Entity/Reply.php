<?php

namespace Application\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\ForumBundle\Entity\Reply
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Reply
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
     * @var text $body
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var integer $thread_id
     *
     * @ORM\Column(name="thread_id", type="integer")
     */
    private $thread_id;

    /**
     * @var integer $user_id
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @var integer $votes
     *
     * @ORM\Column(name="votes", type="integer", nullable=true)
     */
    private $votes = 0;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
     * Set thread_id
     *
     * @param integer $threadId
     */
    public function setThreadId($threadId)
    {
        $this->thread_id = $threadId;
    }

    /**
     * Get thread_id
     *
     * @return integer 
     */
    public function getThreadId()
    {
        return $this->thread_id;
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
     * Set votes
     *
     * @param integer $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * Get votes
     *
     * @return integer 
     */
    public function getVotes()
    {
        return $this->votes;
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
}
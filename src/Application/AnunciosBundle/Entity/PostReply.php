<?php

namespace Application\AnunciosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\AnunciosBundle\Entity\PostReply
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PostReply
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
     * @var integer $post_id
     *
     * @ORM\Column(name="post_id", type="integer")
     */
    private $post_id;

    /**
     * @var integer $user_id
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $user_id;

    /**
     * @var text $body
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;
    
    /**
     * @var string $linkedin
     *
     * @ORM\Column(name="linkedin", type="string", length=255, nullable=true)
     */
    private $linkedin;

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
     * Set post_id
     *
     * @param integer $postId
     */
    public function setPostId($postId)
    {
        $this->post_id = $postId;
    }

    /**
     * Get post_id
     *
     * @return integer
     */
    public function getPostId()
    {
        return $this->post_id;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;
    }

    /**
     * Get linkedin
     *
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
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

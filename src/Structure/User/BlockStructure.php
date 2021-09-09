<?php
namespace App\Structure\User;

class BlockStructure {

    /**
     * @var array
     */
    private $blocking;

    /**
     * @var array
     */
    private $blockers;

    public function __construct(array $blocking, array $blockers)
    {
        $this->setBlocking($blocking);
        $this->setBlockers($blockers);
    }

    /**
     * @return array
     */
    public function getBlocking(): array
    {
        return $this->blocking;
    }

    /**
     * @param array $blocking
     */
    public function setBlocking(array $blocking): void
    {
        $this->blocking = $blocking;
    }

    /**
     * @return array
     */
    public function getBlockers(): array
    {
        return $this->blockers;
    }

    /**
     * @param array $blockers
     */
    public function setBlockers(array $blockers): void
    {
        $this->blockers = $blockers;
    }
}
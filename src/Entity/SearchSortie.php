<?php

namespace App\Entity;

use DateTime;
use App\Entity\Campus;
class SearchSortie
{

    /**
     * @var Campus | null
     */
    private  ?Campus $campus=null;

    /**
     * @var string | null
     */
    private $nomSortie;

    /**
     * @var DateTime | null
     */
    private $debutInterval;

    /**
     * @var DateTime | null
     */
    private $finInterval;

    /**
     * @return \App\Entity\Campus
     */
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    /**
     * @param \App\Entity\Campus $campus
     * @return SearchSortie
     */
    public function setCampus(\App\Entity\Campus $campus): SearchSortie
    {
        $this->campus = $campus;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomSortie(): ?string
    {
        return $this->nomSortie;
    }

    /**
     * @param string|null $nomSortie
     * @return SearchSortie
     */
    public function setNomSortie(?string $nomSortie): SearchSortie
    {
        $this->nomSortie = $nomSortie;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDebutInterval(): ?DateTime
    {
        return $this->debutInterval;
    }

    /**
     * @param DateTime $debutInterval
     * @return SearchSortie
     */
    public function setDebutInterval(DateTime $debutInterval): SearchSortie
    {
        $this->debutInterval = $debutInterval;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFinInterval(): ?DateTime
    {
        return $this->finInterval;
    }

    /**
     * @param DateTime $finInterval
     * @return SearchSortie
     */
    public function setFinInterval(DateTime $finInterval): SearchSortie
    {
        $this->finInterval = $finInterval;
        return $this;
    }



}
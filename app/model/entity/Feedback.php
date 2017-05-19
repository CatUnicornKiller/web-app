<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Feedback
 *
 * @ORM\Entity
 */
class Feedback
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $grade;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $saveDate;

    /**
     * @ORM\Column(type="date")
     */
    protected $startDate;

    /**
     * @ORM\Column(type="date")
     */
    protected $endDate;

    /**
     * @ORM\Column(type="text")
     */
    protected $hostCity;

    /**
     * @ORM\Column(type="text")
     */
    protected $hostFaculty;

    /**
     * @ORM\Column(type="text")
     */
    protected $hostDepartment;

    /**
     * @ORM\Column(type="text")
     */
    protected $exchangeType;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $preparationVisa;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $preparationVaccination;

    /**
     * @ORM\Column(type="text")
     */
    protected $preparationComplications;

    /**
     * @ORM\Column(type="text")
     */
    protected $preparationMoney;

    /**
     * @ORM\Column(type="text")
     */
    protected $accommodation;

    /**
     * @ORM\Column(type="text")
     */
    protected $cpHelp;

    /**
     * @ORM\Column(type="text")
     */
    protected $exchangeCommunication;

    /**
     * @ORM\Column(type="text")
     */
    protected $socialTravelling;

    /**
     * @ORM\Column(type="text")
     */
    protected $socialProgram;

    /**
     * @ORM\Column(type="text")
     */
    protected $furtherTips;

    /**
     * @ORM\Column(type="text")
     */
    protected $overallReview;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     */
    protected $country;


    public function __construct(
        $name,
        $grade,
        Country $host_country,
        $host_city,
        $host_faculty,
        $host_department,
        $start_date,
        $end_date,
        $exchange_type,
        $preparation_visa,
        $preparation_vaccination,
        $preparation_complications,
        $preparation_money,
        $accommodation,
        $cp_help,
        $exchange_communication,
        $social_travelling,
        $social_program,
        $further_tips,
        $overall_review
    ) {
        $this->name = $name;
        $this->grade = $grade;
        $this->saveDate = new \DateTime;
        $this->startDate = $start_date;
        $this->endDate = $end_date;
        $this->country = $host_country;
        $this->hostCity = $host_city;
        $this->hostFaculty = $host_faculty;
        $this->hostDepartment = $host_department;
        $this->exchangeType = $exchange_type;
        $this->preparationVisa = $preparation_visa;
        $this->preparationVaccination = $preparation_vaccination;
        $this->preparationComplications = $preparation_complications;
        $this->preparationMoney = $preparation_money;
        $this->accommodation = $accommodation;
        $this->cpHelp = $cp_help;
        $this->exchangeCommunication = $exchange_communication;
        $this->socialTravelling = $social_travelling;
        $this->socialProgram = $social_program;
        $this->furtherTips = $further_tips;
        $this->overallReview = $overall_review;
    }

    public function delete()
    {
        $this->deleted = true;
    }
}

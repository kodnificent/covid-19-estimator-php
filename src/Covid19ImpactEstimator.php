<?php

namespace Kodnificent\Covid19ImpactEstimator;

use InvalidArgumentException;

class Covid19ImpactEstimator
{

    /**
     * The region name of the input data
     * 
     * @var string
     */
    protected $region_name;


    /**
     * The average age of the given region
     * 
     * @var int
     */
    protected $region_avg_age;

    /**
     * The average daily income in usd of the given region
     * 
     * @var int
     */
    protected $region_avg_daily_income_in_usd;

    /**
     * The average daily income population of the given region
     * 
     * @var int
     */
    protected $region_avg_daily_income_population;

    /**
     * The period type
     * 
     * @var string
     */
    protected $period_type;

    /**
     * Time to elapse
     * 
     * @var int
     */
    protected $time_to_elapse;

    /**
     * Number of reported cases
     * 
     * @var int
     */
    protected $reported_cases;

    /**
     * Population size
     * 
     * @var int
     */
    protected $population;

    /**
     * Total number of hospital beds
     * 
     * @var int
     */
    protected $total_hospital_beds;

    /**
     * The input data received
     * 
     * @var array
     */
    protected $data;

    /**
     * Best case estimation
     * 
     * @var array
     */
    protected $impact;

    /**
     * Severe case estimation
     * 
     * @var array
     */
    protected $severe_impact;

    /**
     * Create an instance of the Covid19ImpactEstimator class
     * 
     * @param array $data
     * @return $this
     */
    public function __construct(array $data)
    {
        $this->data = $this->validateData($data);
        
        return $this;
    }

    /** 
     * Get the result of the impact estimation
     * 
     * @return array
    */
    public function getResult()
    {
        $data = $this->data;

        $impact = [
            'currentlyInfected' =>  $this->getCurrentlyInfected(),
            'infectionsByRequestedTime' => $this->getInfectionsByRequestedTime(),
            'severeCasesByRequestedTime' => $this->getSevereCasesByRequestedTime(),
            'hospitalBedsByRequestedTime' => $this->getHospitalBedsByRequestedTime(),
            'casesForICUByRequestedTime' => $this->getCasesForICUByRequestedTime(),
            'casesForVentilatorsByRequestedTime' => $this->getCasesForVentilatorsByRequestedTime(),
            'dollarsInFlight'   =>  $this->getDollarsInFlight(),
        ];

        $severeImpact = [
            'currentlyInfected' =>  $this->getCurrentlyInfected(true),
            'infectionsByRequestedTime' => $this->getInfectionsByRequestedTime(true),
            'severeCasesByRequestedTime' => $this->getSevereCasesByRequestedTime(true),
            'hospitalBedsByRequestedTime' => $this->getHospitalBedsByRequestedTime(true),
            'casesForICUByRequestedTime' => $this->getCasesForICUByRequestedTime(true),
            'casesForVentilatorsByRequestedTime' => $this->getCasesForVentilatorsByRequestedTime(true),
            'dollarsInFlight'   =>  $this->getDollarsInFlight(true),
        ];

        $result = compact('data', 'impact', 'severeImpact');

        return $result;
    }

    /**
     * Run an estimate of the impact of COVID19
     * with the given data
     * 
     * @param array $data
     * @return array
     */
    public static function estimate($data)
    {
        $estimator = (new static($data));

        return $estimator->getResult();
    }

    /**
     * Get the number of currently infected persons
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getCurrentlyInfected(bool $is_severe_impact = false)
    {
        return $is_severe_impact ? $this->reported_cases * 50 : $this->reported_cases * 10;
    }

    /**
     * Get the number of possible infections by the requested time
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getInfectionsByRequestedTime(bool $is_severe_impact = false)
    {
        $currently_infected = $this->getCurrentlyInfected($is_severe_impact);

        $time_to_elapse = $this->normalizeTimeToElapse();

        $exp = floor($time_to_elapse / 3);

        return $currently_infected * pow(2, $exp);
    }

    /**
     * Get the estimated number of severe positive
     * cases that will require ICU care
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getSevereCasesByRequestedTime(bool $is_severe_impact = false)
    {
        return (int) ((15 / 100) * $this->getInfectionsByRequestedTime($is_severe_impact));
    }

    /**
     * Get an estimate of the number of available hospital beds
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getHospitalBedsByRequestedTime(bool $is_severe_impact = false)
    {
        // only about 35% of beds available for severe case patients
        $available_hospital_beds = (35/100) * $this->total_hospital_beds;

        // the remaining hospital beds after beds have been allocated to severe patients
        $hospital_beds = $available_hospital_beds - $this->getSevereCasesByRequestedTime($is_severe_impact);

        return (int) $hospital_beds;
    }

    /**
     * Get the estimated number of severe cases that will require
     * ICU care
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getCasesForICUByRequestedTime(bool $is_severe_impact = false)
    {
        return (int) ((5/100) * $this->getInfectionsByRequestedTime($is_severe_impact));
    }

    /**
     * Get the estimated number of server positive cases
     * that will require ventilators
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getCasesForVentilatorsByRequestedTime(bool $is_severe_impact = false)
    {
        return (int) ((2 / 100) * $this->getInfectionsByRequestedTime($is_severe_impact));
    }

    /**
     * Get an estimate of how much money the economy
     * is likely to loose over the given period
     * 
     * @param bool $is_severe_impact
     * @return int
     */
    public function getDollarsInFlight(bool $is_severe_impact = false)
    {
        return (int) (
            (
                $this->getInfectionsByRequestedTime($is_severe_impact) 
                * $this->region_avg_daily_income_population * $this->region_avg_daily_income_in_usd
            ) / $this->normalizeTimeToElapse()
        );
    }

    /**
     * Normalize the time to elapse to days
     * 
     * @return void
     */
    protected function normalizeTimeToElapse()
    {
        
        switch ($this->period_type) {
            case 'weeks':
                // 7 days in a week
                $time_to_elapse = 7 * $this->time_to_elapse;
                break;
            
            case 'months':
                // 30 days in 1 month
                $time_to_elapse = 30 * $this->time_to_elapse;
                break;
            
            default:
                $time_to_elapse = $this->time_to_elapse;
                break;
        }

        return $time_to_elapse;
    }

    /**
     * Validate the given input data and assign properties
     * 
     * @param array $data
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    protected function validateData($data)
    {
        $this->region_name = $data['region']['name'];

        $this->region_avg_age = (int) $data['region']['avgAge'];

        $this->region_avg_daily_income_in_usd = (float) $data['region']['avgDailyIncomeInUSD'];

        $this->region_avg_daily_income_population = (float) $data['region']['avgDailyIncomePopulation'];
        
        $valid_periods = ['days', 'weeks', 'months'];

        $period_type = $data['periodType'];

        if(!in_array($period_type, $valid_periods)){

            throw new InvalidArgumentException("periodType must be days, weeks or months");
        }

        $this->period_type = $period_type;

        $this->time_to_elapse = (int) $data['timeToElapse'];

        $this->reported_cases = (int) $data['reportedCases'];

        $this->population = (int) $data['population'];

        $this->total_hospital_beds = (int) $data['totalHospitalBeds'];

        return $data;
    }
}
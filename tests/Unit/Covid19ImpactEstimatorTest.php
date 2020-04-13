<?php 

namespace Kodnificent\Covid19ImpactEstimator\Tests\Unit;

use InvalidArgumentException;
use Kodnificent\Covid19ImpactEstimator\Covid19ImpactEstimator;
use PHPUnit\Framework\TestCase;

class EstimatorTest extends TestCase
{
    /**
     * Estimator instance
     * 
     * @var \Kodnificent\Covid19ImpactEstimator\Covid19ImpactEstimator
     */
    private $estimator;

    /**
     * Mock input data
     * 
     * @var array
     */
    private $data;

    protected function setUp(): void
    {
        $this->data = getMockInput();

        $this->estimator = new Covid19ImpactEstimator($this->data);
    }

    protected function tearDown(): void
    {
        unset($this->data, $this->estimator);
    }

    public function testDataPeriodTypeShouldFailValidation()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $this->data['periodType'] = 'year';
        $this->estimator = new Covid19ImpactEstimator($this->data);
    }

    public function testGetCurrentInfectedFunction()
    {
        // test for best case
        $impact = $this->estimator->getCurrentlyInfected();
        $this->assertEquals(getMockImpactValue('currentlyInfected'), $impact);

        // test for severe impact
        $severe = $this->estimator->getCurrentlyInfected(true);
        $this->assertEquals(getMockSevereImpactValue('currentlyInfected'), $severe);
    }

    public function testInfectionsByRequestedTime()
    {
        // test for best case
        $impact = $this->estimator->getInfectionsByRequestedTime();
        $this->assertEquals(getMockImpactValue('infectionsByRequestedTime'), $impact);

        // test for servere impact
        $severe = $this->estimator->getInfectionsByRequestedTime(true);
        $this->assertEquals(getMockSevereImpactValue('infectionsByRequestedTime'), $severe);
    }

    public function testSevereCasesByRequestedTime()
    {
        $impact = $this->estimator->getSevereCasesByRequestedTime();
        $this->assertEquals(getMockImpactValue('severeCasesByRequestedTime'), $impact);

        $severe = $this->estimator->getSevereCasesByRequestedTime(true);
        $this->assertEquals(getMockSevereImpactValue('severeCasesByRequestedTime'), $severe);
    }

    public function testGetHospitalBedsByRequestedTime()
    {
        $impact = $this->estimator->getHospitalBedsByRequestedTime();
        $this->assertEquals(getMockImpactValue('hospitalBedsByRequestedTime'), $impact);

        $severe = $this->estimator->getHospitalBedsByRequestedTime(true);
        $this->assertEquals(getMockSevereImpactValue('hospitalBedsByRequestedTime'), $severe);
    }

    public function testGetCasesForICUByRequestedTime()
    {
        $impact = $this->estimator->getCasesForICUByRequestedTime();
        $this->assertEquals(getMockImpactValue('casesForICUByRequestedTime'), $impact);
        
        $severe = $this->estimator->getCasesForICUByRequestedTime(true);
        $this->assertEquals(getMockSevereImpactValue('casesForICUByRequestedTime'), $severe);
    }

    public function testGetCasesForVentilatorsByRequestedTime()
    {
        $impact = $this->estimator->getCasesForVentilatorsByRequestedTime();
        $this->assertEquals(getMockImpactValue('casesForVentilatorsByRequestedTime'), $impact);
    
        $severe = $this->estimator->getCasesForVentilatorsByRequestedTime(true);
        $this->assertEquals(getMockSevereImpactValue('casesForVentilatorsByRequestedTime'), $severe);
    }

    public function testGetDollarsInFlight()
    {
        $impact = $this->estimator->getDollarsInFlight();
        $this->assertEquals(getMockImpactValue('dollarsInFlight'), $impact);
    
        $severe = $this->estimator->getDollarsInFlight(true);
        $this->assertEquals(getMockSevereImpactValue('dollarsInFlight'), $severe);
    }

    public function testGetResult()
    {
        $result = $this->estimator->getResult();
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('impact', $result);
        $this->assertArrayHasKey('severeImpact', $result);

        $impact = $result['impact'];
        $severeImpact = $result['severeImpact'];

        $this->assertArrayHasKey('currentlyInfected', $impact);
        $this->assertArrayHasKey('infectionsByRequestedTime', $impact);
        $this->assertArrayHasKey('severeCasesByRequestedTime', $impact);
        $this->assertArrayHasKey('hospitalBedsByRequestedTime', $impact);
        $this->assertArrayHasKey('casesForICUByRequestedTime', $impact);
        $this->assertArrayHasKey('casesForVentilatorsByRequestedTime', $impact);
        $this->assertArrayHasKey('dollarsInFlight', $impact);
        
        $this->assertArrayHasKey('currentlyInfected', $severeImpact);
        $this->assertArrayHasKey('infectionsByRequestedTime', $severeImpact);
        $this->assertArrayHasKey('severeCasesByRequestedTime', $severeImpact);
        $this->assertArrayHasKey('hospitalBedsByRequestedTime', $severeImpact);
        $this->assertArrayHasKey('casesForICUByRequestedTime', $severeImpact);
        $this->assertArrayHasKey('casesForVentilatorsByRequestedTime', $severeImpact);
        $this->assertArrayHasKey('dollarsInFlight', $severeImpact);
    }
}
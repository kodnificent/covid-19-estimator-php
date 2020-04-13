<?php

/**
 * Get test mock data
 * 
 * @return array
 */
function getMockData()
{
    $mock_data = file_get_contents('mock-result.json', true);

    $data = json_decode($mock_data, true);

    return $data;
}

/**
 * Get input data
 * 
 * @return array
 */
function getMockInput()
{
    $input_data = getMockData()['data'];

    return $input_data;
}

/**
 * Get the impact data of the mock result
 * 
 * @return array
 */
function getMockImpact()
{
    return getMockData()['impact'];
}

/**
 * Get the severe impact data of the mock result
 * 
 * @return array
 */
function getMockSevereImpact()
{
    return getMockData()['severeImpact'];
}

/**
 * Get the value of an impact property
 * 
 * @param string $property
 * @return mixed
 */
function getMockImpactValue(string $property)
{
    return getMockImpact()[$property];
}

/**
 * Get the value of a severe impact property
 * 
 * @param string $property
 * @return mixed
 */
function getMockSevereImpactValue(string $property)
{
    return getMocksevereImpact()[$property];
}
<?php

use Kodnificent\Covid19ImpactEstimator\Covid19ImpactEstimator;

/**
 * Get an estimate of the impact of COVID19 based on the given data
 * 
 * @param array $data
 * @return array
 */
function covid19ImpactEstimator(array $data)
{
  return Covid19ImpactEstimator::estimate($data);
}
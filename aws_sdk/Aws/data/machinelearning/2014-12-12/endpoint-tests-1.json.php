<?php
// This file was auto-generated from sdk-root/src/data/machinelearning/2014-12-12/endpoint-tests-1.json
return [ 'testCases' => [ [ 'documentation' => 'For region eu-west-1 with FIPS enabled and DualStack enabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning-fips.eu-west-1.api.aws', ], ], 'params' => [ 'Region' => 'eu-west-1', 'UseDualStack' => true, 'UseFIPS' => true, ], ], [ 'documentation' => 'For region eu-west-1 with FIPS enabled and DualStack disabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning-fips.eu-west-1.amazonaws.com', ], ], 'params' => [ 'Region' => 'eu-west-1', 'UseDualStack' => false, 'UseFIPS' => true, ], ], [ 'documentation' => 'For region eu-west-1 with FIPS disabled and DualStack enabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning.eu-west-1.api.aws', ], ], 'params' => [ 'Region' => 'eu-west-1', 'UseDualStack' => true, 'UseFIPS' => false, ], ], [ 'documentation' => 'For region eu-west-1 with FIPS disabled and DualStack disabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning.eu-west-1.amazonaws.com', ], ], 'params' => [ 'Region' => 'eu-west-1', 'UseDualStack' => false, 'UseFIPS' => false, ], ], [ 'documentation' => 'For region us-east-1 with FIPS enabled and DualStack enabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning-fips.us-east-1.api.aws', ], ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => true, 'UseFIPS' => true, ], ], [ 'documentation' => 'For region us-east-1 with FIPS enabled and DualStack disabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning-fips.us-east-1.amazonaws.com', ], ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => false, 'UseFIPS' => true, ], ], [ 'documentation' => 'For region us-east-1 with FIPS disabled and DualStack enabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning.us-east-1.api.aws', ], ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => true, 'UseFIPS' => false, ], ], [ 'documentation' => 'For region us-east-1 with FIPS disabled and DualStack disabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://machinelearning.us-east-1.amazonaws.com', ], ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => false, 'UseFIPS' => false, ], ], [ 'documentation' => 'For custom endpoint with fips disabled and dualstack disabled', 'expect' => [ 'endpoint' => [ 'url' => 'https://example.com', ], ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => false, 'UseFIPS' => false, 'Endpoint' => 'https://example.com', ], ], [ 'documentation' => 'For custom endpoint with fips enabled and dualstack disabled', 'expect' => [ 'error' => 'Invalid Configuration: FIPS and custom endpoint are not supported', ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => false, 'UseFIPS' => true, 'Endpoint' => 'https://example.com', ], ], [ 'documentation' => 'For custom endpoint with fips disabled and dualstack enabled', 'expect' => [ 'error' => 'Invalid Configuration: Dualstack and custom endpoint are not supported', ], 'params' => [ 'Region' => 'us-east-1', 'UseDualStack' => true, 'UseFIPS' => false, 'Endpoint' => 'https://example.com', ], ], ], 'version' => '1.0',];

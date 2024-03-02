<?php

namespace App\Controller\Tests;

use function PHPUnit\Framework\throwException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
  # config/routes/test_newrelic_routes.yaml (manual include)

    TestNewRelicError:
        path: /_test/newrelic/error
        defaults:
            _controller: App\Controller\Tests\TestNewRelic::testError

    TestNewRelicMetric:
        path: /_test/newrelic/error
        defaults:
            _controller: App\Controller\Tests\TestNewRelic::testMetric
*/

class TestNewRelic extends AbstractController {

    public function testError() {
        if (extension_loaded('newrelic')) {
            try {
                throw new \Exception("Exception with newrelic_notice_error()");
            } catch (\Exception $e) {
                newrelic_notice_error($e);
            }
            return new Response("Check newrelic errors!");
        }
        return new Response("Nope.. newrelic not installed");
    }

    /**
     * @Route("/metric", name="_metric")
     */
    public function testMetric() {
        $newrelicMetric = "Custom/Test/Metrics";
        if (extension_loaded('newrelic')) {
            newrelic_custom_metric($newrelicMetric, 1);
            return new Response("Check newrelic metrics ".$newrelicMetric);
        }
        return new Response("Nope.. newrelic not installed");
    }
}


?>
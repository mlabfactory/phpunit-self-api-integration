<?php
declare(strict_types=1);

namespace MLAB\PHPITest\Assertions;

use MLAB\PHPITest\Entity\Json;
use MLAB\PHPITest\Service\HttpResponse;

final class HttpAssert extends JsonAssert {

    private readonly Assertions $assertions;

    public function __construct(HttpResponse $json)
    {   
        $content = json_decode($json->request->getContent());
        $this->assertions = new Assertions($content);
    }

    public static function assertHttpResponse(HttpResponse $response)
    {
        $assertions = new self($response);
        return $assertions;
    }

}
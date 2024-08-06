<?php

namespace MLAB\PHPITest\Service;

use DateTime;
use PHPUnit\Framework\Assert;
use MLAB\PHPITest\Service\HttpRequest;
use MLAB\PHPITest\Assertions\Assertions;
use MLAB\PHPITest\Constraint\StatusCode;

class HttpResponse extends Assertions
{

    use StatusCode;
    public readonly HttpRequest $request;
    protected mixed $decoded;

    public function __construct(HttpRequest $request, mixed $decoded = null)
    {
        $this->request = $request;
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful()
    {
        if ($this->request->isSuccessful()) {
            $this->isTrue();
        }

        $this->statusMessageWithDetails('>=200, <300', $this->request->getStatusCode());

        return $this;
    }

    /**
     * Assert that the Precognition request was successful.
     *
     * @return $this
     */
    public function assertSuccessfulPrecognition()
    {
        $this->assertNoContent();

        $this->assertTrue(
            !is_null($this->request->getHeaders()['Precognition-Success']),
            'Header [Precognition-Success] not present on response.'
        );

        return $this;
    }

    /**
     * Assert that the response is a server error.
     *
     * @return $this
     */
    public function assertServerError()
    {
        $this->assertTrue(
            $this->request->isSuccessful() === false,
            $this->statusMessageWithDetails('>=500, < 600', $this->request->getStatusCode())
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param  int  $status
     * @return $this
     */
    public function assertStatus($status)
    {
        $message = $this->statusMessageWithDetails($status, $actual = $this->request->getStatusCode());

        $this->assertSame($actual, $status, $message);

        return $this;
    }

    /**
     * Get an assertion message for a status assertion containing extra details when available.
     *
     * @param  string|int  $expected
     * @param  string|int  $actual
     * @return string
     */
    protected function statusMessageWithDetails($expected, $actual)
    {
        return "Expected response status code [{$expected}] but received {$actual}.";
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param  string|null  $uri
     * @return $this
     */
    public function assertRedirect($uri = null)
    {
        $this->assertTrue(
            $this->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->request->getStatusCode()),
        );

        if (!is_null($uri)) {
            $this->assertLocation($uri);
        }

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a URI that contains the given URI.
     *
     * @param  string  $uri
     * @return $this
     */
    public function assertRedirectContains($uri)
    {
        $this->assertTrue(
            $this->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->request->getStatusCode()),
        );

        $this->assertEquals(
            $uri,
            $this->request->getHeaders()[\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER]
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param  string  $headerName
     * @param  mixed  $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {
        $this->assertTrue(
            $this->request->getHeaders()[$headerName],
            "Header [{$headerName}] not present on response."
        );

        $actual = $this->request->getHeaders()[$headerName];

        if (!is_null($value)) {
            $this->assertEquals(
                $value,
                $this->request->getHeaders()[$headerName],
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response does not contain the given header.
     *
     * @param  string  $headerName
     * @return $this
     */
    public function assertHeaderMissing($headerName)
    {
        $this->assertFalse(
            $this->request->getHeaders()[$headerName],
            "Unexpected header [{$headerName}] is present on response."
        );

        return $this;
    }

    /**
     * Assert that the current location header matches the given URI.
     *
     * @param  string  $uri
     * @return $this
     */
    public function assertLocation($uri)
    {
        $this->assertEquals(
            $uri,
            $this->request->getHeaders()[\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER]
        );

        return $this;
    }

    /**
     * Assert that the response offers a file download.
     *
     * @param  string|null  $filename
     * @return $this
     */
    public function assertDownload($filename = null)
    {
        $contentDisposition = explode(';', $this->request->getHeaders()['content-disposition']);

        if (trim($contentDisposition[0]) !== 'attachment') {
            $this->fail(
                'Response does not offer a file download.' . PHP_EOL .
                    'Disposition [' . trim($contentDisposition[0]) . '] found in header, [attachment] expected.'
            );
        }

        if (!is_null($filename)) {
            if (
                isset($contentDisposition[1]) &&
                trim(explode('=', $contentDisposition[1])[0]) !== 'filename'
            ) {
                $this->fail(
                    'Unsupported Content-Disposition header provided.' . PHP_EOL .
                        'Disposition [' . trim(explode('=', $contentDisposition[1])[0]) . '] found in header, [filename] expected.'
                );
            }

            $message = "Expected file [{$filename}] is not present in Content-Disposition header.";

            if (!isset($contentDisposition[1])) {
                $this->fail($message);
            } else {
                $this->assertSame(
                    $filename,
                    isset(explode('=', $contentDisposition[1])[1])
                        ? trim(explode('=', $contentDisposition[1])[1], " \"'")
                        : '',
                    $message
                );

                return $this;
            }
        } else {
            $this->assertTrue(true);

            return $this;
        }
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string  $cookieName
     * @param  mixed  $value
     * @return $this
     */
    public function assertPlainCookie($cookieName, $value = null)
    {
        $this->assertCookie($cookieName, $value);

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string  $cookieName
     * @param  mixed  $value
     * @param  bool  $encrypted
     * @param  bool  $unserialize
     * @return $this
     */
    public function assertCookie($cookieName, $value = null)
    {
        $this->assertNotNull(
            $cookie = $this->request->getCookies($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        if (!$cookie || is_null($value)) {
            return $this;
        }

        $cookieValue = $cookie->getValue();

        $this->assertEquals(
            $value,
            $cookieValue,
            "Cookie [{$cookieName}] was found, but value [{$cookieValue}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is expired.
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieExpired($cookieName)
    {
        $this->assertNotNull(
            $cookie = $this->request->getCookies($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        $expiresAt = new DateTime($cookie->getExpiresTime());

        $this->assertTrue(
            $cookie->getExpiresTime() !== 0 && $expiresAt() <= new DateTime(),
            "Cookie [{$cookieName}] is not expired, it expires at [{$expiresAt}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is not expired.
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieNotExpired($cookieName)
    {
        $this->assertNotNull(
            $cookie = $this->request->getCookies($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        /** @var $cookie  */
        $expiresAt = new DateTime($cookie->getExpiresTime());

        $this->assertTrue(
            $cookie->getExpiresTime() === 0 || $expiresAt >= new DateTime(),
            "Cookie [{$cookieName}] is expired, it expired at [{$expiresAt}]."
        );

        return $this;
    }

    /**
     * Asserts that the response does not contain the given cookie.
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieMissing($cookieName)
    {
        $this->assertNull(
            $this->request->getCookies($cookieName),
            "Cookie [{$cookieName}] is present on response."
        );

        return $this;
    }

    /**
     * Assert that the given string matches the response content.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertContent($value)
    {
        $this->assertSame($value, $this->request->getContent());

        return $this;
    }

    /**
     * Assert that the given string matches the streamed response content.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertStreamedContent($value)
    {
        $this->assertSame($value, $this->request->getContent());

        return $this;
    }


    /**
     * Assert that the given string or array of strings are contained within the response text.
     *
     * @param  array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeText($values)
    {
        foreach ($values as $value) {
            $this->assertStringContainsString((string) $value, $this->request->getContent());
        }

        return $this;
    }


    /**
     * Assert that the given string or array of strings are not contained within the response.
     *
     * @param  string|array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertDontSee(array $values)
    {
        foreach ($values as $value) {
            $this->assertStringNotContainsString((string) $value, $this->request->getContent());
        }
        return $this;
    }

    /**
     * Assert that the given string or array of strings are not contained within the response text.
     *
     * @param  array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertDontSeeText(array $values)
    {
        foreach ($values as $value) {
            $this->assertStringNotContainsString((string) $value, $this->request->getContent());
        }

        return $this;
    }

    /**
     * Get the content of the HTTP response.
     *
     * @return string The content of the HTTP response.
     */
    public function getContent()
    {
        return $this->request->getContent();
    }

    /**
     * Checks if the HTTP response is a redirect.
     *
     * @return bool Returns true if the response is a redirect, false otherwise.
     */
    private function isRedirect(): bool
    {
        $code = $this->request->getStatusCodeRedirect();
        $assertCode = ['201', '301', '302', '303', '307', '308'];

        if (in_array($code, $assertCode)) {
            return true;
        }
        return false;
    }
}

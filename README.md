# PHPUnit Self API Integration Test

PHPUnit Self API Integration Test is a PHPUnit extension tailored for executing integration tests on self-contained APIs within PHP applications. This extension simplifies the process of testing API endpoints within the same system, ensuring smooth integration and functionality.

## Features
- Enables seamless integration testing of API endpoints within PHP applications.
- Provides a straightforward approach to testing APIs within the same system.
- Compatible with PHPUnit, ensuring ease of use and integration into existing testing workflows.

## Installation
You can install the PHPUnit Self API Integration Test extension via Composer:

```bash
composer require mlabfactory/phpunit-self-api-integration-test
````

## Usage
- Extend your PHPUnit test case classes with the provided base test case class.
- Utilize the provided methods to make HTTP requests to your API endpoints and perform assertions on the responses.
- Run your PHPUnit tests as usual.

### Here's an example test case using the PHPUnit Self API Integration Test extension:

```bash
use PHPUnit\Framework\TestCase;
use MLAB\PHPITest\Service\HttpRequest;

class MyApiIntegrationTest extends TestCase
{
    public function my_test(): void
    {
        $request = new HttpRequest();

        $response = $request->get("/my/custom/api");

        $response->assertStatus(200);
        $response->assertJsonStructure($fixture);
    }
}
```

## Contributing
Contributions are welcome! Please fork the repository, make your changes, and submit a pull request. Ensure you've added appropriate tests and documentation for any new features or changes.

## License
This project is licensed under the MIT License - see the LICENSE file for details.

## Support
For any questions, issues, or feedback, please open an issue on GitHub or contact Your Name.

Feel free to adjust the content and structure as needed for your project!
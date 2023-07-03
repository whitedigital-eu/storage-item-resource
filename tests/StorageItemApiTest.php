<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function sprintf;

class StorageItemApiTest extends ApiTestCase
{
    protected HttpClientInterface $client;
    protected ContainerInterface $container;

    protected string $iri = '/api/storage_items';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Depends('testGetItem')]
    public function testDeleteItem(int $id): void
    {
        $this->client->request(Request::METHOD_DELETE, sprintf('%s/%d', $this->iri, $id));
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPostItem(): int
    {
        $templateFileName = dirname(__DIR__) . '/src/DataFixture/assets/storage_item-fixture.template.txt';
        $fileName = dirname(__DIR__) . '/src/DataFixture/assets/uploaded-storage_item-fixture.txt';
        copy($templateFileName, $fileName);

        $response = $this->client->request(Request::METHOD_POST, $this->iri, [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'extra' => [
                'files' => [
                    'file' => new UploadedFile($fileName, 'uploaded-storage_item-fixture.txt', 'text/plain'),
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return json_decode($response->getContent())->id;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testPostItemFileError(): void
    {
        $this->client->request(Request::METHOD_POST, $this->iri, [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'extra' => [
                'files' => [
                    'file' => [''],
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testPostItemEmpty(): void
    {
        $this->client->request(Request::METHOD_POST, $this->iri, [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     *
     * @depends testPostItem
     */
    public function testGetItem(int $id): int
    {
        $response = $this->client->request(Request::METHOD_GET, sprintf('%s/%d', $this->iri, $id));

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['@id' => sprintf('%s/%d', $this->iri, $id)]);

        return json_decode($response->getContent())->id;
    }
}

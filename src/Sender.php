<?php

declare(strict_types=1);

namespace PhpMob\ThaiBulkSms;

use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Sender implements SenderInterface
{
    const VERSION = '1.0@dev';
    const BASE_URL = 'https://www.thaibulksms.com';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    /**
     * @var array
     */
    private $options = [];

    public function __construct(HttpClient $httpClient, array $options)
    {
        $this->httpClient = $httpClient;

        $this->resolver = new OptionsResolver();
        $this->configure($this->resolver);
        $this->options = $this->resolver->resolve($options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function configure(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'force' => 'standard',
                'api_path' => '/sms_api.php',
                'sandbox' => true,
                'sandbox_api_path' => '/sms_api_test.php',
            ])
            ->setRequired(['username', 'password', 'sender'])
            ->setAllowedTypes('username', 'string')
            ->setAllowedTypes('password', 'string')
            ->setAllowedTypes('sender', 'string')
            ->setAllowedTypes('force', 'string')
            ->setAllowedValues('force', ['standard', 'premium'])
            ->setAllowedTypes('sandbox', 'boolean');
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function resolverRuntimeOptions(array $options): array
    {
        if (empty($options)) {
            return $this->options;
        }

        return $this->resolver->resolve(array_replace_recursive($this->options, $options));
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Result
     */
    private function serializeResponse(ResponseInterface $response): Result
    {
        $serializer = new Serializer([new PropertyNormalizer()], [new XmlEncoder()]);
        $contents = $response->getBody()->getContents();

        if (preg_match_all('|<QUEUE>(.*?)</QUEUE>|ims', $contents, $matches)) {
            $result = new Result();

            foreach ($matches[0] as $content) {
                /** @var Result $object */
                $object = $serializer->deserialize($content, Result::class, 'xml');

                $result->Queues[] = $object;

                if ($object->isOk()) {
                    $result->Status = 1;
                }
            }

            return $result;
        }

        return $serializer->deserialize($contents, Result::class, 'xml');
    }

    /**
     * @param array $body
     * @param array $options
     *
     * @return Result
     *
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    private function sendRequest(array $body, array $options): Result
    {
        $uri = self::BASE_URL . ($options['sandbox'] ? $options['sandbox_api_path'] : $options['api_path']);

        $body = array_replace($body, [
            'username' => $options['username'],
            'password' => $options['password'],
        ]);

        $headers = [
            'User-Agent' => 'PhpMob/ThaiBulkSms#' . self::VERSION,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        return $this->serializeResponse($this->httpClient->sendRequest(
            MessageFactoryDiscovery::find()->createRequest('POST', $uri, $headers, http_build_query($body))
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function send($numbers, string $message, ?\DateTimeInterface $scheduled = null, array $options = []): Result
    {
        if (!is_array($numbers)) {
            $numbers = [$numbers];
        }

        if (null !== $scheduled) {
            $scheduled = $scheduled->format('Ymdhi');
        }

        $options = $this->resolverRuntimeOptions($options);

        $body = [
            'msisdn' => implode(',', $numbers),
            'message' => $message,
            'ScheduledDelivery' => $scheduled,
            'sender' => $options['sender'],
            'force' => $options['force'],
        ];

        return $this->sendRequest($body, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredit(array $options = []): Result
    {
        $options = $this->resolverRuntimeOptions($options);

        $body = [
            'tag' => 'credit_remain' . ('premium' === $options['force'] ? '_premium' : ''),
        ];

        return $this->sendRequest($body, $options);
    }
}

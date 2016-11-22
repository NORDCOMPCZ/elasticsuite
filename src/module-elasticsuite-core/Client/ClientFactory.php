<?php
/**
 * DISCLAIMER :
 *
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile_Elasticsuite
 * @package   Smile\ElasticsuiteCore
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCore\Client;

use Psr\Log\LoggerInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Smile\ElasticsuiteCore\Api\Client\ClientConfigurationInterface;
use Smile\ElasticsuiteCore\Api\Client\ClientFactoryInterface;

/**
 * Provides a simple way to retrieve an Elasticsearch client.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCore
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var ClientConfigurationInterface
     */
    private $clientConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * Factory constructor.
     *
     * @param ClientConfigurationInterface $clientConfiguration Elasticsearch configuration helper.
     * @param LoggerInterface              $logger              Elasticsearch logger.
     */
    public function __construct(ClientConfigurationInterface $clientConfiguration, LoggerInterface $logger)
    {
        $this->clientConfiguration = $clientConfiguration;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function createClient()
    {
        if ($this->client === null) {
            $clientBuilder = ClientBuilder::create();
            $clientBuilder->setHosts($this->clientConfiguration->getServerList());
            if ($this->clientConfiguration->isHttpAuthEnabled()
                && !empty($this->clientConfiguration->getHttpAuthUser())
                && !empty($this->clientConfiguration->getHttpAuthPassword()) ) {
                foreach ($this->clientConfiguration->getServerList() as $host) {
                    $hosts[] = sprintf(
                        '%s://%s:%s@%s',
                        ($this->clientConfiguration->isHttpsEnabled() ? 'https' : 'http'),
                        $this->clientConfiguration->getHttpAuthUser(),
                        $this->clientConfiguration->getHttpAuthPassword(),
                        $host
                    );
                }
                $clientBuilder->setHosts($hosts);
            }

            if ($this->clientConfiguration->isDebugModeEnabled()) {
                $clientBuilder->setLogger($this->logger);
            }

            $this->client = $clientBuilder->build();
        }

        return $this->client;
    }
}

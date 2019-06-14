<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\InvalidArgumentException;
use Http\Message\MessageFactory\SlimMessageFactory;
use Http\Message\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractClient
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var ParameterBag
     */
    private $options;

    /**
     * @var ResponseFactoryInterface|ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param ResponseFactoryInterface|ResponseFactory $responseFactory
     */
    public function __construct($responseFactory = null, array $options = [])
    {
        $this->responseFactory = $responseFactory;
        
        if (!$responseFactory instanceof ResponseFactoryInterface && !$responseFactory instanceof ResponseFactory) {
            $this->responseFactory = new SlimMessageFactory();
        }

        $this->options = new ParameterBag();
        $this->options = $this->doValidateOptions($options);
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        if (null !== $this->optionsResolver) {
            return $this->optionsResolver;
        }

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);

        return $this->optionsResolver;
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    protected function validateOptions(array $options = []): ParameterBag
    {
        if (empty($options)) {
            return $this->options;
        }

        return $this->doValidateOptions($options);
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    private function doValidateOptions(array $options = []): ParameterBag
    {
        $parameterBag = $this->options->add($options);

        try {
            $parameters = $this->getOptionsResolver()->resolve($parameterBag->all());
        } catch (\Throwable $e) {
            // Wrap any errors.
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return new ParameterBag($parameters);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_redirects' => false,
            'max_redirects' => 5,
            'timeout' => 30,
            'verify' => true,
            'proxy' => null,
        ]);

        $resolver->setAllowedTypes('allow_redirects', 'boolean');
        $resolver->setAllowedTypes('verify', 'boolean');
        $resolver->setAllowedTypes('max_redirects', 'integer');
        $resolver->setAllowedTypes('timeout', ['integer', 'float']);
        $resolver->setAllowedTypes('proxy', ['null', 'string']);
    }
    
    public function setIgnoreErrors($ignoreErrors)
    {
    }
    
    public function getIgnoreErrors()
    {
    }
    
    public function setMaxRedirects($maxRedirects)
    {
        $this->options->add(['max_redirects', $maxRedirects]);
    }
    
    public function getMaxRedirects()
    {
        return $this->options->get('max_redirects');
    }
    
    public function setTimeout($timeout)
    {
        $this->options->add(['timeout', $timeout]);
    }
    
    public function getTimeout()
    {
        return $this->options->get('timeout');
    }
    
    public function setVerifyPeer($verifyPeer)
    {
        $this->options->add(['verify', $verifyPeer]);
    }
    
    public function getVerifyPeer()
    {
        return $this->options->get('verify');
    }
    
    public function getVerifyHost()
    {
        $this->options->add(['verify', true]);
    }
    
    public function setVerifyHost($verifyHost)
    {
        $this->options->add(['verify', $verifyHost]);
    }
    
    public function setProxy($proxy)
    {
        $this->options->add(['proxy', $proxy]);
    }
    public function getProxy()
    {
        return $this->options->get('proxy');
    }
}

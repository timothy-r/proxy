<?php namespace Ace;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Request as GuzzleRequest;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @author timrodger
 * Date: 15/11/15
 */
class Proxy
{
    /**
     * @var string
     */
    private $remote;

    private $path;

    /**
     * @param $remote string
     */
    public function __construct($remote, $path)
    {
        $this->remote = $remote;
        $this->path = $path;
    }

    /**
     *
     * @param Request $req
     */
    public function fromRequest(SymfonyRequest $inbound)
    {
        $uri = $this->remote;
        // remove $this->path from $inbound ->path
        $pattern = '#^' . $this->path . '#';
        $inbound_path = preg_replace($pattern, '', $inbound->getBasePath());
        $uri .= $inbound_path;

        $outbound = new GuzzleRequest(
            $inbound->getMethod(),
            $uri,
            $inbound->headers->all(),
            $inbound->getContent()
        );

        // make the proxy request to the configured remote server
        $client = new Client();
        $response = $client->send($outbound);

        // return a SymfonyResponse constructed from the GuzzleResponse

        return new SymfonyResponse(
            $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}
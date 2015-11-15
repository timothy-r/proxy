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
     * @param SymfonyRequest $req
     */
    public function fromRequest(SymfonyRequest $inbound)
    {
        $uri = $this->remote;
        // remove $this->path from $inbound ->path
        $pattern = '#^' . $this->path . '#';
        $inbound_path = preg_replace($pattern, '', $inbound->getRequestUri());
        $uri .= $inbound_path;

        $headers = $inbound->headers->all();

        unset($headers['host']);

        if (($inbound->getMethod() === 'GET' || $inbound->getMethod() === 'HEAD')) {
            unset($headers['content-type']);
            unset($headers['content-length']);
        }

        $outbound = new GuzzleRequest(
            $inbound->getMethod(),
            $uri,
            $headers
        );
//
//            $inbound->getContent(true)
//        );

        // make the proxy request to the configured remote server
        $client = new Client();
        $response = $client->send($outbound);

        // return a SymfonyResponse constructed from the GuzzleResponse

        // remove x-frame options - or set them to all?
        $response_headers = $response->getHeaders();
        unset($response_headers['x-frame-options']);
        unset($response_headers['X-FRAME-OPTIONS']);

        return new SymfonyResponse(
            $response->getBody(),
            $response->getStatusCode(),
            $response_headers
        );
    }
}
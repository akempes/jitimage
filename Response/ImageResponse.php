<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage\Response package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Response;

use \Thapp\Image\Resource\CachedResource;
use \Thapp\Image\Resource\ResourceInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @class ImageResponse extends Response
 * @see Response
 *
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Response
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImageResponse extends Response
{
    /**
     * useXsend
     *
     * @var boolean
     */
    protected $useXsend;

    /**
     * resource
     *
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * trustXSendFileHeader
     *
     * @var boolean
     */
    protected static $trustXSendFileHeader = true;

    /**
     * @param ResourceInterface $resource
     * @param int $status
     * @param array $headers
     */
    public function __construct(ResourceInterface $resource, $status = 200, array $headers = [])
    {
        $this->status   = $status;
        $this->resource = $resource;
        $this->headers  = new  ResponseHeaderBag($headers);
    }

    /**
     * create
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    public static function create($content = '', $status = 200, $headers = [])
    {
        if (!$content instanceof ResourceInterface) {
            throw new \InvalidArgumentException();
        }

        return new static($content, $status, $headers);
    }

    /**
     * trustXsendFileHeaders
     *
     * @param boolean $trust
     *
     * @return void
     *
     */
    public static function trustXsendFileHeaders($trust)
    {
        static::$trustXSendFileHeader = (bool)$trust;
    }

    /**
     * prepare
     *
     * @param Request $request
     *
     * @access public
     * @return void
     */
    public function prepare(Request $request)
    {
        $this->headers->set('Content-Transfer-Encoding', 'binary');

        $this->useXsend = static::$trustXSendFileHeader && $request->headers->has('X-Sendfile-Type');

        $lastMod = (new \DateTime)->setTimestamp($modDate = $this->resource->getLastModified());
        $mod = strtotime($request->headers->get('if-modified-since', $time = time()));

        if (($this->resource instanceof CachedResource || $this->resource->isFresh($time)) && $mod === $modDate) {
            $this->setHeadersIfNotProcessed($lastMod);
        } else {
            $this->setProcessedHeaders($this->resource, $lastMod);
        }
    }

    /**
     * void
     *
     * @param mixed $lastMod
     *
     * @access protected
     * @return mixed
     */
    protected function setHeadersIfNotProcessed($lastMod)
    {
        $this->setNotModified();
        $this->setLastModified($lastMod);
    }

    /**
     * setProcessedHeaders
     *
     * @param ResourceInterface $resouce
     * @param mixed $lastMod
     *
     * @access protected
     * @return void
     */
    protected function setProcessedHeaders(ResourceInterface $resource, $lastMod)
    {
        $this->headers->set('Content-type', $mime = $resource->getMimeType());
        $this->setLastModified($lastMod);
        $this->headers->set('Accept-ranges', 'bytes');
        $this->headers->set('Keep-Alive', 'timeout=15, max=200');
        $this->headers->set('Connection', 'Keep-Alive', true);

        if ($this->useXsend && $resource->isLocal()) {
            $this->setXsendFileHeaders($resource->getPath(), $lastMod);

            return;
        }

        $this->setContent($content = $resource->getContents());
        $this->setEtag(hash('sha1', $content));
    }

    /**
     * setXsendFileHeaders
     *
     * @param ResourceInterface $resource
     * @param mixed $lastMod
     *
     * @access protected
     * @return void
     */
    protected function setXsendFileHeaders($file, $lastMod)
    {
        $this->setEtag(sha1_file($file));

        $this->headers->set('Content-Length', filesize($file));
        $this->headers->set('Content-Disposition', sprintf('inline; filename="%s"', basename($file)));
        $this->headers->set('X-Sendfile', $file);
    }
}

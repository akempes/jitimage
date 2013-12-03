<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Response;

use Thapp\JitImage\Image;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic response handler
 *
 * @uses AbstractFileResponse
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class GenericFileResponse extends AbstractFileResponse
{

    /**
     * {@inheritdoc}
     */
    protected function setHeaders(Response $response, Image $image, \DateTime $lastMod)
    {
        $response->headers->set('Content-type', $image->getMimeType());
        $response->setContent($content = $image->getContents());

        $response->setLastModified($lastMod);

        $response->setEtag(hash('md5', $response->getContent()));

        $file = $image->getSource();

        $response->headers->set('Accept-ranges', 'bytes');
        $response->headers->set('Keep-Alive', 'timeout=15, max=200');
        $response->headers->set('Connection', 'Keep-Alive', true);
    }

    /**
     * setHeadersIfNotProcessed
     *
     * @param Response $response
     * @param Image $image
     *
     * @access protected
     * @return mixed
     */
    protected function setHeadersIfNotProcessed(Response $response, Image $image, \DateTime $lastMod)
    {
        $response->setNotModified();
        $response->setLastModified($lastMod);
    }
}

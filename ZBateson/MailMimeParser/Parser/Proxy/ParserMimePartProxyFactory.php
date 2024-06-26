<?php
/**
 * This file is part of the ZBateson\MailMimeParser project.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace ZBateson\MailMimeParser\Parser\Proxy;

use Psr\Log\LoggerInterface;
use ZBateson\MailMimeParser\Message\Factory\PartHeaderContainerFactory;
use ZBateson\MailMimeParser\Message\MimePart;
use ZBateson\MailMimeParser\Parser\IParserService;
use ZBateson\MailMimeParser\Parser\Part\ParserPartChildrenContainerFactory;
use ZBateson\MailMimeParser\Parser\Part\ParserPartStreamContainerFactory;
use ZBateson\MailMimeParser\Parser\PartBuilder;
use ZBateson\MailMimeParser\Stream\StreamFactory;

/**
 * Responsible for creating proxied IMimePart instances wrapped in a
 * ParserMimePartProxy with a MimeParser.
 *
 * @author Zaahid Bateson
 */
class ParserMimePartProxyFactory extends ParserPartProxyFactory
{
    protected LoggerInterface $logger;

    protected StreamFactory $streamFactory;

    protected ParserPartStreamContainerFactory $parserPartStreamContainerFactory;

    protected PartHeaderContainerFactory $partHeaderContainerFactory;

    protected ParserPartChildrenContainerFactory $parserPartChildrenContainerFactory;

    public function __construct(
        LoggerInterface $logger,
        StreamFactory $sdf,
        PartHeaderContainerFactory $phcf,
        ParserPartStreamContainerFactory $pscf,
        ParserPartChildrenContainerFactory $ppccf
    ) {
        $this->logger = $logger;
        $this->streamFactory = $sdf;
        $this->partHeaderContainerFactory = $phcf;
        $this->parserPartStreamContainerFactory = $pscf;
        $this->parserPartChildrenContainerFactory = $ppccf;
    }

    /**
     * Constructs a new ParserMimePartProxy wrapping an IMimePart object that
     * will dynamically parse a message's content and parts as they're
     * requested.
     */
    public function newInstance(PartBuilder $partBuilder, IParserService $parser) : ParserMimePartProxy
    {
        $parserProxy = new ParserMimePartProxy($partBuilder, $parser);

        $streamContainer = $this->parserPartStreamContainerFactory->newInstance($parserProxy);
        $headerContainer = $this->partHeaderContainerFactory->newInstance($parserProxy->getHeaderContainer());
        $childrenContainer = $this->parserPartChildrenContainerFactory->newInstance($parserProxy);

        $part = new MimePart(
            $partBuilder->getParent()->getPart(),
            $this->logger,
            $streamContainer,
            $headerContainer,
            $childrenContainer
        );
        $parserProxy->setPart($part);

        $streamContainer->setStream($this->streamFactory->newMessagePartStream($part));
        $part->attach($streamContainer);
        return $parserProxy;
    }
}

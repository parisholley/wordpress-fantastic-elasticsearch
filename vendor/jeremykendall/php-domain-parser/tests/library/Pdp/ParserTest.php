<?php

namespace Pdp;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    protected $parser;

    protected function setUp()
    {
        parent::setUp();
        $file = realpath(dirname(__DIR__) . '/../../data/public-suffix-list.php');
        $this->parser = new Parser(
            new PublicSuffixList($file)
        );
    }

    protected function tearDown()
    {
        $this->parser = null;
        parent::tearDown();
    }

    /**
     * @covers Pdp\Parser::parseUrl()
     */
    public function testParseBadUrlThrowsInvalidArgumentException()
    {
        $this->setExpectedException(
            '\InvalidArgumentException', 
            'Invalid url http:///example.com'
        );

        $this->parser->parseUrl('http:///example.com');
    }

    /**
     * @covers Pdp\Parser::parseUrl()
     * @dataProvider parseDataProvider
     */
    public function testParseUrl($url, $publicSuffix, $registerableDomain, $subdomain, $hostPart)
    {
        $pdpUrl = $this->parser->parseUrl($url);
        $this->assertInstanceOf('\Pdp\Uri\Url', $pdpUrl);
    }

    /**
     * @covers Pdp\Parser::parseUrl()
     * @covers Pdp\Parser::parseHost()
     * @dataProvider parseDataProvider
     */
    public function testParseHost($url, $publicSuffix, $registerableDomain, $subdomain, $hostPart)
    {
        $pdpUrl = $this->parser->parseUrl($url);
        $this->assertEquals($hostPart, $pdpUrl->host);

        $pdpHost = $this->parser->parseHost($hostPart);
        $this->assertInstanceOf('\Pdp\Uri\Url\Host', $pdpHost);
        $this->assertEquals($hostPart, $pdpHost->__toString());
    }

    /**
     * @covers Pdp\Parser::parseUrl()
     * @covers Pdp\Parser::getPublicSuffix()
     * @dataProvider parseDataProvider
     */
    public function testGetPublicSuffix($url, $publicSuffix, $registerableDomain, $subdomain, $hostPart)
    {
        $pdpUrl = $this->parser->parseUrl($url);
        $this->assertSame($publicSuffix, $pdpUrl->host->publicSuffix);
        $this->assertSame($publicSuffix, $this->parser->getPublicSuffix($hostPart));
    }

    /**
     * @covers Pdp\Parser::parseUrl()
     * @covers Pdp\Parser::getRegisterableDomain()
     * @dataProvider parseDataProvider
     */
    public function testGetRegisterableDomain($url, $publicSuffix, $registerableDomain, $subdomain, $hostPart)
    {
        $pdpUrl = $this->parser->parseUrl($url);
        $this->assertSame($registerableDomain, $pdpUrl->host->registerableDomain);
        $this->assertSame($registerableDomain, $this->parser->getRegisterableDomain($hostPart));
    }

    /**
     * @covers Pdp\Parser::parseUrl()
     * @covers Pdp\Parser::getSubdomain()
     * @dataProvider parseDataProvider
     */
    public function testGetSubdomain($url, $publicSuffix, $registerableDomain, $subdomain, $hostPart)
    {
        $pdpUrl = $this->parser->parseUrl($url);
        $this->assertSame($subdomain, $pdpUrl->host->subdomain);
        $this->assertSame($subdomain, $this->parser->getSubdomain($hostPart));
    }
    
	/**
     * @dataProvider parseDataProvider
	 */
	public function testPHPparse_urlCanReturnCorrectHost($url, $publicSuffix, $registerableDomain, $subdomain, $hostPart)
	{
		$this->assertEquals($hostPart, parse_url('http://' . $hostPart, PHP_URL_HOST));
	}

    public function parseDataProvider()
    {
        // url, public suffix, registerable domain, subdomain, host part
        return array(
            array('http://www.waxaudio.com.au/audio/albums/the_mashening', 'com.au', 'waxaudio.com.au', 'www', 'www.waxaudio.com.au'),
            array('example.com', 'com', 'example.com', null, 'example.com'),
            array('giant.yyyy', 'yyyy', 'giant.yyyy', null, 'giant.yyyy'),
            array('cea-law.co.il', 'co.il', 'cea-law.co.il', null, 'cea-law.co.il'),
            array('http://edition.cnn.com/WORLD/', 'com', 'cnn.com', 'edition', 'edition.cnn.com'),
            array('http://en.wikipedia.org/', 'org', 'wikipedia.org', 'en', 'en.wikipedia.org'),
            array('a.b.c.cy', 'c.cy', 'b.c.cy', 'a', 'a.b.c.cy'),
            array('https://test.k12.ak.us', 'k12.ak.us', 'test.k12.ak.us', null, 'test.k12.ak.us'),
            array('www.scottwills.co.uk', 'co.uk', 'scottwills.co.uk', 'www', 'www.scottwills.co.uk'),
            array('b.ide.kyoto.jp', 'ide.kyoto.jp', 'b.ide.kyoto.jp', null, 'b.ide.kyoto.jp'),
            array('a.b.example.uk.com', 'uk.com', 'example.uk.com', 'a.b', 'a.b.example.uk.com'),
            array('test.nic.ar', 'ar', 'nic.ar', 'test', 'test.nic.ar'),
            array('a.b.test.ck', 'test.ck', 'b.test.ck', 'a', 'a.b.test.ck'),
            array('baez.songfest.om', 'om', 'songfest.om', 'baez', 'baez.songfest.om'),
            array('politics.news.omanpost.om', 'om', 'omanpost.om', 'politics.news', 'politics.news.omanpost.om'),
            // BEGIN https://github.com/jeremykendall/php-domain-parser/issues/16
            array('us.example.com', 'com', 'example.com', 'us', 'us.example.com'),
            array('us.example.na', 'na', 'example.na', 'us', 'us.example.na'),
            array('www.example.us.na', 'us.na', 'example.us.na', 'www', 'www.example.us.na'),
            array('us.example.org', 'org', 'example.org', 'us', 'us.example.org'),
            array('webhop.broken.biz', 'biz', 'broken.biz', 'webhop', 'webhop.broken.biz'),
            array('www.broken.webhop.biz', 'webhop.biz', 'broken.webhop.biz', 'www', 'www.broken.webhop.biz'),
            // END https://github.com/jeremykendall/php-domain-parser/issues/16
            // Test schemeless url
            array('//www.broken.webhop.biz', 'webhop.biz', 'broken.webhop.biz', 'www', 'www.broken.webhop.biz'),
            // Test ftp support - https://github.com/jeremykendall/php-domain-parser/issues/18
            array('ftp://www.waxaudio.com.au/audio/albums/the_mashening', 'com.au', 'waxaudio.com.au', 'www', 'www.waxaudio.com.au'),
            array('ftps://test.k12.ak.us', 'k12.ak.us', 'test.k12.ak.us', null, 'test.k12.ak.us'),
        );
    }
}

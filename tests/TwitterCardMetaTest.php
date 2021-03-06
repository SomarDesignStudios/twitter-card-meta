<?php

/**
 * Class TwitterCardMetaTest
 *
 * @mixin PHPUnit_Framework_Assert
 */
class TwitterCardMetaTest extends SapphireTest
{
    protected static $fixture_file = 'fixtures/TwitterCardMetaTest.yml';

    public function setUp()
    {
        /** =========================================
         * @var SiteConfig $siteConfig
         * ========================================*/

        parent::setUp();

        $defaultTwitterImage = $this->objFromFixture('File', 'default');

        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->setField('DefaultTwitterHandle', 'silverstripe');
        $siteConfig->setField('DefaultTwitterImageID', $defaultTwitterImage->ID);
        $siteConfig->write();
    }

    /**
     * @covers TwitterCardMeta::getCreatorHandle()
     */
    public function testGetCreatorHandle()
    {
        /** =========================================
         * @var TwitterCardMeta|SiteTree $aboutPage
         * ========================================*/

        $aboutPage = $this->objFromFixture('SiteTree', 'about');
        $handle = $aboutPage->getCreatorHandle();
        $this->assertEquals('toastnz', $handle);

        $aboutPage = $this->objFromFixture('SiteTree', 'home');
        $handle = $aboutPage->getCreatorHandle();
        $this->assertEquals('silverstripe', $handle);
    }

    /**
     * @covers TwitterCardMeta::getFirstImage()
     */
    public function testGetFirstImage()
    {
        /** =========================================
         * @var TwitterCardMeta|SiteTree $aboutPage
         * @var TwitterCardMeta|SiteTree $homePage
         * ========================================*/

        $aboutPage = $this->objFromFixture('SiteTree', 'about');
        $firstImage = $aboutPage->getFirstImage();
        $this->assertEquals('assets/Uploads/6868265.gif', $firstImage);

        $homePage = $this->objFromFixture('SiteTree', 'home');
        $firstImage = $homePage->getFirstImage();
        $this->assertEquals('', $firstImage, 'Found a result when there should be none');
    }

    /**
     * @covers TwitterCardMeta::onBeforeWrite()
     */
    public function testOnBeforeWrite()
    {
        /** =========================================
         * @var TwitterCardMeta|SiteTree $homePage
         * @var TwitterCardMeta|SiteTree $aboutPage
         * ========================================*/

        /** -----------------------------------------
         * Homepage
         * ----------------------------------------*/

        $homePage = $this->objFromFixture('SiteTree', 'home');
        $homePage->write();

        $defaultTwitterImage = $this->objFromFixture('File', 'default');

        $this->assertEquals(
            'Cras luctus. Convallis etiam proin urna, consequat nibh vulputate luctus laoreet venenatis vestibulum malesuada vehicula.',
            $homePage->TwitterDescription
        );

        $this->assertEquals('Home', $homePage->TwitterTitle);
        $this->assertEquals('silverstripe', $homePage->TwitterCreator);
        $this->assertEquals('silverstripe', $homePage->TwitterSite);
        $this->assertEquals($defaultTwitterImage->ID, $homePage->TwitterImageID);

        /** -----------------------------------------
         * About page
         * ----------------------------------------*/

        $aboutPage = $this->objFromFixture('SiteTree', 'about');
        $aboutPage->write();

        $defaultTwitterImage = $this->objFromFixture('File', 'alt');

        $this->assertEquals(
            'Welcome to SilverStripe!',
            $aboutPage->TwitterDescription
        );

        $this->assertEquals('About', $aboutPage->TwitterTitle);
        $this->assertEquals('toastnz', $aboutPage->TwitterCreator);
        $this->assertEquals('silverstripe', $aboutPage->TwitterSite);
        $this->assertEquals($defaultTwitterImage->ID, $aboutPage->TwitterImageID);
    }

    /**
     * @covers TwitterCardMeta::getTwitterImageURL()
     */
    public function testGetTwitterImageURL()
    {
        /** =========================================
         * @var SiteConfig $siteConfig
         * @var Image $defaultTwitterImage
         * @var TwitterCardMeta|SiteTree $homePage
         * @var TwitterCardMeta|SiteTree $aboutPage
         * ========================================*/

        $siteConfig = SiteConfig::current_site_config();

        $defaultTwitterImage = $this->objFromFixture('File', 'default');

        $siteConfig->setField('DefaultTwitterHandle', '');
        $siteConfig->setField('DefaultTwitterImageID', 0);
        $siteConfig->write();

        $expectedImageURL = $defaultTwitterImage->CroppedImage(560, 750)->AbsoluteURL;

        /** -----------------------------------------
         * Test without default
         * ----------------------------------------*/

        $homePage = $this->objFromFixture('SiteTree', 'home');
        $imageURL = $homePage->getTwitterImageURL();
        $this->assertEquals('', $imageURL);

        $aboutPage = $this->objFromFixture('SiteTree', 'about');
        $imageURL = $aboutPage->getTwitterImageURL();
        $this->assertEquals(Controller::join_links(Director::absoluteBaseURL(), 'assets/Uploads/6868265.gif'), $imageURL);

        $aboutPage->setField('TwitterImageID', $defaultTwitterImage->ID);
        $aboutPage->write();
        $imageURL = $aboutPage->getTwitterImageURL();
        $this->assertEquals($expectedImageURL, $imageURL);

        /** -----------------------------------------
         * Test with default
         * ----------------------------------------*/

        $siteConfig->setField('DefaultTwitterImageID', $defaultTwitterImage->ID);
        $siteConfig->write();

        $imageURL = $homePage->getTwitterImageURL();
        $this->assertEquals($siteConfig->DefaultTwitterImage()->CroppedImage(560, 750)->AbsoluteURL, $imageURL);
    }
}

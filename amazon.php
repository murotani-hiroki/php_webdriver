<?php
require_once './vendor/autoload.php';

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

function getAmazonInfo(?string $janCode) {

    if (!$janCode) {
        return ['asin' => '', 'price' => '' ];
    }

    $driverPath = realpath(__DIR__ . "/chromedriver");
    putenv("webdriver.chrome.driver=" . $driverPath);

    $options = new ChromeOptions();
    $options->addArguments(['--headless', '--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']);
    //$options->addArguments(['--no-sandbox']);
    //$options->addArguments(['--disable-gpu']);
    //$options->addArguments(['--disable-dev-shm-usage']);
    //$options->addArguments(["window-size=1024,2048"]);

    //$host = 'http://localhost:4444/wd/hub';
    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
    //$driver = Facebook\WebDriver\Remote\RemoteWebDriver::create($host, $capabilities);
    @$driver = ChromeDriver::start($capabilities);

    $driver->get('https://www.amazon.co.jp/');
    $element = $driver->findElement(WebDriverBy::name('field-keywords'));
    $element->sendKeys($janCode);
    $element->submit();

    /*
    $driver->wait()->until(
        WebDriverExpectedCondition::titleContains('Amazon')
    );
    */

    //$items = $driver->findElements(WebDriverBy::cssSelector('div.s-result-item div.sg-col-inner h2 a'));
    $items = $driver->findElements(WebDriverBy::cssSelector('div.s-asin'));
    if (!$items) {
        $driver->close();
        return ['asin' => '', 'price' => '' ];
    }
    $asin = $items[0]->getAttribute('data-asin');
    
    $price = '';
    $items = $driver->findElements(WebDriverBy::cssSelector('div.s-result-item div.sg-col-inner .a-price-whole'));
    if ($items) {
        $price = $items[0]->getText();
        $price = str_replace('¥', '', $price);
        $price = str_replace('￥', '', $price);
        $price = str_replace(',', '', $price);
    }

    $driver->close();
    
    error_log('amazon asin=' . $asin . "\n", 3, './debug.log');

    return ['asin' => $asin, 'price' => $price ];
}

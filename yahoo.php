<?php
require_once './vendor/autoload.php';

use Doctrine\DBAL\Driver\DrizzlePDOMySql\Driver;
use Facebook\WebDriver\Chrome\ChromeOptions;

use Facebook\WebDriver\Chrome\ChromeDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriver;

function getYahooInfo(string $url) {

    $driverPath = realpath(__DIR__ . "/chromedriver");
    putenv("webdriver.chrome.driver=" . $driverPath);

    $options = new ChromeOptions();
    //$options->addArguments(['--headless']);
    //$options->addArguments(["window-size=1024,2048"]);

    //$host = 'http://localhost:4444/wd/hub';
    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
    //$driver = Facebook\WebDriver\Remote\RemoteWebDriver::create($host, $capabilities);
    @$driver = ChromeDriver::start($capabilities);

    $driver->get($url);

    $shopName = $driver->findElements(WebDriverBy::cssSelector('div.mdBreadCrumb li:first-child span'))[0]->getText();

    //$items = $driver->findElements(WebDriverBy::cssSelector('div.s-result-item div.sg-col-inner h2 a'));
    $items = $driver->findElements(WebDriverBy::cssSelector('div#itmlst>ul>li'));
    $yahooInfos = [];
    foreach ($items as $item) {
        $url = $item->findElements(WebDriverBy::cssSelector('div.elName>a'))[0]->getAttribute('href');
        $price = $item->findElements(WebDriverBy::cssSelector('span.elPriceValue'))[0]->getText();
        $price = str_replace(',', '', $price);
        $price = str_replace('円', '', $price);
        $yahooInfos[] = ['shop' => $shopName, 'url' => $url, 'price' => $price ];
            
        //echo $detailUrl . '<br/>';
        //$detail = $item->findElements(WebDriverBy::cssSelector('div.elName>a'))[0]->click();
    }


    foreach($yahooInfos as &$yahooInfo) {
        try {
            //echo $yahooInfo['url'] . '<br/>';
            $driver->get($yahooInfo['url']);
        } catch(WebDriverException $e) {
            // skip
            //echo $e->getMessage() . '<br/>';
        }
        $detail = $driver->findElements(WebDriverBy::cssSelector('div#itm_cat'))[0];
        $elRows = $detail->findElements(WebDriverBy::cssSelector('li.elRow'));

        foreach ($elRows as $elRow) {
            $rowTitle = $elRow->findElements(WebDriverBy::cssSelector('div.elRowTitle > p'))[0]->getText();
            if (str_starts_with($rowTitle, 'JAN')) {
                $yahooInfo['janCode'] = $elRow->findElements(WebDriverBy::cssSelector('div.elRowData > p'))[0]->getText();
            }
        }
    }

    $driver->close();

    return $yahooInfos;
}

//getYahooInfo('https://store.shopping.yahoo.co.jp/yardforce-official/c0bdc9caa4.html#sideNaviItems');
//https://store.shopping.yahoo.co.jp/bestexcel/search.html
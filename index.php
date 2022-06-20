<?php if (isset($_POST['url'])) { ?>
<?php
require_once './yahoo.php';
require_once './amazon.php';

//$yahooInfo = getYahooInfo('https://store.shopping.yahoo.co.jp/yardforce-official/c0bdc9caa4.html#sideNaviItems');
//$yahooInfos = getYahooInfo('https://store.shopping.yahoo.co.jp/sake-premoa/search.html?p=%28%E3%83%86%E3%83%AC%E3%83%93+%E3%83%96%E3%83%AB%E3%83%BC%E3%83%AC%E3%82%A4%29&strcid=b1c7c1fcb4&used=#CentSrchFilter1');
$yahooInfos = getYahooInfo($_POST['url']);
//$janCodes = array_map(fn($i) => $i['janCode'], $yahooInfo);

$results = [];
foreach ($yahooInfos as &$yahooInfo) {
    if (@!$yahooInfo['janCode']) {
        continue;
    }
    $amazonInfo = getAmazonInfo($yahooInfo['janCode']);
    $results[] = join(',', [$yahooInfo['janCode'], $yahooInfo['price'], $amazonInfo['asin'], $amazonInfo['price']]);
    //$yahooInfo['asin'] = $amazonInfo['asin'];
    //$yahooInfo['amazonPrice'] = $amazonInfo['amazonPrice'];
}
$csv = join("\n", $results);

$fileName = $yahooInfos[0]['shop'] . '.csv';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $fileName);
echo $csv;
//var_dump($csv);
?>
<?php } else { ?>
<html>
<head>
<style>
.flex {
    display: flex;
}
.flex div {
    margin: 5px;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script type="text/javascript">
$(function() {
    
    function download(url) {
        $.ajax({
            url: 'http://localhost:8080/',
            type: 'post',
            data: { url: url },
        }).done (function(data) {
            const blob = new Blob([data]);
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.setAttribute('download', url + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            /*
            $('body').append('<a id="downloadLink"></a>');
            $('#downloadLink').attr('href', window.URL.createObjectURL(blob));
            $('#downloadLink').attr('download', url + '.csv');
            $('#downloadLink').click();
            $('#downloadLink').remove();
            */
        });
    }

    $('.addBtn').on('click', function() {
        var $input = $('.input:last');
        $added = $input.clone(true).insertAfter($input);
        $added.find('.url').val('');
    });

    $('.deleteBtn').on('click', function() {
        $(this).parents('.input').remove();
    });

    $('#downloadBtn').on('click', function() {
        $('.url').each(function(){
            download($(this).val());
        });
    });

})
</script>
</head>
<body>
<h1>商品リスト作成</h1>
<div id="main">
    <div class="flex input">
        <div>URL</div>
        <div><input type="text" name="url" size="100" class="url"></div>
        <div><input type="button" value="ー" class="deleteBtn"></div>
    </div>
</div>

<div class="flex">
    <div><input type="button" value="ダウンロード" id="downloadBtn"></div>
    <div><input type="button" value="＋" class="addBtn"></div>
</div>
</body>
</html>
<?php } ?>
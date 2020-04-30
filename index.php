<?php
include('crawler.php');
$url="";
if($_POST["url"]!="")
{
	$imageCount=0;$internalCount=0;$externalCount=0;$pageLoad=0;$wordCount=0;$titleLength=0;
	//$url = 'https://agencyanalytics.com';
	$url = parse_url($_POST["url"], PHP_URL_SCHEME) === null ? 'http://' . $_POST["url"] : $_POST["url"];
	$pages = 5;
	$crawler = new Crawler($url,$pages); // create an object of Crawler class
	$crawler->run(); // execute the code
	$visited = $crawler->getResult(); // fetch the result into an array
	$pageCrawled = count($visited);
	foreach($visited as $visit)
	{
		//var_dump($visit['title_length']);
		$url = $visit['url'];
		$imageCount += $visit['images'];
		$internalCount += $visit['internal'];
		$externalCount += $visit['external'];
		$wordCount += $visit['words'];
		$titleLength += $visit['title_length'];			
		$pageLoad +=$visit['load'];
	}
}
?>  
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Website Crawler</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">      	
            <div class="page-header">
			    <h1>Website Crawler</h1></p>
			    <form method="POST" action="" class="form-inline">
					<div class="form-group mx-sm-3 mb-2">
						<label for="url" class="sr-only">URL</label>
						<input type="text" class="form-control" id="url" name="url" value="<?=$url?>" placeholder="URL">
					</div>
					<button type="submit" class="btn btn-primary mb-2">Crawl</button>
			    </form>
			</div>

			<ul>
				<li>Number of pages crawled: <b><?= $pageCrawled ?></b></li>
				<li>Number of a unique images: <b><?= $imageCount ?></b></li>
				<li>Number of unique internal links: <b><?= $internalCount ?></b></li>
				<li>Number of unique external links: <b><?= $externalCount ?></b></li>
				<li>Avg page load: <b><?= round($pageLoad/$pageCrawled,2) ?></b> second</li>
				<li>Avg word count: <b><?= ($wordCount/$pageCrawled) ?></b></li>
				<li>Avg Title length: <b><?= round($titleLength/$pageCrawled,2) ?></b></li>
			</ul>
			<table class="table">
				<tr>
					<th>Page</th>
					<th>Status Code</th>
				</tr>
			<?php 
			if($_POST["url"]!="")
			{
			foreach($visited as $visit){ 
			?>	
				<tr>
					<td><?=$visit['url']?></td>
					<td><?=$visit['code']?></td>
				</tr>
			<?php 
			}
			} 
			?>	
			</table>
        </div>
        <!-- jQuery first, then Popper.js, and then Bootstrap's JavaScript -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    </body>
</html>

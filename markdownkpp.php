#!/usr/bin/php
<?php

class Markdownkpp {

	const delimitRe = '/[^\n]\K    +/'; /* 4 or more spaces that aren't indentation */

	static public function process ($dom) {
		$uls = $dom->getElementsByTagName('ul');
		foreach ($uls as $ul) {
			if (self::ulHasTabs($ul)) {
				$table = self::ulToTable($ul);
				self::colspanPad($table);
				$ul->parentNode->replaceChild($table, $ul);
			}
		}
	}
	
	private static function ulHasTabs ($ul) {
		foreach ($ul->childNodes as $li) {
			if ($li->nodeName === 'li') {
				foreach ($li->childNodes as $n) {
					if ($n->nodeType === XML_TEXT_NODE) {
						//if (strpos($n->textContent, "    ") !== false)
						if (preg_match(self::delimitRe, $n->textContent) > 0)
							return true;
					}
				}
			}
		}
		return false;
	}

	private static function ulHasThead ($ul) {
		return false;
		/* TODO: implement. if > li:2nd-child has exactly one <hr> Child */
	}
	
	private static function trAddTd ($tr) {
		$td = $tr->ownerDocument->createElement('td');
		$tr->appendChild($td);
		return $td;
	}
	
	private static function ulToTable ($ul) {
		$colCount = self::ulHasTabs($ul, true);
		$doc = $ul->ownerDocument;
		$table = $doc->createElement('table');
		$tbody = $doc->createElement('tbody');
		$table->appendChild($tbody);
		foreach ($ul->childNodes as $li) {
			if ($li->nodeName === 'li') {
				$tr = self::liToTr($li, $colCount);
				$tbody->appendChild($tr);
			}
		}
		return $table;
	}
	private static function liToTr ($li) {
		$doc = $li->ownerDocument;
		$tr = $doc->createElement('tr');
		$td = self::trAddTd($tr);
		$cols = 1;
		foreach ($li->childNodes as $n) {
			if ($n->nodeType === XML_TEXT_NODE) {
				$split = preg_split(self::delimitRe, $n->textContent);
				foreach ($split as $no => $_) {
					if ($no > 0) {
						$td = self::trAddTd($tr);
						$cols++;
					}
					$text = $doc->createTextNode($_);
					$td->appendChild($text);
				}
			}
			else {
				$td->appendChild($n->cloneNode(true));
			}
		}
		return $tr;
	}

	private static function colspanPad ($table) {
		$maxCols = 0;
		foreach ($table->firstChild->childNodes as $tr) { /* firstChild is tbody */
			$maxCols = max($maxCols, $tr->childNodes->length);
		}
		foreach ($table->firstChild->childNodes as $tr) { /* firstChild is tbody */
			$colspan = $maxCols - $tr->childNodes->length + 1;
			if ($colspan > 1)
				$tr->lastChild->setAttribute('colspan', $colspan);
		}
	}
}

/*
$xml = <<<XML
	<html>
		<body>
			<h1>heading</h1>
			<p>paragraph <br />
				break
			</p>
			<ul>
				<li>listb	listsdf</li>
				<li>lostb	lost</li>
			</ul>
		</body>
	</html>
XML;

$dom = new DOMDocument;
$dom->loadXML($xml);

Markdownkpp::process($dom);

echo $dom->saveXML();
*/

if (defined('STDIN')) {
	$xml = "<html>\n"; /* markdown usually comes non-wrapped */
	while ($line = fgets(STDIN)) {
		$xml .= $line;
	}
	$xml .= "\n</html>";
	$dom = new DOMDocument;
	if (false === $dom->loadXML($xml)) {
		exit(1);
	}
	Markdownkpp::process($dom);
	$processed = $dom->saveXML($dom->firstChild); /* no xml declaration */
	$processed = preg_replace('/^<html>\s?/', '', $processed, 1); /* unwrap */
	$processed = preg_replace('/\s?<\/html>$/', '', $processed, 1);
	echo $processed;
	exit(0);
}

?>

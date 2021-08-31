<?php

// Generate Quickstatements

$timestamp_filename = 'timestamp.txt';
if (!file_exists($timestamp_filename))
{
	file_put_contents($timestamp_filename, time());
}
$timestamp = file_get_contents($timestamp_filename);

// Headings for file
$headings = explode("\t", file_get_contents('header.tsv'));

// TSV file with mapping, no headings
$filename = 'wikidata.tsv';

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		"\t" 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		$obj = new stdclass;
		
		foreach ($row as $k => $v)
		{
			if ($v != '')
			{
				$obj->{$headings[$k]} = $v;
			}
		}
	
		// print_r($obj);	
		
		// Quickstaments
					
		// skip over thing we have already processed
		$skip = false;
		
		if (isset($obj->modified) && ($obj->modified < $timestamp))
		{
			$skip = true;
		}
		
		if (isset($obj->ignore))
		{
			$skip = true;
		}
				
		// sanity check
		if (!$skip && isset($obj->taxonID) && isset($obj->scientificName) && isset($obj->namePublishedInID))
		{
			$w = array();
			
			$statement = array();
			
			// add reference (we are asuming that Wikidata has the same taxon name string as our source database)
			
			$statement[] = $obj->taxonID;
			$statement[] = 'P225';
			$statement[] = '"' . $obj->scientificName . '"';
			$statement[] = 'S248';
			$statement[] = $obj->namePublishedInID;
			
			if (isset($obj->referenceType))
			{
				switch ($obj->referenceType)
				{
					case 'original':
						$statement[] = 'S6184';
						$statement[] = 'Q1361864';
						break;

					case 'combination':
						$statement[] = 'S6184';
						$statement[] = 'Q14594740';
						break;
									
					default:
						break;
				}
			
			}

			$qs = join("\t", $statement);
			
			echo $qs . "\n";
		
		}
		
		
		
	}
	$row_count++;
}


// we are done
//file_put_contents($timestamp_filename, time());

?>



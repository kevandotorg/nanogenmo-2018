<?php

include "esperanto.php";
include "esperanto2.php";
include "spanish.php";
include "spanish2.php";
include "spanish3.php";
include "italian.php";
include "italian2.php";
include "french3.php";
include "french2.php";
include "german1.php";
include "german2.php";
include "dutch.php";
include "irish.php";
include "danish.php";
include "swedish.php";
include "norwegian.php";
include "polish.php";

include "adverbs.php";

$previousLine = "";
$speaker = "";
$previousSpeaker = "";
$nextSpeaker = "";
$focus = "Britain";
$recentText = "";
$actCount = 1;
$sceneCount = 1;
$lineCount = 1;
$wordCount = 0;
$wordTarget = 50000;

$fullCast = array("es","es2","es3","fr3","fr2","it","it2","de1","de2","nl","ie","dk","se","no","pl");
$castOnStage = array();
$castHaveSpoken = array();
$castHaveAppeared = array();

//castWalkOn();
//castWalkOn();

$initialCast = $fullCast;
shuffle($initialCast);
array_splice($initialCast, 3);	
$castOnStage = $initialCast;

$stageSetting = "";

print frontispiece();

while ($wordCount<$wordTarget)
{
	$sceneIsOver = 0;
	$sceneScript = "";
	$mainCastPositions = "";
	$scenePlace = "";
	
	while ($sceneIsOver == 0)
	{
		$newSpeaker = $castOnStage[array_rand($castOnStage)];
		if (sizeof($castOnStage)>1)
		{
			if (rand(1,2)==1) { $newSpeaker = $previousSpeaker; } // 50% chance of returning to previous speaker, if they're still on stage
			while ($newSpeaker == $speaker || $newSpeaker == "" || !in_array($newSpeaker, $castOnStage))
			{ $newSpeaker = $castOnStage[array_rand($castOnStage)]; }
			if ($nextSpeaker != "") { $newSpeaker = $nextSpeaker; $nextSpeaker = ""; }
		}

		$previousSpeaker = $speaker;
		$speaker = $newSpeaker;

		if (!in_array($speaker, $castHaveSpoken))
		{ array_push($castHaveSpoken,$speaker); }

		// If there are question(s), ignore every sentence except the last question when deciding how to respond
		if (preg_match("/(.+[.?!] )*([^?]+\?)/",$previousLine,$questionMatch))
		{
			$previousLine = $questionMatch[2];
		}
			
		// better for "my x" to be met with "your x", and vice versa
		$previousLine = preg_replace("/\bmy\b/i","yo@ur",$previousLine);
		$previousLine = preg_replace("/\byour\b/i","my",$previousLine);
		$previousLine = preg_replace("/\byo@ur\b/i","your",$previousLine);
		$previousLine = preg_replace("/\b(me|I)\b/i","yo@u",$previousLine);
		$previousLine = preg_replace("/\byou\b/i","I",$previousLine);
		$previousLine = preg_replace("/\byo@u\b/i","you",$previousLine);
		
		$firstLine = cleanLine(fetchLine($previousLine,$focus,$speaker,$actCount));
		$recentText .= $firstLine;

		$secondLine = "";
		$thirdLine = "";
		
		if (rand(1,5) == 1 || (strlen($firstLine)<30 && rand(1,3) == 1))
		{
			$secondLine = " ".cleanLine(fetchLine($firstLine,$focus,$speaker,$actCount));
			$recentText .= $secondLine;
		}
		if (rand(1,5) == 1)
		{
			$thirdLine = "";
			if (rand(1,5)==1) { $thirdLine .= " ".direction("pause"); }
			elseif (rand(1,2)==1)
			{
				$turnTo = $castOnStage[array_rand($castOnStage)];
				if ($turnTo != $speaker && $turnTo != $previousSpeaker && sizeof($castOnStage)>2)
				{
					$thirdLine .= " ".direction(randomWord("to","turning to","to","turning to","whispering to")." ".actorName($turnTo));
					$nextSpeaker = $turnTo;
				}
			}
			elseif (rand(1,10)==1) { $thirdLine .= " ".direction(randomAdverb()); }
			elseif (rand(1,10)==1) { $thirdLine .= " ".direction(actorPronounE($speaker)." PERFORMS SCENE EFFECT"); }

			$thirdLine .= " ".cleanLine(fetchLine($secondLine,$focus,$speaker,$actCount));
			$recentText .= $thirdLine;
		}
		
		$line = $firstLine.$secondLine.$thirdLine;
		$lineCount++;
		
		// deliver the line
		$sceneScript .= "<p><span class=\"character\">".actorName($speaker).":</span> ";
		if (rand(1,10)==10) { $sceneScript .= direction(randomAdverb())." "; }
		elseif (rand(1,10)==10) { $sceneScript .= direction(actorPronounE($speaker)." PERFORMS SCENE EFFECT")." "; }
		$sceneScript .= "$line</p>\n";

		if (stripos($mainCastPositions,actorName($speaker)) == false && in_array($speaker, $initialCast)) // position on stage if they were there at the start of it, and not already done
		{
			$possibleLocation = "";
						
			if (preg_match("/\b(cough|sneeze|limp|sleep|wash|bath)(ing|es|s|y|ed|d)?\b/i",$line,$matches))
			{
				$verb = preg_replace("/e$/","",strtolower($matches[1]))."ing";
				if ($verb == "washing" || $verb == "bathing") { $possibleLocation = "bathroom"; }
				if ($verb == "sleeping") { $possibleLocation = "bedroom"; $verb = "awakening from sleep"; }
				$mainCastPositions .= " ".actorName($speaker)." is ".$verb.".";
			}
			elseif (preg_match("/\b(swim)(ming|er|s)?\b/i",$line,$matches))
			{
				$mainCastPositions .= " ".actorName($speaker)." emerges from the water.";
			}
			elseif (preg_match("/\b(knife|fork|spoon|plate|bowl|glass|bottle|pot|pan|dish|saucer|hammer)e?s?\b/i",$line,$matches) && rand(1,2)==1) // half odds as these crop up a lot
			{
				$verb = randomWord("holding","carrying","cleaning","examining","wiping");
				if (rand(1,3)==1 && strtolower($matches[1]) == "knife") { $verb = "sharpening"; $possibleLocation = "kitchen"; }
				if (rand(1,2)==1 && $verb == "cleaning") { $possibleLocation = "kitchen"; }
				if (strtolower($matches[1]) == "hammer") { $possibleLocation = ""; }
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1]).".";
			}
			elseif (preg_match("/\b(gun|pistol|rifle)\b/i",$line,$matches))
			{
				$verb = randomWord("holding","examining","cleaning","reloading");
				$mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1]).".";
			}
			elseif (preg_match("/\b(sword|dagger|needle|saber|blade)\b/i",$line,$matches))
			{
				$verb = randomWord("holding","sheathing","polishing","cleaning","wiping clean");
				$mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1]).".";
			}
			elseif (preg_match("/\b(spade|digging|dig)\b/i",$line,$matches))
			{
				$verb = randomWord("digging");
				$possibleLocation = randomWord("forest","path","field","public park","garden");
				$mainCastPositions .= " ".actorName($speaker)." is $verb a hole.";
			}
			elseif (preg_match("/\b(candle|torch|lantern|lamp|match)e?s?\b/i",$line,$matches))
			{
				$verb = randomWord("holding","lighting","shielding");
				$mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1]).".";
			}
			elseif (preg_match("/\b(umbrella|overcoat|raincoat|cloak)s?\b/i",$line,$matches))
			{
				$verb = randomWord("holding","folding","shaking","drying");
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			elseif (preg_match("/\b(ladder)s?\b/i",$line,$matches))
			{
				$verb = randomWord("carrying","climbing down from","positioning");
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			elseif (preg_match("/\b(apple|pear|potato|orange|egg|banana|peach|plum|cherry|grape|carrot|artichoke|onion|cucumber|vegetable)e?s?\b/i",$line,$matches))
			{
				$verb = randomWord("holding","examining","eating","peeling","slicing");
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			elseif (preg_match("/\b(cake|pudding|muffin)s?\b/i",$line,$matches))
			{
				$verb = randomWord("eating","making","holding");
				
				$possibleLocation = randomWord("restaurant","cafe");
				if ($verb == "making") { $possibleLocation = "kitchen"; }
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			elseif (preg_match("/\b(cigar|cigarette|pipe)s?\b/i",$line,$matches))
			{
				$verb = randomWord("smoking","lighting");
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			elseif (preg_match("/\b(wall)s?\b/i",$line,$matches))
			{
				$verb = randomWord("leaning on","sitting on","climbing down from");
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			elseif (preg_match("/\b(card|piquet)s?\b/i",$line,$matches))
			{
				$verb = randomWord("shuffling","holding","sorting through");
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb a deck of cards.";
			}
			elseif (preg_match("/\b(chess|chessboard|checkmate)\b/i",$line,$matches) && !preg_match("/chess/",$mainCastPositions)) // just one chessboard
			{
				$verb = randomWord("setting up","staring at","unfolding");
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb a chessboard.";
			}
			elseif (preg_match("/\b(sausage|steak|pork|beef|cheese|salad|oyster|turkey|cauliflower|cabbage|lettuce|spinach|bacon|venison|ham)s?\b/i",$line,$matches))
			{
				$food = strtolower($matches[1]);
				if (preg_match("/\b(pork|beef|cheese|venison|ham)s?\b/",$food)) { $food = "piece of $food"; }
				
				$verb = randomWord("eating","cooking","examining","slicing","frying");
				if (rand(1,2)==1 || $verb == "slicing" || $verb == "cooking" || $verb == "frying") { $possibleLocation = "kitchen"; }

				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn($food).".";
			}
			elseif (preg_match("/\b(loaf|bread)s?\b/i",$line,$matches))
			{
				$verb = randomWord("holding","slicing","breaking","examining","inspecting");
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb a loaf of bread.";
			}
			elseif (preg_match("/\b(coffee|tea|milk|wine|claret|beer|juice|soup|broth|grog)\b/i",$line,$matches))
			{
				$verb = randomWord("drinking","sipping");
				
				$container = "glass";
				if (preg_match("/\b(coffee|tea)\b/i",$line)) { $container = randomWord("cup","mug","flask");  $verb = randomWord("drinking","sipping","stirring a $container of","holding a $container of","looking into a $container of","adding sugar to a $container of"); }
				if (preg_match("/\b(milk|beer|wine|claret|juice)\b/i",$line)) { $verb = randomWord("drinking","sipping","holding a glass of"); }
				if (preg_match("/\b(soup|broth)\b/i",$line)) { $verb = randomWord("eating","sipping","holding"); $container = randomWord("bowl","pot","flask","mug"); }
				
				if (rand(1,3)==1) { $verb = "pouring a ".$container." of"; }
				
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".strtolower($matches[1]).".";
				if (rand(1,2)==1) { $possibleLocation = randomWord("restaurant","cafe"); } else { $possibleLocation = "kitchen"; }
			}
			else if (preg_match("/\b(newspaper|note.?book|exercise book|copy.book|pocket.book|large book|German book|Latin book|old book|new book|book|letter|novel)s?\b/i",$line,$matches) && rand(1,2)==1) // half odds as books come up a lot
			{
				$verb = randomWord("reading","holding","leafing through","examining");
				if (rand(1,3)==1 && strtolower($matches[1]) == "letter") { $verb = "writing"; }
				if (rand(1,3)==1 && strtolower($matches[1]) == "notebook" && strtolower($matches[1]) == "note book") { $verb = "writing in"; }

				//$item = strtolower($matches[1]);
				//if ($item == "book") { $item = randomWord("large","small","red","blue","tattered","old","torn")." ".randomWord("book","paperback","hardback","novel","textbook","notebook"); }

				$mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1])).".";
			}
			else if (preg_match("/\b(coat|jacket|hat|glove|sock|shirt|scarf|gown|dress|skirt|wig|apron|waistcoat)s?\b/i",$line,$matches))
			{
				$verb = randomWord("holding","wearing","darning");

				if (rand(1,5)==1 && !preg_match("/\b(dress|skirt|shirt)\b/i",$line))
				{ $mainCastPositions .= " ".actorName($speaker)." is removing ".actorPronounEir($speaker)." ".strtolower($matches[1])."."; $possibleLocation = "bedroom"; }
				else if (rand(1,4)==1)
				{ $mainCastPositions .= " ".actorName($speaker)." is adjusting ".actorPronounEir($speaker)." ".strtolower($matches[1])."."; }
				else { $mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1]))."."; }
			}
			else if (preg_match("/\b(suitcase|luggage|trunk|sack|portmanteau)s?\b/i",$line,$matches))
			{
				$verb = randomWord("putting down","holding","carrying","packing","unpacking");
			
				if ($verb == "packing") { $possibleLocation = randomWord("bedroom", "hotel room"); }
				if ($verb == "unpacking") { $possibleLocation = randomWord("bedroom", "hotel room"); }
				
				if ($matches[1] == "luggage")
				{ $mainCastPositions .= " ".actorName($speaker)." is $verb ".actorPronounEir($speaker)." luggage."; }
				else
				{ $mainCastPositions .= " ".actorName($speaker)." is $verb ".aOrAn(strtolower($matches[1]))."."; }
			}
			else if (preg_match("/\b(necklace|watch|ring|bracelet|brooch|shoe)e?s?\b/i",$line,$matches))
			{
				$verb = randomWord("holding","wearing","polishing","examining","repairing");

				if (rand(1,5)==1) { $mainCastPositions .= " ".actorName($speaker)." is removing ".actorPronounEir($speaker)." ".strtolower($matches[1])."."; $possibleLocation = "bedroom"; }
				else { $mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1])."."; }
			}
			elseif (preg_match("/\b(money|coins?|florins|francs|ducats|rubles|dollars|bank ?-?notes|buttons)\b/i",$line,$matches))
			{
				$verb = randomWord("counting out","examining some","sorting","searching through some");
				if ($matches[1] == "coin") { $matches[1] = "coins"; }
				$mainCastPositions .= " ".actorName($speaker)." is $verb ".strtolower($matches[1]).".";
			}
			elseif (preg_match("/\b(key)\b/i",$line,$matches))
			{
				$verb = randomWord("examining","holding","swinging","searching for");

				$mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1]).".";
			}
			elseif (preg_match("/\b(clock)\b/i",$line,$matches) && !preg_match("/clock/",$mainCastPositions)) // just one clock position
			{
				$verb = randomWord("looking at","watching","winding","adjusting");

				$mainCastPositions .= " ".actorName($speaker)." is $verb the ".strtolower($matches[1]).".";
			}
			else if (preg_match("/\b(horse|mule|donkey|bicycle)s?\b/i",$line,$matches) && rand(1,10)==1)
			{
				$verb = randomWord("riding","leading","brushing");			
				if (preg_match("/\b(bicycle)\b/",$line)) { $verb = randomWord("riding","pushing"); }
				else { $mainCastPositions .= " ".actorName($speaker)." is $verb a ".strtolower($matches[1])."."; }
			}
			else if (preg_match("/\b(carriage|cab|coach)s?\b/i",$line,$matches))
			{
				$verb = randomWord("emerges from","is hailing","climbs down from");			
				
				$mainCastPositions .= " ".actorName($speaker)." $verb a ".strtolower($matches[1]).".";
			}
			
			if ($possibleLocation != "" && $scenePlace == "") { $scenePlace = $possibleLocation; }
		}
		
		$recentText = substr($recentText,strlen($recentText)-1000);
		
		if ($focus == "")
		{
			$focuses = explode(" ",dropShortAndBoringWords($line));
			$focus = $focuses[array_rand($focuses)];
		}

		if (preg_match("/^Where/",$line) && rand(1,5)==1)
		{
			if ($actCount == 1)
			{ $focus = "Britain"; }
			else
			{ $focus = randomWord("Germany","France","Spain","Italy","Denmark","Sweden"); }
		}
		if (preg_match("/^How (old|long)/",$line)) { $focus = randomWord("year","month"); }
		if (preg_match("/^When/",$line)) { $focus = randomWord("today","tomorrow"); }
		if (preg_match("/^Who are/",$line)) { $focus = randomWord("I am"); }
		if (preg_match("/^Who has/",$line)) { $focus = randomWord("I have","He has","She has"); }
		if (preg_match("/^(do|did|have|will|would|could|can|should|may) (I|you|they)\b/i",$line,$inmatches))
		{
			if ($inmatches[2] == "you")
			{ $inmatches[2] = "I"; }
			elseif ($inmatches[2] == "I")
			{ $inmatches[2] = "you"; }
		
			$focus = $inmatches[2]." ".$inmatches[1];
		}
		if (preg_match("/(Mr\.s?\.? [A-Z])/",$line, $mrb)) // focus strongly on these names, including the Zelig-like Mr. B.
		{ $focus = $mrb[1]; }

		$previousLine = $line;

		if (rand(1,10)==1 && $sceneCount>1) { $focus = ""; }
		
		// send people off
		$justWalkedOff = "";
		if ((preg_match("/(fare|good)[- ]?(day|night|bye|well)/i",$line) || preg_match("/(adieu|let us be going|au revoir)/i",$line)) && (sizeof($castOnStage)>2 || $lineCount>5 )) // is someone leaving?
		{
			castWalkOff($speaker);
			$justWalkedOff = $speaker;
		}
		else if (rand(1,15)==1 && ((sizeof($castOnStage)>1 && $lineCount>5) || (sizeof($castOnStage)>2)))
		{
			$actor = $castOnStage[array_rand($castOnStage)];
			if (rand(1,2)==1) { $actor = $speaker; } // 50% chance of it being the person who just spoke

			// can only leave if they've spoken in this scene
			if (in_array($actor, $castHaveSpoken))
			{
				castWalkOff($actor);
				$justWalkedOff = $actor;
			}
		}

		if ($justWalkedOff != "")
		{
			// if there are props that will give us a means of transportation and it's one of the characters with a smaller vocabulary, they leave forever, at 5000-word intervals
			if (preg_match("/\b(ticket|passport|train|station|railway|ship|boat|ferry|coast|ocean|sea|carriage)s?\b/",$sceneScript) && vocabulary($justWalkedOff)<1001 && $wordCount>($wordTarget-(sizeof($fullCast)-8)*5000))
			{ 
				$fullCast = array_diff($fullCast, array("$justWalkedOff"));
				$sceneScript .= mainDirection(actorName($justWalkedOff)." exits, never to return");
			}
			else
			{ $sceneScript .= mainDirection(actorName($justWalkedOff)." exits"); }
		}		
		
		// decide whether to end the scene
		$leftToSpeak = array_diff($castOnStage, $castHaveSpoken);
		if ((($lineCount>10 && rand(1,20)==1 && empty($leftToSpeak)) || sizeof($castOnStage)==1 || $lineCount>100) && !preg_match("/\?$/",$line))
		{
			//print "<!--[SCENE OVER: lineCount=$lineCount, cast=".sizeof($castOnStage).", $wordCount words]-->";
			$lineCount=0;
			$sceneIsOver = 1;
		}
		
		// send people on
		if ($sceneIsOver == 0 && $justWalkedOff == "" && rand(1,sizeof($castOnStage)*8)==1 && sizeof($castOnStage)<sizeof($fullCast)) // if scene continues, decide whether to introduce a new character, based on how many people are already on stage
		{
			$actor = castWalkOn();
			
			if (!in_array($actor, $castHaveAppeared))
			{
				array_push($castHaveAppeared,$actor);
				if ($actor == "eo1" || $actor == "eo2")
				{ $sceneScript .= mainDirection(actorName($actor).", a stranger, enters"); }
				else
				{ $sceneScript .= mainDirection(actorName($actor)." enters"); }
			}
			else
			{ $sceneScript .= mainDirection(actorName($actor)." enters"); }
		}

		// miscellaneous stage directions
		if ($sceneIsOver == 0 && rand(1,15)==1)
		{
			$miscDirections = array();
			
			array_push($miscDirections,actorName($castOnStage[array_rand($castOnStage)])." PERFORMS SCENE EFFECT");
			array_push($miscDirections,actorName($castOnStage[array_rand($castOnStage)])." PERFORMS SCENE EFFECT");
			array_push($miscDirections,actorName($castOnStage[array_rand($castOnStage)])." crosses the stage");
			array_push($miscDirections,actorName($castOnStage[array_rand($castOnStage)])." walks to the ".randomWord("front","back","edge")." of the stage");

			if (sizeof($castOnStage)>3) { array_push($miscDirections,"everybody turns to look at ".actorName($castOnStage[array_rand($castOnStage)])); }
			
			$firstOfPair = actorName($castOnStage[array_rand($castOnStage)]);
			$secondOfPair = actorName($castOnStage[array_rand($castOnStage)]);
			
			if ($firstOfPair != $secondOfPair) { array_push($miscDirections,"$firstOfPair ".randomWord("glances at","exchanges glances with","glares at","smiles at","points to")." $secondOfPair"); }
			if ($firstOfPair != $secondOfPair) { array_push($miscDirections,"$firstOfPair ".randomWord("walks toward","approaches","sits next to")." $secondOfPair"); }
			
			shuffle($miscDirections);
			
			$sceneScript .= mainDirection($miscDirections[0]);
		}
	}
	
	if (rand(0,$wordTarget)<$wordCount && $actCount>1)
	{ $light = 1; }
	else
	{ $light = 0; }
	
	$sceneScript = sceneIntro($actCount,$sceneCount,$scenePlace,$mainCastPositions,$sceneScript,$initialCast,$light)."\n\n".$sceneScript;
	
	// replace placeholder directions now that the full scene script is known
	while (preg_match("/\[([A-Za-zø]+) PERFORMS SCENE EFFECT\]/",$sceneScript,$matches))
	{
		$scenery = array();
		$performer = $matches[1];
		
		if ($performer == "he" || $performer == "she") // inline direction
		{
			if (preg_match("/\b(slept|sleep)i/",$sceneScript)) { array_push($scenery,"yawning"); }
			if (preg_match("/\bcough/i",$sceneScript)) { array_push($scenery,"coughing"); }
			if (preg_match("/\bsneeze/i",$sceneScript)) { array_push($scenery,"sneezing"); }
			if (preg_match("/\bpapers/i",$sceneScript)) { array_push($scenery,"shuffling papers"); }
			if (preg_match("/\btable/i",$sceneScript) && !preg_match("/at the table/i",$sceneScript)) { array_push($scenery,randomWord("sitting down at","pulling up a chair at")." at the table"); } // only sit down once
			if (preg_match("/\bclock/i",$sceneScript)) { array_push($scenery,"looking at the clock"); }
			if (preg_match("/\b(money|coin|ducat)/i",$sceneScript) && rand(1,2)==1) { array_push($scenery,randomWord("examining","tossing","polishing","pocketing")." a coin"); }
			if (preg_match("/\b(key|lock)/i",$sceneScript)) { array_push($scenery,randomWord("unlocking","locking")." a door"); }
			if (preg_match("/\b(bread|meat|sausage|pork|beef|cheese|salad|turkey|cauliflower|cabbage|lettuce|cake|pudding|broth|soup|spinach|bacon|fruit|vegetables|peas|toast|venison|ham)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"eating some ".strtolower($inmatches[1])); }
			if (preg_match("/\b(cake|pudding|muffin|biscuit|carrot|potato|tomato)e?s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"eating a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(rifle|pistol|gun)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"reloading the ".strtolower($inmatches[1])); }
			if (preg_match("/\b(coffee|tea|milk|wine|claret|beer|juice|grog)\b/i",$sceneScript,$inmatches)) { array_push($scenery,randomWord("drinking","pouring")." some ".strtolower($inmatches[1])); }
			if (preg_match("/\bfire/i",$sceneScript)) { array_push($scenery,randomWord("poking","adding a log to","adding some twigs to","staring into")." the fire"); }
			if (preg_match("/\b(cigar|cigarette|candle|lamp|lantern)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"lighting a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(cigar|cigarette)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"stubbing out a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(fork|knife|spoon|ladle|pen|pencil|hammer)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,randomWord("putting down","picking up")." a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(glove|sock|shoe|boot)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,randomWord("putting on","removing")." a ".strtolower($inmatches[1])); }
		}
		else
		{
			if (preg_match("/\brain/i",$sceneScript) && $light == 0) { array_push($scenery,"rain lashes down"); }
			if (preg_match("/\bshower/i",$sceneScript)) { array_push($scenery,"a light rain falls"); }
			if (preg_match("/\bsnow/i",$sceneScript)) { array_push($scenery,"snow falls"); }
			if (preg_match("/\bit is night/i",$sceneScript)) { array_push($scenery,randomWord("an owl hoots","a wolf howls")); }
			if (preg_match("/\b(thunder|storm|lightning|tempest)s?\b/i",$sceneScript) && $light == 0) { array_push($scenery,"thunder ".randomWord("rumbles","crashes","booms")); }
			if (preg_match("/\bbird/i",$sceneScript)) { array_push($scenery,"birds ".randomWord("sing","twitter","fly past","scatter")); }
			if (preg_match("/\b(cafe|restaurant)/i",$sceneScript)) { array_push($scenery,"a ".randomWord("waiter","waitress")." ".randomWord("takes $performer's order","serves $performer's food")); }
			if (preg_match("/\bbells?\b/i",$sceneScript)) { array_push($scenery,"a bell ".randomWord("rings","sounds","chimes")); }
			if (preg_match("/\bwind[ys]?\b/i",$sceneScript)) { array_push($scenery,"leaves blow across the stage"); }
			if (preg_match("/\btelephone\b/i",$sceneScript)) { array_push($scenery,"a telephone rings"); }
			if (preg_match("/\bdog/i",$sceneScript)) { array_push($scenery,"a dog ".randomWord("barks","growls","whines","snarls","runs past")); }
			if (preg_match("/\bcat/i",$sceneScript)) { array_push($scenery,"a cat ".randomWord("purrs","miaows","hisses","runs past")); }
			if (preg_match("/\b(train|railway)s?\b/i",$sceneScript)) { array_push($scenery,"a train ".randomWord("passes","whistle blows","pulls up","departs")); }
			if (preg_match("/\bhorse\b/i",$sceneScript,$matches)) { array_push($scenery,"a horse ".randomWord("neighs","whinnies","snorts")); }

			if (preg_match("/\b(cigar|cigarette|candle|lamp|lantern)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer lights a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(cigar|cigarette)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer stubs out a ".strtolower($inmatches[1])); }
			if (preg_match("/\bsleepi/",$sceneScript)) { array_push($scenery,"$performer yawns"); }
			if (preg_match("/\bcough/i",$sceneScript)) { array_push($scenery,"$performer coughs"); }
			if (preg_match("/\bsneeze/i",$sceneScript)) { array_push($scenery,"$performer sneezes"); }
			if (preg_match("/\bpassport/i",$sceneScript)) { array_push($scenery,"$performer ".randomWord("leafs through","picks up","examines")." a passport"); }
			if (preg_match("/\b(suitcase|trunk|case|portmanteau)/i",$sceneScript)) { array_push($scenery,"$performer ".randomWord("opens","closes","hefts","begins to unpack","begins to pack")." a suitcase"); }
			if (preg_match("/\b(painting|picture|portrait)/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer ".randomWord("looks at","inspects","admires")." ".aOrAn(strtolower($inmatches[1]))); }
			if (preg_match("/\btable/i",$sceneScript) && !preg_match("/at the table/i",$sceneScript)) { array_push($scenery,"$performer ".randomWord("sits down at","pulls up a chair at")." the table"); }
			if (preg_match("/\bclock/i",$sceneScript)) { array_push($scenery,"$performer looks at the clock"); }
			if (preg_match("/\b(money|coin)/i",$sceneScript)) { array_push($scenery,"$performer tosses a coin"); }
			if (preg_match("/\b(key|lock|unlock)/i",$sceneScript)) { array_push($scenery,"$performer ".randomWord("unlocks","locks")." a door"); }
			if (preg_match("/\b(fork|knife|spoon|ladle|hammer)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer ".randomWord("puts down","picks up","cleans","inspects")." a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(rifle|pistol|gun)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer ".randomWord("reloads","picks up","cleans","inspects")." a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(bread|meat|sausage|pork|beef|cheese|salad|turkey|cauliflower|cabbage|lettuce|soup|spinach|broth|fruit|vegetables|peas|ham)s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer eats some ".strtolower($inmatches[1])); }
			if (preg_match("/\b(cake|pudding|muffin|biscuit|carrot|potato|tomato)e?s?\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer eats a ".strtolower($inmatches[1])); }
			if (preg_match("/\b(coffee|tea|milk|wine|claret|beer|juice|grog)\b/i",$sceneScript,$inmatches)) { array_push($scenery,"$performer ".randomWord("drinks","pours")." some ".strtolower($inmatches[1])); }
			if (preg_match("/\bfire/i",$sceneScript)) { array_push($scenery,"$performer ".randomWord("pokes","adds a log to","stares into")." the fire"); }
		}

		//  && !preg_match("/".$matches[1]."/",$mainCast) was lying around here, not sure why
		if (sizeof($scenery)>0)
		{
			shuffle($scenery);
			$sceneScript = preg_replace("/\[".$performer." PERFORMS SCENE EFFECT\]/","[".$scenery[0]."]",$sceneScript,1); 
		}
		else
		{
			$sceneScript = preg_replace("/\[".$performer." PERFORMS SCENE EFFECT\]/","",$sceneScript,1); 
		}
	}
	
	print $sceneScript;
	$wordCount += str_word_count(strip_tags($sceneScript));
	
	$previousLine = "";
	$focus = "";
	$castOnStage = array();
	$castHaveSpoken = array();
	castWalkOn();
	castWalkOn();
	if (rand(1,3)==1) { castWalkOn(); }
	$initialCast = $castOnStage;
	$previousSpeaker = "";
	$nextSpeaker = "";
	$sceneCount++;
	if ($wordCount>$wordTarget/3 && $actCount == 1)
	{
		$sceneCount = 1;
		$actCount++;

		// Esperanto characters arrive
		array_push($fullCast,"eo1");
		array_push($fullCast,"eo2");
	}
	if ($wordCount>$wordTarget*2/3 && $actCount == 2)
	{
		$sceneCount = 1;
		$actCount++;
		
		// Esperanto characters leave
		$fullCast = array_diff($fullCast, array("eo1"));
		$fullCast = array_diff($fullCast, array("eo2"));
	}
	$lineCount = 1;
}

print "<!-- FINAL WORD COUNT = $wordCount -->";

print ending();

function dropShortAndBoringWords($text)
{
	$text = preg_replace("/\b[A-Za-z][A-Za-z]?[A-Za-z]?\b/","",$text);
	$text = preg_replace("/\./","",$text);
	$text = preg_replace("/ +/"," ",$text);
	$text = preg_replace("/(that|with|have|this|will|your|from|they|been|much|some)/i"," ",$text);
	return $text;
}

function cleanLine($line)
{
	$line = preg_replace("/[:;,]$/","",$line);
	if (!preg_match("/[.?!:;,\"]$/",$line)) { $line .= "."; }
	$line = ucfirst($line);
	return $line;
}

function direction($text)
{
	return "<i>[".$text."]</i>";
}

function mainDirection($text)
{
	return "<p>".direction($text)."</p>";
}

function actorName($speaker)
{
	switch ($speaker)
	{
		case "eo1": return "Ludwik"; break;
		case "eo2": return "Helen"; break;
		case "es": return "Nina"; break;
		case "es2": return "Carla"; break;
		case "es3": return "Isabella"; break;
		case "fr3": return "Marie"; break;
		case "fr2": return "Antoine"; break;
		case "it": return "Guiseppe"; break;
		case "it2": return "Nina"; break;
		case "de1": return "Otto"; break;
		case "de2": return "Frieda"; break;
		case "nl": return "Jan"; break;
		case "ie": return "Quinn"; break;
		case "dk": return "Karen"; break;
		case "se": return "Elias"; break;
		case "no": return "Bjørn"; break;		
		case "pl": return "Dominik"; break;		
		return "Anon"; break;
	}
	
 
}

function actorPronounEir($speaker)
{
	switch ($speaker)
	{
		case "eo1": return "his"; break;
		case "eo2": return "her"; break;
		case "es": return "her"; break;
		case "es2": return "her"; break;
		case "es3": return "her"; break;
		case "fr3": return "her"; break;
		case "fr2": return "his"; break;
		case "it": return "his"; break;
		case "it2": return "her"; break;
		case "de1": return "his"; break;
		case "de2": return "her"; break;
		case "nl": return "his"; break;
		case "ie": return "his"; break;
		case "dk": return "her"; break;
		case "se": return "her"; break;
		case "no": return "his"; break;
		case "pl": return "his"; break;
		return "their"; break;
	}
}

function actorPronounE($speaker)
{
	if (actorPronounEir($speaker) == "his") { return "he"; }
	return "she";
}

function languageLine($speaker)
{
	switch ($speaker)
	{
		case "eo1": return esperantoLine(); break;
		case "eo2": return secondEsperantoLine(); break;
		case "es": return spanishLine(); break;
		case "es2": return secondSpanishLine(); break;
		case "es3": return thirdSpanishLine(); break;
		case "fr2": return secondFrenchLine(); break;
		case "fr3": return thirdFrenchLine(); break;
		case "it": return firstItalianLine(); break;
		case "it2": return secondItalianLine(); break;
		case "de1": return firstGermanLine(); break;
		case "de2": return secondGermanLine(); break;
		case "nl": return firstDutchLine(); break;
		case "ie": return firstIrishLine(); break;
		case "dk": return firstDanishLine(); break;
		case "se": return firstSwedishLine(); break;
		case "no": return firstNorwegianLine(); break;
		case "pl": return firstPolishLine(); break;
		return "Anon"; break;
	}
}

function vocabulary($speaker)
{
	switch ($speaker)
	{
		case "eo1": return 1770; break;
		case "eo2": return 890; break;
		case "es": return 700; break;
		case "es2": return 4190; break;
		case "es3": return 2640; break;
		case "fr2": return 840; break;
		case "fr3": return 450; break;
		case "it": return 1000; break;
		case "it2": return 1480; break;
		case "de1": return 840; break;
		case "de2": return 860; break;
		case "nl": return 1330; break;
		case "ie": return 650; break;
		case "dk": return 1190; break;
		case "se": return 930; break;
		case "no": return 1150; break;
		case "pl": return 5680; break;
		return 100; break;
	}
}

function aOrAn($word)
{
	if (preg_match("/^[aeiou]/",$word)) { return "an $word"; }
	return "a $word";
}

function randomWord($a="",$b="",$c="",$d="",$e="",$f="",$g="",$h="")
{
	$words = array($a,$b,$c,$d,$e,$f,$g,$h);
	$words = array_filter($words);
	
	$chosen = rand(0,(sizeof($words)-1)*(sizeof($words)-1))	;
	$chosen = floor(sqrt($chosen));

	if (sizeof($words)<1) { return ""; }
	
	return $words[$chosen];
}

function fetchLine($feedline,$focus,$speaker,$actCount)
{
	global $recentText;
	//return "lorem ipsum dolor";
	
	if ($feedline == "") { return languageLine($speaker); } 

	$bestline = "..."; $bestscore = 0;
	
	if (rand(1,3)==1) { $focus = ""; } // occasionally ignore the topic
	
	for ($i=0; $i<min(1000,vocabulary($speaker)/2); $i++)
	{
		$tryline = languageLine($speaker);

		if (stripos($recentText,cleanLine($tryline)) !== false)
		{
			 // prevent repetition of recent lines
		}
		else if ($actCount==3 && preg_match("/(England|London|Britain|British|English)/i",$tryline))
		{
			// stop talking about Britain entirely in third act
		}	
		else
		{
			// array of related words which can make connections between sentences
			$synonyms = array(
				array("Britain","England","London","Europe","British","English"),
				array("England","France","Germany","Italy","Spain","Denmark","Sweden","country","nation","island","Europe","world"),
				array("London","Paris","Berlin","Copenhagen","Rome"),
				array("flower","garden","tree","grass","fruit","leaf"),
				array("fruit","apple","pear","peach","berry","berries","orange"),
				array("flower","rose","violet","daisy"),
				array("boy","brother","son","father","uncle","_his_"), // underscore to make it a one-way replacement
				array("daughter","girl","sister","mother","aunt","_her_"),
				array("rain","sun","cloud","snow","wind","weather","shower"),
				array("January","February","March","April","May","June","July","August","September","October","November","December"),
				array("spring","summer","autumn","winter"),
				array("today","yesterday","tomorrow"),
				array("hour","minute","clock","wait"),
				array("money","coin","note","wage","gold","bill","rich","poor","how much","£","price","ducat","florin"),
				array("god","jesus","church","priest","pray","christ","pastor","bishop","parish","cathedral"),
				array("chess","king","bishop","castle","knight","checkmate"),
				array("king","queen","prince","royal"),
				array("police","law","crime","judge","thief","robber","kill"),
				array("death","die","ill","illness","doctor","disease","wound","sick","health","surgeon","physician"),
				array("coffee","tea","wine","beer","milk"),
				array("piano","guitar","harp","flute","trumpet","instrument","music"),
				array("cat","dog","mouse"),
				array("dog","hound"),
				array("pig","horse","cow","farm","chicken","mule","animal","creature","lion"),
				array("gold","silver","steel","iron","bronze","copper"),
				array("fire","smoke","blaze","burn","hot"),
				array("cigar","tobacco","snuff"),
				array("stone","rock"),
				array("grave","coffin"),
				array("ship","boat","cabin","steamer","deck","sloop"),
				array("theatre","opera","actor","actress"),
				array("museum","gallery","exhibition"),
				array("laugh","joke","jest","amusing"),
				array("walk","foot","march"),
				array("shoe","boot"),
				array("soldier","war","battle"),
				array("toy","game","ball","chess","piquet","play","cards"),
				array("bed","sleep","dream","night"),
				array("house","home","castle","building","tower"),
				array("knife","sword","dagger","sabre"),
				array("gun","bullet","cannon","rifle","shoot","war"),
				array("meat","flesh","beef","pork","chicken","food","dinner"),
				array("paper","book","newspaper","reading","page"),
				array("letter","words","write","pen"),
				array("council","government","election","mayor")
			);

			$bestsynscore = scoreLinePair($feedline,$tryline,$focus,$actCount);
			foreach ($synonyms as $synlist) {
				if (is_array($synlist)){
					foreach ($synlist as $s1) {
						foreach ($synlist as $s2) {
							if ($s1 != $s2)
							{
								$synline = preg_replace("/\b".$s1."(s?)\b/",$s2."$1",$tryline,-1,$hits);
								if ($hits>0)
								{
									//print "($s1:$s2|$synline)";
									$tryscore = scoreLinePair($feedline,$synline,$focus,$actCount);
									if ($tryscore > $bestsynscore) { $bestsynscore = $tryscore; }
								}
							}
						}
					}
				}
			}
					
			if ($bestsynscore>$bestscore && $feedline <> $tryline && stripos($recentText,$tryline) == false)
			{
				//print "<!-- $recentText != $tryline -->";
				$bestline = $tryline;
				$bestscore = $bestsynscore;
			}
			else
			{
//				print "(didn't use $tryline / $bestsynscore)\n";
			}
		}
	}
	
	//print "(used $bestline = ".stripos($recentText,$bestline)." after $feedline, score=$bestscore)\n";
	
	return $bestline;
}

function scoreLinePair($first,$second,$focus,$actCount)
{
	//$tryscore = 1000-levenshtein(substr($first,0,255),substr($second,0,255));
	$tryscore = similar_text($first,$second,$percent);
	$tryscore = $percent*4;

	if ($focus != "" && strpos($second,$focus) !== false) { $tryscore += 100; } // favour sentences that include the focus word
	
	// favour possible answers to questions (may be rhetorical if the same person says both)
	if (preg_match("/^Who.+\?$/",$first) && preg_match("/^(I|He|She|Mr)\b/",$second)) { $tryscore += 50; }
	if (preg_match("/^(Is|Are|Have|Did|Do|Does) .+\?$/",$first) && preg_match("/^(Yes|No)\b/",$second)) { $tryscore += 50; }
	if (preg_match("/(are|have|did) you.+\?$/i",$first) && preg_match("/^I /",$second)) { $tryscore += 50; }
	if (preg_match("/where .+\?$/i",$first) && preg_match("/^(At|In) /",$second)) { $tryscore += 50; }
	if (preg_match("/where .+\?$/i",$first) && preg_match("/\bt?here\b/",$second)) { $tryscore += 50; }
	if (preg_match("/^When .+\?$/i",$first) && preg_match("/\b(today|tomorrow|now|yesterday|next|month|day|year|o'clock|morning|evening)s?\b/",$second)) { $tryscore += 50; }
	if (preg_match("/what time/i",$first) && preg_match("/(o'clock|morning|evening)/",$second)) { $tryscore += 50; }
	if (preg_match("/what day/i",$first) && preg_match("/(day|tomorrow)/",$second)) { $tryscore += 50; }
	if (preg_match("/^Is it.+\?$/",$first) && preg_match("/^(Yes|No|It is) /",$second)) { $tryscore += 50; }
	if (preg_match("/is it\?$/i",$first) && preg_match("/^(It is) /",$second)) { $tryscore += 50; }

	if (strlen($second)>80) { $tryscore -= 25; } // disfavour long sentences
	if (strlen($second)>200) { $tryscore -= 25; } // even more for really long sentences
	if (preg_match("/\?$/",$first) && preg_match("/\?$/",$second)) { $tryscore -= 100; } // strongly discourage run-on questions

	return $tryscore;
}

function castWalkOn()
{
	global $fullCast, $castOnStage;
	
	if (sizeof($fullCast)==sizeof($castOnStage)) { return "nobody"; }
	
	$castBackstage = array_diff($fullCast, $castOnStage);
	$actor = $castBackstage[array_rand($castBackstage)];

	array_push($castOnStage,$actor);
	
	return $actor;
}

function castWalkOff($language)
{
	global $castOnStage;

	$castOnStage = array_diff($castOnStage, array("$language"));
	return 1;
}

function sceneIntro($actCount,$sceneCount,$scenePlace = "", $mainCast = "", $sceneScript = "", $initialCast = "", $light = 0)
{	
	$darkAdjectives = array("smoky","quiet","dark","dimly-lit","gloomy","shadowy","deserted","small");
	$lightAdjectives = array("empty","quiet","bright","peaceful");
	
	$basicPlaces = array("kitchen","bar","restaurant","church","cafe","hotel lobby","railway platform","tavern","railway carriage","office","back room","corridor","hotel room",
					"road","forest","path","field","public park","street","alleyway","garden","bridge","beach");
	$places = $basicPlaces;

	$themedPlaces = array();
	if (preg_match("/\btable/",$sceneScript) && $actCount!=2)
	{ $themedPlaces = array_merge($themedPlaces,array("kitchen","bar","restaurant","lounge","cafe","tavern")); }
	if (preg_match("/\b(desk|work|office)/",$sceneScript) && $actCount<2)
	{ $themedPlaces = array_merge($themedPlaces,array("office")); }
	if (preg_match("/\b(tree|path|field)/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("forest","path","field")); }
	if (preg_match("/\b(doctor|patient|nurse)/",$sceneScript) && $actCount<2)
	{ $themedPlaces = array_merge($themedPlaces,array("hospital ward")); }
	if (preg_match("/\b(fire|tent)s?/",$sceneScript) && $actCount<3)
	{ $themedPlaces = array_merge($themedPlaces,array("campfire","campsite")); }
	if (preg_match("/\b(hotel|bed)/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("bedroom","hotel room","hotel lobby")); }
	if (preg_match("/\b(bath)(ing|es|er)?/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("bathroom","hotel room","beach")); }
	if (preg_match("/\b(wash)(ing|es|er)?/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("bathroom","hotel room","kitchen","back room","beach")); }
	if (preg_match("/\b(swim)(ming|s|mer)?/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("beach")); }
	if (preg_match("/\b(church|priest|god|God|pray|parish|clergy|funeral)/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("church","churchyard")); }
	if (preg_match("/\b(waiter|bill|meal)s\b/",$sceneScript) && $actCount<3)
	{ $themedPlaces = array_merge($themedPlaces,array("restaurant","bar","cafe")); }
	if (preg_match("/\btheatre/",$sceneScript) && $actCount!=2)
	{ $themedPlaces = array_merge($themedPlaces,array("theatre bar","theatre lobby")); }
	if (preg_match("/\blibrary/",$sceneScript) && $actCount==3)
	{ $themedPlaces = array_merge($themedPlaces,array("library")); }
	if (preg_match("/\bmuseum/",$sceneScript) && $actCount==3)
	{ $themedPlaces = array_merge($themedPlaces,array("museum")); }
	if (preg_match("/\bgallery/",$sceneScript) && $actCount==3)
	{ $themedPlaces = array_merge($themedPlaces,array("gallery")); }
	if (preg_match("/\b(river|canal|bridge|dock)s\b/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("bridge","dock")); }

	// if someone's left the play in this scene, we need it to be at a port of departure
	if (preg_match("/exits, never to return/",$sceneScript))
	{ $themedPlaces = array(); }

	// ports of departure
	if (preg_match("/\b(carriage|cab|coach)s\b/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("horse-drawn carriage")); }
	if (preg_match("/\b(train|station|railway)s\b/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("railway platform","railway carriage")); }
	if (preg_match("/\b(ship|boat|ferry|coast|ocean|sea|quay)s?\b/",$sceneScript) && $actCount==2)
	{ $themedPlaces = array_merge($themedPlaces,array("ship at sea","dock","harbour")); }
	elseif (preg_match("/\b(ship|boat|ferry|coast|ocean|sea)s?\b/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("dock","harbour","lighthouse","ship at harbour")); }
	if (preg_match("/\b(ticket|passport)s\b/",$sceneScript))
	{ $themedPlaces = array_merge($themedPlaces,array("railway station","dock","ticket office")); }

	$extrasList = "";
	foreach ($initialCast as $language)
	{
		if (!preg_match("/".actorName($language)."/",$mainCast))
		{ $extrasList .= actorName($language).", "; }
	}

	if (sizeof($themedPlaces)>0) { $scenePlace = $themedPlaces[array_rand($themedPlaces)]; } 
	else { $scenePlace = $places[array_rand($places)]; } 

	if (sizeof($initialCast)>2 && $scenePlace == "bedroom") { $scenePlace = $basicPlaces[array_rand($basicPlaces)]; }

	$indoorRegexp = "/(kitchen|bar|restaurant|church|room|lobby|lounge|cafe|tavern|lighthouse|ward|office|corridor|station|carriage|gallery|museum|library)$/";
	
	if (preg_match($indoorRegexp,$scenePlace))
	{ $inside = 1; $window = " ".randomWord("outside","outside the window"); }
	else
	{ $inside = 0; $window = ""; }

	if ($inside==1 && $actCount==2)
	{
		// bump people outdoors (or outdoors-ish) in act 2
		$themedPlaces = array("railway station","ticket office","road","forest","path","field","public park","street","alleyway","garden","bridge","beach","checkpoint","wasteland","ruin");
		$scenePlace = $themedPlaces[array_rand($themedPlaces)];
	}
	
	// reapply outsideness
	if (preg_match($indoorRegexp,$scenePlace))
	{ $inside = 1; $window = " ".randomWord("outside","outside the window"); }
	else
	{ $inside = 0; $window = ""; }

	if ($inside == 0)
	{
		$darkAdjectives = array_merge($darkAdjectives,array("windswept","litter-strewn","sunless","untidy","secluded"));
		$lightAdjectives = array_merge($lightAdjectives,array("well-kept","sunny","secluded"));
		if (preg_match("/(garden|forest|path|public park|road|field)$/",$scenePlace))
		{
			$darkAdjectives = array_merge($darkAdjectives,array("overgrown","untended"));
			$lightAdjectives = array_merge($lightAdjectives,array("leafy","well-tended"));
		}
		$mainCast = preg_replace("/the clock/","a clock",$mainCast);
	}
	else
	{
		$darkAdjectives = array_merge($darkAdjectives,array("undecorated","damp","dilapidated","grimy","sparsely-furnished","cold","cramped"));
		$lightAdjectives = array_merge($lightAdjectives,array("smart","pleasant","modern","clean","well-furnished","warm","spacious","modest"));
		$mainCast = preg_replace("/(riding|leading|brushing) (an? [a-z]+)\b/","watching $2 through the window",$mainCast); // no riding animals indoors
	}
	
	$extrasList = preg_replace("/, ([^,]+), $/"," and $1",$extrasList);
	if (preg_match("/, $/",$extrasList))
	{
		$secondPose = randomWord("approaches","is nearby","enters","sits nearby","arrives");
		if (preg_match("/(kitchen|bar|restaurant|room|cafe|tavern)$/",$scenePlace))
		{ $secondPose = randomWord("sits at a table", "enters and sits down","stands at the door","approaches","is nearby"); }
		elseif (preg_match("/(office)$/",$scenePlace))
		{ $secondPose = randomWord("sits across the desk","sits at a desk", "enters and sits down","stands at the door","approaches","is nearby"); }
		else if ($scenePlace == "church" && rand(1,2)==1)
		{ $secondPose = randomWord("sits in a pew","stands at the altar","stands in the aisle","stands in front of a window"); }
		else if ($scenePlace == "lighthouse")
		{ $secondPose = randomWord("looks out to sea","is nearby","descends from above"); }
		else if ($scenePlace == "ship at sea")
		{ $secondPose = randomWord("looks out to sea","is on deck","enters from below"); }
		else if ($scenePlace == "railway carriage")
		{ $secondPose = randomWord("sits opposite","enters the carriage","takes a seat","boards the train","is seated"); }
		else if ($scenePlace == "horse-drawn carriage")
		{ $secondPose = randomWord("sits opposite","enters the carriage","takes a seat","boards the carriage","is seated"); }
		else if ($inside == 1 && rand(1,4)==1)
		{ $secondPose = randomWord("stands at the door","stands in front of a window","enters from the street"); }
		$extrasList = " ".preg_replace("/, $/","",$extrasList)." ".$secondPose.".";
	}
	else if ($extrasList != "")
	{
		$isare = "is";
		if (preg_match("/ and /",$extrasList)) { $isare = "are"; }
		
		if ($mainCast != "")
		{ $secondPose = randomWord("are here","are nearby","are across the stage","approach","arrive","look on"); }
		else
		{ $secondPose = randomWord("are here","stand together","walk together","approach each other","stand far apart from one another"); }

		if (preg_match("/(kitchen|bar|restaurant|room|cafe|tavern)$/",$scenePlace))
		{ $secondPose = randomWord("are here","sit around a table","are sitting at a table", "enter and sit down at a table"); }
		elseif (preg_match("/(office)$/",$scenePlace))
		{ $secondPose = randomWord("are here","sit at their desks","enter and sit down at their desks"); }
		else if ($scenePlace == "church" && rand(1,2)==1)
		{ $secondPose = randomWord("are sitting among the pews","stand at the door","stand in the aisle"); }
		else if ($scenePlace == "lighthouse")
		{ $secondPose = randomWord("are here","are nearby","descend from above"); }
		else if ($scenePlace == "ship at sea")
		{ $secondPose = randomWord("are on deck"); }
		else if ($scenePlace == "railway carriage")
		{ $secondPose = randomWord("are seated","board the train","enter the carriage"); }
		else if ($inside == 1 && rand(1,4)==1)
		{ $secondPose = randomWord("stand at the door","enter from the street"); }

		//$extrasList = " ".preg_replace("/, $/","",$extrasList);

		$extrasList = " $extrasList $secondPose.";
	}

	global $castHaveAppeared;
	if (!in_array("eo1", $castHaveAppeared) && strpos($mainCast.$extrasList,actorName("eo1"))>0)
	{
		array_push($castHaveAppeared,"eo1");
		$mainCast = preg_replace("/".actorName("eo1")."/",actorName("eo1").", a stranger, ",$mainCast);
		$extrasList = preg_replace("/".actorName("eo1")."([, ])/",actorName("eo1")." (a stranger)$1",$extrasList);
	}
	if (!in_array("eo2", $castHaveAppeared) && strpos($mainCast.$extrasList,actorName("eo2"))>0)
	{
		array_push($castHaveAppeared,"eo2");
		$mainCast = preg_replace("/".actorName("eo2")."/",actorName("eo2").", a stranger, ",$mainCast);
		$extrasList = preg_replace("/".actorName("eo2")."([, ])/",actorName("eo2")." (a stranger)$1",$extrasList);
	}
	
	$scenery = array();

	// only one possible weather/time-of-day
	if (preg_match("/\b(rain|shower)/",$sceneScript)) { array_push($scenery," It is raining$window."); }
	elseif (preg_match("/\bsnow/",$sceneScript)) { array_push($scenery," It is snowing$window."); }
	elseif (preg_match("/\b(moon|night|dark)/",$sceneScript) && $light == 0) { array_push($scenery," It is night."); }
	elseif (preg_match("/\b(thunder|storm|lightning|tempest)s?\b/",$sceneScript) && $light == 0) { array_push($scenery," Thunder rumbles."); }
	elseif (preg_match("/\bsun/",$sceneScript)) { array_push($scenery," The sky is clear$window."); }
	
	// additional scenery effects
	if (preg_match("/\bfire/",$sceneScript)) { array_push($scenery," A fire burns."); }
	if (preg_match("/\bfog/",$sceneScript) && $light == 0) { array_push($scenery," Dry ice swirls across the stage."); }
	if (preg_match("/\bbells?\b/",$sceneScript)) { array_push($scenery," A bell rings."); }
	if (preg_match("/\bwind[ys]?\b/",$sceneScript) && $inside == 1) { array_push($scenery," Leaves blow past the window."); }
	if (preg_match("/\bwind[ys]?\b/",$sceneScript) && $inside == 0) { array_push($scenery," Leaves blow across the stage."); }
	if (preg_match("/\bdog/",$sceneScript)) { array_push($scenery," A dog barks."); }
	if (preg_match("/\btelephon/",$sceneScript)) { array_push($scenery," A telephone rings."); }
	if (preg_match("/\b(cigar|smoke|tobacco)/",$sceneScript) && $light == 0) { array_push($scenery," Smoke is in the air."); }
	if (preg_match("/\b(piano|guitar|harp|flute|trumpet|violin|harp)s?\b/",$sceneScript,$matches)) { array_push($scenery," A ".$matches[1]." plays."); }
	if (preg_match("/\b(carriage|cab|boat|ship|wagon)/",$sceneScript,$matches) && $inside == 0) { array_push($scenery," A ".$matches[1]." is waiting."); }
	
	if (rand(1,5)==1 && preg_match("/\b(goat|cow|horse|sheep|pig|goat|ox|bull)s?\b/",$sceneScript,$matches) && $inside == 1 && !preg_match("/".$matches[1]."/",$mainCast)) { array_push($scenery," ".ucfirst(aOrAn($matches[1]))." is outside."); }
	if (rand(1,5)==1 && preg_match("/\b(goat|cow|horse|sheep|pig|goat|ox|bull)s?\b/",$sceneScript,$matches) && $inside == 0 && !preg_match("/".$matches[1]."/",$mainCast)) { array_push($scenery," ".ucfirst(aOrAn($matches[1]))." stands downstage."); }
	
	// use three at random
	shuffle($scenery);
	array_splice($scenery, 3);	
	
	if ($light==1)
	{ $adjectives = $lightAdjectives; }
	else
	{ $adjectives = $darkAdjectives; }
	
	return "<h2>ACT $actCount, SCENE $sceneCount</h2><p><i>".ucfirst(aOrAn($adjectives[array_rand($adjectives)]))." ".$scenePlace.".".implode($scenery).$mainCast.$extrasList."</i></p>";
}

function frontispiece()
{
	return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">
<html>
<head>
<title>Out Of Nowhere</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
<LINK rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/nano2018.css\">
</HEAD>
<body>

<h1>Out of Nowhere</h1>
<p>Generated for NaNoGenMo 2018, from a script by Kevan Davis.</p>

<h2>Cast</h2>


<ul>
<li><b>Antoine</b>: <i><a href=\"https://www.gutenberg.org/ebooks/11748\">French Conversation and Composition</a></i> by Harry Vincent Wann, 1920
<li><b>Bjørn</b>: <i><a href=\"https://archive.org/details/norwegiandanishe00stev\">Norwegian-Danish and English conversation teacher</a></i> by Charles McClellan Stevens, 1905
<li><b>Carla</b>: <i><a href=\"https://archive.org/details/spanishteachera00butlgoog/\">The Spanish Teacher and Colloquial Phrasebook</a></i> by Francis Butler, 1864
<li><b>Dominik</b>: <i><a href=\"https://archive.org/details/manualofpolishen00kasprich\">A Manual of Polish and English Conversation</a></i> by Erazm Lucyan Kasprowicz and Julius Cornet, 1912
<li><b>Elias</b>: <i><a href=\"https://archive.org/stream/newpracticaleasy00lensrich\">A New, Practical and Easy Method of Learning the Swedish Language</a></i> by Carl Julius Lenstrom, 1908
<li><b>Frieda</b>: <i><a href=\"https://archive.org/details/firstyeargerman03collgoog\">First Year German</a></i> by William C. Collar, 1905
<li><b>Guiseppe</b>: <i><a href=\"https://www.gutenberg.org/ebooks/50419\">Exercises upon the Different Parts of Italian Speech</a></i> by F. Bottarelli, 1822
<li><b>Helen</b>: <i><a href=\"http://www.gutenberg.org/ebooks/8177\">The Esperanto Teacher: A Simple Course for Non-Grammarians</a></i> by Helen Fryer, 1907
<li><b>Isabella</b>: <i><a href=\"https://archive.org/details/hossfeldsspanish41915gut\">Hossfeld's Spanish Dialogues</a></i> by C. Hossfeld and W. N. Cornett, 1915
<li><b>Jan</b>: <i><a href=\"https://archive.org/details/hossfeldsdutchdi00londuoft/\">Hossfeld's Dutch Dialogues</a></i> by Unknown, 1903
<li><b>Karen</b>: <i><a href=\"https://archive.org/details/spanishteachera00butlgoog/\"> The Danish Speaker</a></i> by Maria Bojesen, 1865
<li><b>Ludwik</b>: <i><a href=\"https://www.gutenberg.org/ebooks//7787\">A Complete Grammar of Esperanto</a></i> by Ivy Kellerman, 1910
<li><b>Marie</b>: <i><a href=\"https://www.gutenberg.org/ebooks/29398\">French Reader on the Cumulative Method</a></i> by Adolphe Dreyspring, 1892
<li><b>Nina</b>: <i><a href=\"https://archive.org/details/englishitalianph00walluoft/\">English-Italian Phrase Book for Social Workers</a></i> by Edith Waller, 1916
<li><b>Otto</b>: <i><a href=\"https://archive.org/stream/materialforexerc00horn\">Material for Exercises in German Composition</a></i> by L. E. Horning, 1895
<li><b>Pablo</b>: <i><a href=\"https://archive.org/details/englishitalianph00walluoft/\">Pitman's Commercial Spanish Grammar (2nd ed.)</a></i> by C. A. Toledano, 1917
<li><b>Quinn</b>: <i><a href=\"https://archive.org/details/cainntnandaoinei00lauoft\">Irish Dialogues</a></i> by Pádraig Ó Laoghaire, 1900
</ul>

";
}

function ending()
{
	return "<h1>THE END</h1></body></html>";
}


?>

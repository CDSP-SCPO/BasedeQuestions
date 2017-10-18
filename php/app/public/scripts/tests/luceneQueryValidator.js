//$.getScript('/scripts/tests/luceneQueryValidator.js');

(function(){
	
	function runTests(tests)
	{
		var l = tests.length;
		
		for (var i = 0; i < l; i ++)
		{
			test = tests[i];
			query = test[0];
			expectedResult = test[1];
			msg = test[2];
			result = luceneQueryValidator.checkQuery(query);
			fireunit.ok(result == expectedResult, query + ' : ' + msg);
		}
	}
	
	luceneQueryValidator.alertUser =false;
	var tests, test, query, expectedResult, msg, result;
	
	var stringsSt = [ 
	/*
	************************
	*Start stemming enabled*
	************************
	*/
		/*
		 ****************
		 *Start squiggle*
		 ****************
		 */
			/*
			 ************************
			 *Start proximity search*
			 ************************
			 */
			['"chirac honnete"~1.5', true, "Valid - squiggle proximity query"],
			[' "chirac honnete"~1.5', true, "Valid - squiggle proximity query"],
			['"chirac honnete"~1.5 ', true, "Valid - squiggle proximity query"],
			['"chirac honnete"~1.5 ', true, "Valid - squiggle proximity query"],
			['"chirac honnete"~            1.5 ', false, "Invalid - squiggle proximity query"],
			['"chirac honnete"        ~5 ', true, "Valid - squiggle proximity query"],
			['"chirac honnete"     ~   2 ', false, "Valid - squiggle proximity query"],
			['"chirac honnete"~ j ', false, "Invalid - squiggle proximity query"],
			[' "chirac honnete"~ k ', false, "Invalid - squiggle proximity query"],
			[' "chirac honnete"     ~ l', false, "Invalid - squiggle proximity query"],
			[' "chirac honnete"         ~ l', false, "Invalid - squiggle proximity query"],
			/*
			**********************
			*End proximity search*
			**********************
			*/
			/*
			****************************
			*Start levenshtein distance*
			****************************
			*/
			['chirac~5', false, "Invalid - squiggle levenshtein "],
			['chirac~0.1', false, "Invalid - squiggle levenshtein "],
			['chirac        ~            0.2', false, "Invalid - squiggle levenshtein "],
			['chirac              ~0.3', false, "Invalid - squiggle levenshtein "],
			['chirac~       0.4', false, "Invalid - squiggle levenshtein "]
			/*
			**************************
			*End levenshtein distance*
			**************************
			*/
		/*
		**************
		*End squiggle*
		**************
		*/
	];
	tests = [].concat(stringsSt);
	console.trace('Running tests with stemming enabled');
	runTests(tests);
	/*
	**********************
	*End stemming enabled*
	**********************
	*/

	/*
	*************************
	*Start stemming disabled*
	*************************
	*/
	
	/*
	 ***********************
	 *End stemming disabled*
	 ***********************
	 */
	fireunit.testDone();
})();
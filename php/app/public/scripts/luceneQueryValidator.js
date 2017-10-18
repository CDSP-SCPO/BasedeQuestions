/**
 * Checks a string against solr query syntax
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 */
var luceneQueryValidator = {
	/**
	 * @var Object several translation strings
	 */
	'translate': {},
	/**
	 * @var boolean
	 */
	'wildcardCaseInsensitive': true,
	/**
	 * @var boolean 
	 */
	'alertUser': true,
	/**
	 * @var boolean
	 */
	'stemming': true,
	/**
	 * @constructor
	 * @param {Object} translate : Object, stemming : boolean
	 * @type Object
	 */
	'init': function(params)
	{

		if (params.translate)
		{
			this.translate = params.translate;
		}
		
		if (params.wildcardCaseInsensitive)
		{
			this.wildcardCaseInsensitive = params.wildcardCaseInsensitive;
		}
		
		if (params.alertUser)
		{
			this.alertUser = params.alertUser;
		}
		
		if (params.stemming)
		{
			this.stemming = params.stemming;
		}

		return this;
	},
	/**
	 * @param {Boolean}
	 */
	'setWildcardCaseInsensitive': function(bool)
	{
		this.wildcardCaseInsensitive = bool;
	},
	/**
	 * @param {Boolean}
	 */
	'setAlertUser': function(bool)
	{
		this.alertUser = bool;
	},
	/**
	 * @param {string} Tells if the query validates against expected solr syntax
	 * @type Boolean
	 */
	'checkQuery': function(query)
	{
		return this._checkQueryValue(query);
	},
	/**
	 * @type Boolean
	 */
	'_checkQueryValue': function(query)
	{

		if (query !== null && query.length > 0)
		{
			query = this._removeEscapes(query);

			if ( ! this._checkAllowedCharacters(query))
			{
				return false;
			}

			if ( ! this._checkAsterisk(query))
			{
				return false;
			}

			if( ! this._checkAmpersands(query))
			{
				 return false;
			}

			if ( ! this._checkPipe(query))
			{
				return false;
			}
	    
			if ( ! this._checkCaret(query))
			{
				return false;
			}

			if ( ! this._checkSquiggle(query))
			{
				return false;
			}
	    
			if ( ! this._checkQuestionMark(query))
			{
				return false;
			}

			if ( ! this._checkParentheses(query))
			{
				return false;
			}
			
			if ( ! this._checkPlusMinusExclamationMarkNOT(query))
			{
				return false;
			}
			
			if ( ! this._checkANDORNOT(query)) 
			{
				return false;
			}    
			
			if ( ! this._checkQuotes(query))
			{
				return false;
			}
			
			if (this.wildcardCaseInsensitive)
			{
				
				if (query.indexOf("*") != -1)
				{
					var i = query.indexOf(':');

					if (i == -1)
					{
						query.value = query.toLowerCase();
					}
					
					else
					{
						query.value = query.substring(0, i) + query.substring(i).toLowerCase();
					}

				}

			}

			return true;
		}
	
	},

	/**
	 * @type Boolean
	 */
	'_removeEscapes': function(query)
	{
		return query.replace(/\\./g, "");
	},

	/**
	 * @type Boolean
	 */
	'_checkAllowedCharacters': function (query)
	{
		matches = query.match(/[:]/);

		if (matches !== null && matches.length > 0)
		{

			if (this.alertUser)
			{
				alert(this.translate.allowedCharacters);
			}

			return false;
		}

		return true;
	},
	
	/**
	 * @type Boolean
	 */
	'_checkAsterisk': function(query)
	{
		var msg;

		if ( ! this.stemming)
		{
			matches = query.match(/^\*|[^\\]\*/);
			msg = this.translate.asterisk;
		}

		else
		{
			
			matches = query.match(/^[\*]+$|[\s]\*|^\*[^\s]/);
			msg = this.translate.asteriskSt;
		}
	
		if (matches !== null)
		{

			if (this.alertUser)
			{
				alert(msg);
			}

			return false;
		}
	
		return true;

	},

	/**
	 * @type Boolean
	 */
	'_checkAmpersands': function(query)
	{
		matches = query.match(/[&]{2}/);

		if (matches !== null && matches.length > 0)
		{
			matches = query.match(/([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?|{}\[\]\^~\\@#\/$%'=]+&*)+(\s+&&\s+)?([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?|!{}\[\]\^~\\@#\/$%'=]+&*)+/);

			if (matches === null)
			{
				
				if (this.alertUser)
				{
					alert(this.translate.ampersands);
				}

				return false;
			}
		}

		return true;
	},

	/**
	 * @type Boolean
	 */
	'_checkPipe': function(query)
	{
		matches = query.match(/[|]{2}/);

		if (matches !== null && matches.length > 0)
		{
			matches = query.match(/([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?&{}\[\]\^~\\@#\/$%'=]+\|*)+(\s+\|\|\s+)?([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?&!{}\[\]\^~\\@#\/$%'=]+\|*)+/);

			if (matches === null)
			{
				
				if (this.alertUser)
				{
					alert(this.translate.pipe);
				}
				
				return false;
			}

	  }

		return true;
	},

	/**
	 * @type Boolean
	 */
	'_checkCaret': function(query)
	{
		matches = query.match(/[^\\]\^(?![\s]*[0-9].?[0-9]*)|[^\\]\^$|^\^/);

		if (matches !== null)
		{

			if (this.alertUser)
			{
				alert(this.translate.caret);
			}
	
			return false;
		}

		return true;
	},

	/**
	 * @type Boolean
	 */
	'_checkSquiggle': function(query)
	{

		var ok = true, msg;
		
		if(query.match(/^~|[^\\]~/))
		{
		
			if ( ! this.stemming)
			{
				// Levenshtein distance
				matches = query.match(/[àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\&{}\[\]\^\\@#\/$%'=]+\s*~0\.[5-9]/);
	
				if (matches === null)
				{
					ok = false;
					
					// Proximity search
					matches = query.match(/\"([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\?&{}\[\]\^\\@#\/$%'=]+\s*){2,}\"\s*~[0-9]+/);
	
					if (matches === null)
					{
						ok = false;
					}
	
					else
					{
						ok = true;
					}
	
				}
	
			}
	
			else
			{
				// Levenshtein distance
				matches = query.match(/[àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\&{}\[\]\^\\@#\/$%'=]+\s*~0\.[5-9]/);

				if (matches === null)
				{
					ok = false;
					
					// Proximity search
					matches = query.match(/\"([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\?&{}\[\]\^\\@#\/$%'=]+\s*){2,}\"\s*~[0-9]+/);

					if (matches === null)
					{
						ok = false;
					}
					
					else
					{
						ok = true;
					}

				}
				
			}
			
		}

		if ((! ok) && this.alertUser)
		{
			alert(this.translate.squiggle);
		}

		return ok;	
	},

	/**
	 * @type Boolean
	 */
	'_checkQuestionMark': function(query)
	{
		if ( ! this.stemming)
		{
			matches = query.match(/^\?|[^\\]\?/);

			if (matches !== null && matches.length > 0)
			{
	
				if (this.alertUser)
				{
					alert(this.translate.questionMark);
				}
	
				return false;
			}
	
			return true;
		}

		else
		{
			matches = query.match(/^(\?)|([^àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%'=]\?+)/);

			if (matches !== null)
			{
	
				if (this.alertUser)
				{
					alert(this.translate.questionMarkSt);
				}
		
				return false;
			}
	
			return true;
		}
		
	},

	/**
	 * @type Boolean
	 */
	'_checkParentheses': function (query)
	{
		var hasLeft = false;
		var hasRight = false;
		matchLeft = query.match(/[(]/g);

		if (matchLeft !== null)
		{
			hasLeft = true;
		}
		
		matchRight = query.match(/[)]/g);

		if (matchRight !== null)
		{
			hasRight = true;
		}

		if (hasLeft || hasRight)
		{

			if (hasLeft && ! hasRight || hasRight && ! hasLeft)
			{

				if(this.alertUser)
				{
					alert(this.translate.parentheses1);
				}
				
				return false;
			}
	
			else
			{
				var number = matchLeft.length + matchRight.length;
	
				if ((number % 2) > 0 || matchLeft.length != matchRight.length)
				{
	
					if (this.alertUser)
					{
						alert(this.translate.parentheses1);
					}
					
					return false;
				}
			}

			matches = query.match(/\(\s*[^àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9]*\s*\)/);
	
			if (matches !== null)
			{

				if (this.alertUser)
				{
					alert(this.translate.parentheses2);
				}
	
				return false;
	    
		    }

		}  

		return true;    
	},

	/**
	 * @type Boolean
	 */
	'_checkPlusMinusExclamationMarkNOT': function(query)
	{
		matches = query.match(/^[^\n+\-!]*$|^(\s*[+\-!]?\s*[àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_:.()\"*?&|{}\[\]\^~\\@#\/$%'=]+[ ]?)+$/);

		if (matches === null || matches.length === 0)
		{

		    if (this.alertUser)
		    {
			    alert(this.translate.plusMinusExclamationMarkNOT);
		    }
		    
			return false;
	  }

	  return true;
	},

	/**
	 * @type Boolean
	 */
	'_checkANDORNOT': function(query)
	{
		matches = query.match(/AND|OR|NOT/);

		if (matches !== null && matches.length > 0)
		{
			matches = query.match(/^([àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?&|!{}\[\]\^~\\@\/#$%'=]+\s*((AND )|(OR ))?[àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝa-zA-Z0-9_+\-:.()\"*?&|!{}\[\]\^~\\@\/#$%'=]+[ ]*)+$/);       

			if (matches === null || matches.length === 0)
			{

				if (this.alertUser)
				{
					alert(this.translate.ANDORNOT);
				}
				
				return false;
			}

//				its difficult to distinguish AND/OR/... from the usual [a-zA-Z] because they're...words!
			matches = query.match(/^((AND )|(OR )|(NOT ))|((AND)|(OR)|(NOT))[ ]*$/);
	    
			if (matches !== null && matches.length > 0)
			{

				if (this.alertUser)
				{
					alert(this.translate.ANDORNOT);
				}

				return false;
			}

		}

		return true;
	},

	/**
	 * @type Boolean
	 */
	'_checkQuotes': function(query)
	{
		matches = query.match(/\"/g);

		if (matches !== null && matches.length > 0)
		{
			var number = matches.length;

			if ((number % 2) > 0)
			{

				if (this.alertUser)
				{
					alert(this.translate.quotes1);
				}

				return false;
			}
	
			matches = query.match(/""/);
	
			if (matches !== null)
		    {
				
				if (this.alertUser)
				{
					alert(this.translate.quotes2);
				}

				return false;    
			}    
		}

		return true;
	}

};
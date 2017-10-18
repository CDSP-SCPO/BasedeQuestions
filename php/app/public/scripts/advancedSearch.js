/**
 * 
 * Gathers the script for the advanced search UI.
 * Relies on the given xHTML markup.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @requires jQuery
 * @requires LiveQuery
 */
var advancedSearch = {
	/**
	 * @var int
	 */
	'searchModalities':null,
	/**
	 * @var int
	 */
	'analysisDistance':null,
	/**
	 * @var int
	 */
	'analysisLevenshtein':null,
	/**
	 * @var int
	 */
	'analysisStartsWith':null,
	/**
	 * @var Object
	 */
	'translate':null,
	/**
	 * @var Object
	 */
	'referenceConditionDiv':null,
	/**
	 * @constructor
	 * @param {Object} params
	 */
	'init': function(params) {
		this.searchModalities = params.searchModalities;
		this.analysisDistance = params.analysisDistance;
		this.analysisLevenshtein = params.analysisLevenshtein;
		this.analysisStartsWith = params.analysisStartsWith;
		this.translate = params.translate;
		this.referenceConditionDiv = '<div class="filter">' + $('div.filter').first().html() + '</div>';
	
		if ($('div.filter').length > 1)
		{
			$('div.filter').first().remove();
		}

		this._addConditionButtonListeners();
		this._addFieldListener();
		this._addSubmitListener();
		this._addSelectAnalysisListener();
		this._addTextInputListener();
	
		if($.browser.mozilla)
		{
			$('#advancedSearchForm').attr('autocomplete', 'off');
		}
		
		return this;
	},

	'_addConditionButtonListeners': function()
	{
		$('#addConditionSpan').click(function(){
			$('#filters').append(advancedSearch.referenceConditionDiv);
			$('div.filter').last().show();
			$('div.filter').last().find('span.removeCondition').show();
		});
		$('span.removeCondition').live('click', function(){
			$(this).closest('div.filter').remove();
			$('div.operator').last().hide();
		});
	},

	'_addFieldListener': function()
	{
		$('.field').livequery('change', function(){
			
			var sel1 = $(this).closest('div').find('select.operators').first(),
				sel2 = $(this).closest('div').find('select.operators').last();
	
			if ($(this).val() == advancedSearch.searchModalities)
			{
				sel1.hide();
				sel1.attr('disabled', 'disabled');
				sel2.show();
				sel2.removeAttr('disabled');
			}
	
			else
			{
				sel2.hide();
				sel2.attr('disabled', 'disabled');
				sel1.show();
				sel1.removeAttr('disabled');
			}
	
		});
	},

	'_addSubmitListener': function()
	{
		$('#advancedSearchForm').submit(function(){
			var error = false;
			$(this).find('div.filter').each(function(i, val){
				
				if ( ! error && ! advancedSearch.checkCondition($(this)))
				{
					 error = true;
				}
	
			});
			return ! error;
		});
	},

	'_addSelectAnalysisListener': function()
	{
		$('select.analysis').livequery('change', function(){
			var distanceValueSpan = $(this).siblings('span.distanceValue');
			var levenshteinSpan = $(this).siblings('span.levenshtein');
	
			if ($(this).val() == advancedSearch.analysisDistance)
			{
	
				if (distanceValueSpan.css('display') == 'none')
				{
					distanceValueSpan.css('display', 'inline');
					$(this).siblings('input.text').val(advancedSearch.translate.distanceTip);
				}
	
			}
			
			else
			{
	
				if (distanceValueSpan.css('display') != 'none')
				{
					distanceValueSpan.css('display', 'none');
					
					if ($(this).siblings('input.text').val() == advancedSearch.translate.distanceTip)
					{
						$(this).siblings('input.text').val('');
					}
					
				}
	
			}
	
			if ($(this).val() == advancedSearch.analysisLevenshtein)
			{
	
				if (levenshteinSpan.css('display') == 'none')
				{
					levenshteinSpan.css('display', 'inline');
				}
	
			}
	
			else
			{
	
				if (levenshteinSpan.css('display') != 'none')
				{
					levenshteinSpan.css('display', 'none');
				}
	
			}
	
		});
	},

	'_addTextInputListener': function()
	{
		$('input.text').live('focus', function(){
			
			if ($(this).siblings('select.analysis').val() == advancedSearch.analysisDistance && $(this).val() == advancedSearch.translate.distanceTip)
			{
				$(this).val('');
			}
			
		});
	},

	/**
	 * @param {object} filterDiv
	 */
	'checkCondition': function(filterDiv)
	{
		var textInputVal, analysis, valid = true, textInput, errorMsgP, errorMsg;
		textInput = filterDiv.find('input.text');
		textInputVal = textInput.val();
		analysis = filterDiv.find('select.analysis').val();
		errorMsgP = filterDiv.find('p.errorMessage');
		
		if ( ! this.isAlnum(textInputVal))
		{
			errorMsg = this.translate.errorAlnum;
			valid = false;
		}
		
		if (this.hasLuceneOperator(textInputVal))
		{
			errorMsg = this.translate.errorOperator;
			valid = false;
		}
	
		switch(analysis)
		{
		
			case this.analysisStartsWith:
	
				if (this.wordCount(textInputVal) != 1)
				{
					errorMsg = this.translate.errorStart;
					valid = false;
				}
				
				break;
	
			case this.analysisLevenshtein:
	
				if (this.wordCount(textInputVal) != 1)
				{
					errorMsg = this.translate.errorLevenshtein;
					valid = false;
				}
				
				break;
	
			case this.analysisDistance:
				
				if (this.wordCount(textInputVal) != 2)
				{
					errorMsg = this.translate.errorDistance;
					valid = false;
				}
				
				break;
			
		}
		
		if ( ! valid)
		{
			textInput.addClass('error');
			filterDiv.addClass('error');
			errorMsgP.html(errorMsg);
			errorMsgP.show();
		}
	
		else
		{
	
			if (filterDiv.hasClass('error'))
			{
				filterDiv.removeClass('error');
			}
			
			if (textInput.hasClass('error'))
			{
				textInput.removeClass('error');
				errorMsgP.hide();
			}
			
		}
	
		return valid;
	},

	/**
	 * @param {String} text
	 */
	'isAlnum': function(text)
	{
		var matches, reg;
		reg = new RegExp('([^a-zA-Z0-9àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ ])', 'g');
		matches = text.match(reg);
	
		if (matches !== null && matches.length > 0)
		{ 
			return false;
		}
	
		return true;
	},

	/**
	 * @param {String} text
	 */
	'hasLuceneOperator': function(text)
	{
		var matches, reg;
		reg = new RegExp('(AND|OR|NOT)', 'g');
		matches = text.match(reg);
	
		if (matches !== null && matches.length > 0)
		{
			return true;
		}
	
		return false;
	},

	/**
	 * @param {String} text
	 */
	'wordCount': function(text)
	{
		text = jQuery.trim(text);
		return text.split(/\s/).length;
	}

};